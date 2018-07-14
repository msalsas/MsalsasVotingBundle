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
use Msalsas\VotingBundle\Tests\Mock\AnonymousUserMock;
use Msalsas\VotingBundle\Tests\Mock\ClickMock;
use Msalsas\VotingBundle\Tests\Mock\UserMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClickerTest extends WebTestCase
{
    protected $emMock;
    protected $requestStackMock;
    protected $translator;

    public function setUp()
    {
        parent::setUp();
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getClientIp'))
            ->getMock();
        $requestMock->expects($this->any())
            ->method('getClientIp')->willReturn('127.0.0.1');

        $this->requestStackMock = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrentRequest'))
            ->getMock();
        $this->requestStackMock->expects($this->any())
            ->method('getCurrentRequest')->willReturn($requestMock);
        $this->translator = new Translator('en');
    }

    public function testNewReferenceClickWithUser()
    {
        $userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks(1));

        $clicks = $clicker->addClick(1);

        $this->assertSame(1, $clicks);
    }

    public function testClickWithAnonymous()
    {
        $referenceClicks = new ReferenceClicks();
        $referenceClicks->setReference(1);

        $userMock = $this->getMockBuilder(AnonymousUserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks($referenceClicks->getReference()));

        $clicks = $clicker->addClick($referenceClicks->getReference());

        $this->assertSame(1, $clicks);
    }

    public function testClickWithAnonymousAndNoIp()
    {
        $referenceClicks = new ReferenceClicks();
        $referenceClicks->setReference(1);

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

        $userMock = $this->getMockBuilder(AnonymousUserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

        $this->assertSame(0, $clicker->getClicks($referenceClicks->getReference()));

        $clicks = $clicker->addClick($referenceClicks->getReference());

        $this->assertSame(0, $clicks);
    }

    public function testClickWithUserWhoAlreadyClicked()
    {
        $userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();

        $clickMock = $this->getClickMock($userMock);
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

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

        $clicks = $clicker->addClick(1);

        $this->assertSame(2, $clicks);
    }


    public function testClickWithUserWhoAlreadyClickedAndNoReference()
    {
        $userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();

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


        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

        $clicks = $clicker->addClick(1);

        $this->assertSame(0, $clicks);
    }

    public function testClickWithAnonymousWhoAlreadyClicked()
    {
        $userMock = $this->getMockBuilder(AnonymousUserMock::class)
            ->getMock();

        $clickMock = $this->getClickMock($userMock);
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

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $clicker = new Clicker($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator);

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