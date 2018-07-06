<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Entity;


class ReferenceVotes
{
    /**
     * @var integer
     */
    protected $reference;

    /**
     * @var integer
     */
    protected $positiveVotes = 0;

    /**
     * @var integer
     */
    protected $negativeVotes = 0;

    /**
     * @var integer
     */
    protected $userVotes = 0;

    /**
     * @var integer
     */
    protected $anonymousVotes = 0;

    /**
     * @var boolean
     */
    protected $published = false;

    /**
     * @return int
     */
    public function getReference(): int
    {
        return $this->reference;
    }

    /**
     * @param int $reference
     */
    public function setReference(int $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return int
     */
    public function getPositiveVotes(): int
    {
        return $this->positiveVotes;
    }

    /**
     * @param int $positiveVotes
     */
    public function setPositiveVotes(int $positiveVotes)
    {
        $this->positiveVotes = $positiveVotes;
    }

    /**
     * @return int
     */
    public function getNegativeVotes(): int
    {
        return $this->negativeVotes;
    }

    /**
     * @param int $negativeVotes
     */
    public function setNegativeVotes(int $negativeVotes)
    {
        $this->negativeVotes = $negativeVotes;
    }

    /**
     * @return int
     */
    public function getUserVotes(): int
    {
        return $this->userVotes;
    }

    /**
     * @param int $userVotes
     */
    public function setUserVotes(int $userVotes)
    {
        $this->userVotes = $userVotes;
    }

    /**
     * @return int
     */
    public function getAnonymousVotes(): int
    {
        return $this->anonymousVotes;
    }

    /**
     * @param int $anonymousVotes
     */
    public function setAnonymousVotes(int $anonymousVotes)
    {
        $this->anonymousVotes = $anonymousVotes;
    }

    /**
     * @param boolean $positive
     * @param boolean $anonymous
     */
    public function addVote(bool $positive, $anonymous = true)
    {
        if ($positive) {
            $this->positiveVotes++;
            if ($anonymous) {
                $this->anonymousVotes++;
            } else {
                $this->userVotes++;
            }
        } else {
            $this->negativeVotes++;
        }
    }

    /**
     * @return boolean
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param boolean $published
     */
    public function setPublished(bool $published)
    {
        $this->published = $published;
    }
}