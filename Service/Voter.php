<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Msalsas\VotingBundle\Entity\ReferenceVotes;
use Msalsas\VotingBundle\Entity\VoteNegative;
use Msalsas\VotingBundle\Entity\VotePositive;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Voter
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TokenStorageInterface
     */
    protected $token;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var integer
     */
    protected $anonPercentAllowed;

    /**
     * @var integer
     */
    protected $anonMinAllowed;

    /**
     * @var array
     */
    protected $negativeReasons;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $token, RequestStack $requestStack, TranslatorInterface $translator, $anonPercentAllowed, $anonMinAllowed, $negativeReasons = array())
    {
        $this->em = $em;
        $this->token = $token;
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->anonPercentAllowed = $anonPercentAllowed;
        $this->anonMinAllowed = $anonMinAllowed;
        $this->negativeReasons = $negativeReasons;
    }

    public function getPositiveVotes($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            $referenceVotes = new ReferenceVotes();
            $referenceVotes->setReference($referenceId);
        }

        return $referenceVotes->getPositiveVotes();
    }

    public function getNegativeVotes($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            $referenceVotes = new ReferenceVotes();
            $referenceVotes->setReference($referenceId);
        }

        return $referenceVotes->getNegativeVotes();
    }

    public function getUserPositiveVotes($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            $referenceVotes = new ReferenceVotes();
            $referenceVotes->setReference($referenceId);
        }

        return $referenceVotes->getUserVotes();
    }

    public function getAnonymousVotes($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            $referenceVotes = new ReferenceVotes();
            $referenceVotes->setReference($referenceId);
        }

        return $referenceVotes->getAnonymousVotes();
    }

    public function votePositive($referenceId)
    {
        $user = $this->token->getToken() ? $this->token->getToken()->getUser() : null;

        $this->validateVote($user, $referenceId);

        $user = $user instanceof UserInterface ? $user : null;

        $vote = new VotePositive();
        $vote->setReference($referenceId);
        $vote->setUser($user);
        $vote->setUserIP($this->request ? $this->request->getClientIp() : null);

        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes $referenceVotes */
        $referenceVotes = $this->addReferenceVote($referenceId, true, !$user);

        $this->em->persist($vote);
        $this->em->persist($referenceVotes);
        $this->em->flush();

        return $referenceVotes->getPositiveVotes();
    }

    public function voteNegative($referenceId, $reason)
    {
        $user = $this->token->getToken() ? $this->token->getToken()->getUser() : null;

        if (!$reason || !is_string($reason) || $reason === '0') {
            throw new AccessDeniedException($this->translator->trans('msalsas_voting.errors.invalid_reason'));
        }

        $this->validateVote($user, $referenceId);

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException($this->translator->trans('msalsas_voting.errors.anon_cannot_vote_negative'));
        }

        $vote = new VoteNegative();
        $vote->setReference($referenceId);
        $vote->setReason($reason);
        $vote->setUser($user);
        $vote->setUserIP($this->request->getClientIp());

        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes $referenceVotes */
        $referenceVotes = $this->addReferenceVote($referenceId, false, !$user);

        $this->em->persist($referenceVotes);
        $this->em->persist($vote);
        $this->em->flush();

        return $referenceVotes->getNegativeVotes();
    }

    public function getUserVote($referenceId)
    {
        $user = $this->token->getToken()->getUser();
        $user = $user instanceof UserInterface ? $user : null;
        $votePositiveRepository = $this->em->getRepository(VotePositive::class);
        $voteNegativeRepository = $this->em->getRepository(VoteNegative::class);

        if ($user && $vote = $votePositiveRepository->findOneBy(array('user' => $user, 'reference' => $referenceId))) {
            return $vote;
        } else if (!$user && $vote = $votePositiveRepository->findOneBy(array('user' => $user, 'reference' => $referenceId, 'userIP' => $this->request->getClientIp()))) {
            return $vote;
        } else if ($vote = $voteNegativeRepository->findOneBy(array('user' => $user, 'reference' => $referenceId))) {
            return $vote;
        }

        return false;
    }

    public function getNegativeReasons()
    {
        return $this->negativeReasons;
    }

    public function userCanVoteNegative($referenceId)
    {
        $user = $this->token->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return !$this->userHasVoted($user, $referenceId);
    }

    public function isPublished($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            return false;
        }

        return $referenceVotes->isPublished();
    }

    public function setPublished($referenceId)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if (!$referenceVotes) {
            throw new \Exception($this->translator->trans('msalsas_voting.errors.ref_does_not_exist', array('%reference%' => $referenceId)));
        }

        $referenceVotes->setPublished(true);
    }

    protected function validateVote($user, $referenceId)
    {
        if (!$user instanceof UserInterface && (!$this->request || !$this->request->getClientIp())) {
            throw new AccessDeniedException($this->translator->trans('msalsas_voting.errors.no_ip_defined_for_anon'));
        }

        if ($this->userHasVoted($user, $referenceId)) {
            throw new AccessDeniedException($this->translator->trans('msalsas_voting.errors.already_voted'));
        }

        if (!$user instanceof UserInterface) {
            $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
            if ($referenceVotes instanceof ReferenceVotes && !$this->anonymousIsAllowed($referenceVotes)) {
                throw new AccessDeniedException($this->translator->trans('msalsas_voting.errors.too_much_anon_votes'));
            }
        }
    }

    protected function userHasVoted($user, $referenceId)
    {
        $votePositiveRepository = $this->em->getRepository(VotePositive::class);
        $voteNegativeRepository = $this->em->getRepository(VoteNegative::class);

        if ($user instanceof UserInterface) {
            if ($votePositiveRepository->findOneBy(
                array(
                    'user' => $user,
                    'reference' => $referenceId
                )
            )) {
                return true;
            } else if ($voteNegativeRepository->findOneBy(
                array(
                    'user' => $user,
                    'reference' => $referenceId
                )
            )) {
                return true;
            }
        } else {
            if ($votePositiveRepository->findOneBy(
                array(
                    'user' => null,
                    'userIP' => $this->request->getClientIp(),
                    'reference' => $referenceId
                )
            )) {
                return true;
            }
        }

        return false;
    }

    protected function addReferenceVote($referenceId, $positive, $anonymous = true)
    {
        /** @var \Msalsas\VotingBundle\Entity\ReferenceVotes|null $referenceVotes */
        $referenceVotes = $this->em->getRepository(ReferenceVotes::class)->findOneBy(array('reference' => $referenceId));
        if ($referenceVotes) {
            $referenceVotes->addVote($positive, $anonymous);
        } else {
            $referenceVotes = new ReferenceVotes();
            $referenceVotes->setReference($referenceId);
            $referenceVotes->addVote($positive, $anonymous);
        }

        return $referenceVotes;
    }

    protected function anonymousIsAllowed(ReferenceVotes $referenceVotes)
    {
        $anonVotes = $referenceVotes->getAnonymousVotes();
        $userVotes = $referenceVotes->getUserVotes();

        if ($anonVotes < $this->anonMinAllowed) {
            return true;
        }

        $anonPercent = $anonVotes ? ($anonVotes / ($userVotes + $anonVotes)) * 100 : 0;
        if ($anonPercent < $this->anonPercentAllowed) {
            return true;
        }

        return false;
    }
}