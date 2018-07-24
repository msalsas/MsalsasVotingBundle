<?php

/*
 * This file is part of the MsalsasVotingBundle package.
 *
 * (c) Manolo Salsas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msalsas\VotingBundle\Tests\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Msalsas\VotingBundle\Entity\Click;
use Msalsas\VotingBundle\Entity\ReferenceClicks;
use Msalsas\VotingBundle\Service\Clicker;
use Msalsas\VotingBundle\Tests\Mock\ClickMock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


class ClickerTest extends AbstractServiceTest
{
    public function testNewReferenceClickWithUser()
    {
        $this->setUserMocks();

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks(1));

        $clicks = $clicker->addClick(1);

        $this->assertSame(1, $clicks);
    }

    public function testClickWithAnonymous()
    {
        $this->setAnonymousUserMocks();

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks(1));

        $clicks = $clicker->addClick(1);

        $this->assertSame(1, $clicks);
    }

    public function testClickWithAnonymousAndNoIp()
    {
        $this->setAnonymousUserMocks();

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getClientIp'))
            ->getMock();
        $requestMock->expects($this->any())
            ->method('getClientIp')->willReturn(null);

        $this->requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrentRequest'))
            ->getMock();
        $this->requestStackMock->expects($this->any())
            ->method('getCurrentRequest')->willReturn($requestMock);

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks(1));

        $clicks = $clicker->addClick(1);

        $this->assertSame(0, $clicks);
    }

    public function testClickWithUserWhoAlreadyClicked()
    {
        $this->setUserMocks();

        $clickMock = $this->getClickMock($this->userMock);
        $referenceClicksMock = $this->getMockBuilder(ReferenceClicks::class)
            ->getMock();
        $referenceClicksMock->method('getReference')->willReturn(1);
        $referenceClicksMock->method('getClicks')->willReturn(2);

        $clickEmRepositoryMock = $this->getRepositoryMock($clickMock);
        $referenceClicksEmRepositoryMock = $this->getRepositoryMock($referenceClicksMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->expects($this->exactly(2))->method('getRepository')->withConsecutive([Click::class], [ReferenceClicks::class])
            ->willReturnOnConsecutiveCalls($clickEmRepositoryMock, $referenceClicksEmRepositoryMock);

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $clicks = $clicker->addClick(1);

        $this->assertSame(2, $clicks);
    }


    public function testClickWithUserWhoAlreadyClickedAndNoReference()
    {
        $this->setUserMocks();

        $referenceClicksMock = $this->getMockBuilder(ReferenceClicks::class)
            ->getMock();
        $referenceClicksMock->method('getReference')->willReturn(1);

        $clickEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceClicksEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $clickEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn(null);
        $referenceClicksEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn($referenceClicksMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->expects($this->exactly(2))->method('getRepository')->withConsecutive([Click::class], [ReferenceClicks::class])
            ->willReturnOnConsecutiveCalls($clickEmRepositoryMock, $referenceClicksEmRepositoryMock);

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $clicks = $clicker->addClick(1);

        $this->assertSame(0, $clicks);
    }

    public function testClickWithAnonymousWhoAlreadyClicked()
    {
        $this->setAnonymousUserMocks();

        $clickMock = $this->getClickMock($this->userMock);
        $referenceClicksMock = $this->getMockBuilder(ReferenceClicks::class)
            ->getMock();
        $referenceClicksMock->method('getReference')->willReturn(1);
        $referenceClicksMock->method('getClicks')->willReturn(2);

        $clickEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceClicksEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $clickEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn($clickMock);
        $referenceClicksEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn($referenceClicksMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->expects($this->exactly(2))->method('getRepository')->withConsecutive([Click::class], [ReferenceClicks::class])
            ->willReturnOnConsecutiveCalls($clickEmRepositoryMock, $referenceClicksEmRepositoryMock);

        $clicker = new Clicker($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator);

        $clicks = $clicker->addClick(1);

        $this->assertSame(2, $clicks);
    }

    private function getClickMock($user, $userIP = '127.0.0.1')
    {
        return $this->getMockBuilder(ClickMock::class)
            ->setConstructorArgs(array($user, $userIP))
            ->getMock();
    }
}