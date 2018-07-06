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


class ReferenceClicks
{
    /**
     * @var integer
     */
    protected $reference;

    /**
     * @var integer
     */
    protected $clicks = 0;

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
    public function getClicks(): int
    {
        return $this->clicks;
    }

    /**
     * @param int $clicks
     */
    public function setClicks(int $clicks)
    {
        $this->clicks = $clicks;
    }

    public function addClick()
    {
        $this->clicks++;
    }
}