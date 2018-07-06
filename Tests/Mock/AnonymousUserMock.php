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

class AnonymousUserMock
{
    public function getUsername()
    {
        return 'anon.';
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getRoles()
    {
        return array();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}