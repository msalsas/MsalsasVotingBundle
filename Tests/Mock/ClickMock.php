<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Tests\Mock;


class ClickMock
{
    protected $user;
    protected $userIP;

    public function __construct($user, $userIP = '127.0.0.1')
    {
        $this->user = $user;
        $this->userIP = $userIP;
    }

    public function getId()
    {
        return 1;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getReference()
    {
        return 1;
    }

    public function getUserIP()
    {
        return $this->userIP;
    }
}