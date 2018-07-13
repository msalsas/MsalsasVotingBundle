<?php

namespace Msalsas\VotingBundle\Tests\Entity;

use Msalsas\VotingBundle\Entity\Click;
use Msalsas\VotingBundle\Tests\Mock\UserMock;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ClickTest extends TestCase
{
    public function testClick()
    {
        $userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();
        $click = new Click();
        $click->setId(1);
        $click->setReference(1);
        $click->setUser($userMock);
        $click->setUserIP('127.0.0.1');

        $this->assertSame(1, $click->getId());
        $this->assertSame(1, $click->getReference());
        $this->assertSame($userMock, $click->getUser());
        $this->assertSame('127.0.0.1', $click->getUserIP());
    }
}