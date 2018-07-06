<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Controller;

use Msalsas\VotingBundle\Service\Voter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

class VoteController extends Controller
{
    /**
     * @var Voter
     */
    protected $voter;

    public function __construct(Voter $voter)
    {
        $this->voter = $voter;
    }

    public function votePositive($id, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        try {
            $votesCount = $this->voter->votePositive($id);
        } catch (AccessDeniedException $e) {
            return $this->json($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), 403);
        }

        return $this->json($votesCount);
    }

    public function voteNegative($id, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        $reason = $request->getContent();
        if (!$reason || !is_string($reason) || !in_array($reason, $this->voter->getNegativeReasons())) {
            throw new AccessDeniedException();
        }

        try {
            $votesCount = $this->voter->voteNegative($id, $reason);
        } catch (AccessDeniedException $e) {
            return $this->json($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), 403);
        }

        return $this->json($votesCount);
    }
}