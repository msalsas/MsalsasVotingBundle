<?php

namespace Msalsas\VotingBundle\Tests\Controller;

use Msalsas\VotingBundle\Controller\VoteController;
use Msalsas\VotingBundle\Service\Voter;
use Msalsas\VotingBundle\Tests\Mock\AnonymousUserMock;
use Msalsas\VotingBundle\Tests\Mock\UserMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\Translator;

class VoteControllerTest
{
    protected $emMock;
    protected $userMock;
    protected $requestStackMock;
    protected $requestMock;
    protected $tokenStorage;
    protected $translator;

    public function setUp()
    {
        parent::setUp();
        $emRepositoryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')->getMock();
        $this->emMock = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
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

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException|null
     */
    public function testVotePositive()
    {
        $client = static::createClient();

        $client->request('POST', '/vote-positive/en/1', [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
//
//    public function testVotePositive()
//    {
//        $userMock = $this->getMockBuilder(AnonymousUserMock::class)
//            ->getMock();
//
//        $tokenMock = $this->getMockBuilder(TokenInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenMock->method('getUser')->willReturn($userMock);
//
//        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
//
//        $voter = new Voter($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator, 2, 50, array('irrelevant'));
//
//
//        $controller = new VoteController($voter);
//        $controller->setContainer(new Container());
//        $request = new Request();
//        $request->headers->add(array('X-Requested-With' => 'XMLHttpRequest'));
//
//        $response = $controller->votePositive(1, $request);
//
//        $this->assertEquals(200, $response->getStatusCode());
//    }
//
//    public function testVoteNegative()
//    {
//        $userMock = $this->getMockBuilder(UserMock::class)
//            ->getMock();
//
//        $tokenMock = $this->getMockBuilder(TokenInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenMock->method('getUser')->willReturn($userMock);
//
//        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
//
//        $voter = new Voter($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator, 2, 50, array('irrelevant'));
//
//
//        $controller = new VoteController($voter);
//        $controller->setContainer(new Container());
//        $request = new Request();
//
//        $request->initialize(
//            [],
//            [],
//            [],
//            [],
//            [],
//            [],
//            'irrelevant'
//        );
//        $request->headers->add(array('X-Requested-With' => 'XMLHttpRequest'));
//
//        $response = $controller->voteNegative(1, $request);
//
//        $this->assertEquals(200, $response->getStatusCode());
//    }
//
//    /**
//     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
//     */
//    public function testVotePositiveWithoutXmlHttpRequest()
//    {
//        $userMock = $this->getMockBuilder(AnonymousUserMock::class)
//            ->getMock();
//
//        $tokenMock = $this->getMockBuilder(TokenInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenMock->method('getUser')->willReturn($userMock);
//
//        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
//
//        $voter = new Voter($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator, 2, 50, array('irrelevant'));
//
//
//        $controller = new VoteController($voter);
//        $controller->setContainer(new Container());
//        $request = new Request();
//
//        $controller->votePositive(1, $request);
//    }
//
//    /**
//     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
//     */
//    public function testVoteNegativeWithoutXmlHttpRequest()
//    {
//        $userMock = $this->getMockBuilder(UserMock::class)
//            ->getMock();
//
//        $tokenMock = $this->getMockBuilder(TokenInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenMock->method('getUser')->willReturn($userMock);
//
//        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
//
//        $voter = new Voter($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator, 2, 50, array('irrelevant'));
//
//
//        $controller = new VoteController($voter);
//        $controller->setContainer(new Container());
//        $request = new Request();
//
//        $request->initialize(
//            [],
//            [],
//            [],
//            [],
//            [],
//            [],
//            'irrelevant'
//        );
//
//        $controller->voteNegative(1, $request);
//    }
//
//    /**
//     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
//     */
//    public function testVoteNegativeWithoutReason()
//    {
//        $userMock = $this->getMockBuilder(UserMock::class)
//            ->getMock();
//
//        $tokenMock = $this->getMockBuilder(TokenInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenMock->method('getUser')->willReturn($userMock);
//
//        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
//
//        $voter = new Voter($this->emMock, $tokenStorageMock, $this->requestStackMock, $this->translator, 2, 50, array('irrelevant'));
//
//
//        $controller = new VoteController($voter);
//        $controller->setContainer(new Container());
//        $request = new Request();
//        $request->headers->add(array('X-Requested-With' => 'XMLHttpRequest'));
//
//        $controller->voteNegative(1, $request);
//    }
}