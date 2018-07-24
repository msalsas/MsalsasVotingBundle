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
use Msalsas\VotingBundle\Tests\Mock\AnonymousUserMock;
use Msalsas\VotingBundle\Tests\Mock\UserMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


abstract class AbstractServiceTest extends WebTestCase
{
    protected $emMock;
    protected $requestStackMock;
    protected $translator;
    protected $userMock;
    protected $tokenStorageMock;

    public function setUp()
    {
        parent::setUp();
        $this->setDefaultMocks();
        $this->translator = new Translator('en');
    }

    protected function setDefaultMocks()
    {
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
    }

    protected function setUserMocks()
    {
        $this->userMock = $this->getMockBuilder(UserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($this->userMock);

        $this->tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenStorageMock->method('getToken')->willReturn($tokenMock);
    }

    protected function setAnonymousUserMocks()
    {
        $this->userMock = $this->getMockBuilder(AnonymousUserMock::class)
            ->getMock();

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenMock->method('getUser')->willReturn($this->userMock);

        $this->tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tokenStorageMock->method('getToken')->willReturn($tokenMock);
    }

    protected function getRepositoryMock($classMock)
    {
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn($classMock);

        return $emRepositoryMock;
    }
}
