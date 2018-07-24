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
use Msalsas\VotingBundle\Entity\ReferenceVotes;
use Msalsas\VotingBundle\Entity\VoteNegative;
use Msalsas\VotingBundle\Entity\VotePositive;
use Msalsas\VotingBundle\Service\Voter;
use Msalsas\VotingBundle\Tests\Mock\VoteNegativeMock;
use Msalsas\VotingBundle\Tests\Mock\VotePositiveMock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


class VoterTest extends AbstractServiceTest
{

    public function testAnonymousCannotVoteNegative()
    {
        $this->setAnonymousUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(false, $voter->userCanVoteNegative(1));
    }

    public function testUserCanVoteNegative()
    {
        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(true, $voter->userCanVoteNegative(1));
    }

    public function testUserCannotVoteNegativeVotedPositive()
    {
        $this->setUserMocks();
        $votePositiveMock = $this->getVotePositiveMock($this->userMock);
        $emRepositoryMock = $this->getRepositoryMock($votePositiveMock);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(false, $voter->userCanVoteNegative(1));
    }

    public function testUserCannotVoteNegativeVotedNegative()
    {
        $this->setUserMocks();
        $positiveVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $voteNegativeMock = $this->getVoteNegativeMock($this->userMock);
        $negativeVoteEmRepositoryMock = $this->getRepositoryMock($voteNegativeMock);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(false, $voter->userCanVoteNegative(1));
    }

    public function testGetVotes()
    {
        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(0, $voter->getUserPositiveVotes(1));
        $this->assertSame(0, $voter->getPositiveVotes(1));
        $this->assertSame(0, $voter->getNegativeVotes(1));
        $this->assertSame(0, $voter->getAnonymousVotes(1));
        $this->assertSame(false, $voter->getUserVote(1));
    }

    public function testGetVotesWithExistingReference()
    {
        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $referenceVotesMock->method('getPositiveVotes')->willReturn(11);
        $referenceVotesMock->method('getUserVotes')->willReturn(6);
        $referenceVotesMock->method('getNegativeVotes')->willReturn(3);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(5);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame($referenceVotesMock, $voter->getUserVote(1));
        $this->assertSame(6, $voter->getUserPositiveVotes(1));
        $this->assertSame(5, $voter->getAnonymousVotes(1));
        $this->assertSame(11, $voter->getPositiveVotes(1));
        $this->assertSame(3, $voter->getNegativeVotes(1));
        $this->assertSame(['irrelevant'], $voter->getNegativeReasons());
    }

    public function testGetUserVoteWhenNegative()
    {
        $this->setUserMocks();
        $positiveVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $voteNegativeMock = $this->getVoteNegativeMock($this->userMock);
        $negativeVoteEmRepositoryMock = $this->getRepositoryMock($voteNegativeMock);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame($voteNegativeMock, $voter->getUserVote(1));
    }

    public function testGetAnonymousVoteWithVoteByIP()
    {
        $this->setAnonymousUserMocks();
        $votePositiveMock = $this->getVotePositiveMock($this->userMock);
        $positiveVoteEmRepositoryMock = $this->getRepositoryMock($votePositiveMock);
        $negativeVoteEmRepositoryMock = $this->getRepositoryMock(null);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame($votePositiveMock, $voter->getUserVote(1));
    }

    public function testVotePositiveWithUser()
    {
        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getPositiveVotes(1));

        $votes = $voter->votePositive(1);

        $this->assertSame(1, $votes);
    }

    public function testVotePositiveWithUserAndExistingReference()
    {
        $this->setUserMocks();

        $positiveVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $negativeVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $referenceVotesMock->method('getPositiveVotes')->willReturn(11);
        $referenceVotesMock->method('getUserVotes')->willReturn(6);
        $referenceVotesMock->method('getNegativeVotes')->willReturn(3);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(5);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $referenceVotesEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceVotesEmRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class], [ReferenceVotes::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock, $referenceVotesEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $votes = $voter->votePositive(1);

        $this->assertSame(11, $votes);
    }

    public function testVoteNegativeWithUserAndExistingReference()
    {
        $this->setUserMocks();

        $positiveVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $negativeVoteEmRepositoryMock = $this->getRepositoryMock(null);
        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $referenceVotesMock->method('getPositiveVotes')->willReturn(11);
        $referenceVotesMock->method('getUserVotes')->willReturn(6);
        $referenceVotesMock->method('getNegativeVotes')->willReturn(3);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(5);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $referenceVotesEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceVotesEmRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);

        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class], [ReferenceVotes::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock, $referenceVotesEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $votes = $voter->voteNegative(1, 'irrelevant');

        $this->assertSame(3, $votes);
    }

    public function testVoteNegativeWithUser()
    {
        $referenceVotes = new ReferenceVotes();
        $referenceVotes->setReference(1);

        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getNegativeVotes($referenceVotes->getReference()));

        $votes = $voter->voteNegative(1, 'irrelevant');

        $this->assertSame(1, $votes);
    }

    /**
     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function testVoteNegativeWithUserWithoutReason()
    {
        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getNegativeVotes(1));

        $voter->voteNegative(1, '');
    }

    public function testVotePositiveWithAnonymous()
    {
        $this->setAnonymousUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getPositiveVotes(1));

        $votes = $voter->votePositive(1);

        $this->assertSame(1, $votes);
    }

    public function testVoteWithAnonymousOneAllowed()
    {
        $this->setAnonymousUserMocks();

        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('getPositiveVotes')->willReturn(10);

        $positiveVotesMock = $this->getMockBuilder(VotePositive::class)
            ->getMock();
        $positiveVotesMock->method('getReference')->willReturn(1);

        $negativeVotesMock = $this->getMockBuilder(VoteNegative::class)
            ->getMock();
        $negativeVotesMock->method('getReference')->willReturn(1);

        $positiveVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $negativeVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceVotesEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $positiveVoteEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn(null);
        $negativeVoteEmRepositoryMock->method('findOneBy')->willReturn($negativeVotesMock);
        $referenceVotesEmRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class], [ReferenceVotes::class], [ReferenceVotes::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock, $referenceVotesEmRepositoryMock, $referenceVotesEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 1, array('irrelevant'));

        $votes = $voter->votePositive(1);

        $this->assertSame(10, $votes);
    }

    public function testVoteWithAnonymousTenPercentAllowed()
    {
        $this->setAnonymousUserMocks();

        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(1);
        $referenceVotesMock->method('getUserVotes')->willReturn(100);

        $positiveVotesMock = $this->getMockBuilder(VotePositive::class)
            ->getMock();
        $positiveVotesMock->method('getReference')->willReturn(1);

        $negativeVotesMock = $this->getMockBuilder(VoteNegative::class)
            ->getMock();
        $negativeVotesMock->method('getReference')->willReturn(1);

        $positiveVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $negativeVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceVotesEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $positiveVoteEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn(null);
        $negativeVoteEmRepositoryMock->method('findOneBy')->willReturn($negativeVotesMock);
        $referenceVotesEmRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class], [ReferenceVotes::class], [ReferenceVotes::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock, $referenceVotesEmRepositoryMock, $referenceVotesEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 10, 0, array('irrelevant'));

        $voter->votePositive(1);
    }

    /**
     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function testVoteWithAnonymousNotAllowed()
    {
        $this->setAnonymousUserMocks();

        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(1);
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 0, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getPositiveVotes(1));

        $voter->votePositive(1);
    }

    /**
     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function testVotePositiveWithAnonymousPercentToZero()
    {
        $this->setAnonymousUserMocks();

        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('getAnonymousVotes')->willReturn(10);
        $referenceVotesMock->method('getUserVotes')->willReturn(1);
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);

        $positiveVotesMock = $this->getMockBuilder(VotePositive::class)
            ->getMock();
        $positiveVotesMock->method('getReference')->willReturn(1);

        $negativeVotesMock = $this->getMockBuilder(VoteNegative::class)
            ->getMock();
        $negativeVotesMock->method('getReference')->willReturn(1);

        $positiveVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $negativeVoteEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $referenceVotesEmRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $positiveVoteEmRepositoryMock->expects($this->exactly(1))->method('findOneBy')->willReturn(null);
        $negativeVoteEmRepositoryMock->method('findOneBy')->willReturn($negativeVotesMock);
        $referenceVotesEmRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->withConsecutive([VotePositive::class], [VoteNegative::class], [ReferenceVotes::class])
            ->willReturnOnConsecutiveCalls($positiveVoteEmRepositoryMock, $negativeVoteEmRepositoryMock, $referenceVotesEmRepositoryMock);

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 0, 0, array('irrelevant'));

        $voter->votePositive(1);
    }

    /**
     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function testVoteNegativeWithAnonymous()
    {
        $referenceVotes = new ReferenceVotes();
        $referenceVotes->setReference(1);

        $this->setAnonymousUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getNegativeVotes($referenceVotes->getReference()));

        $voter->voteNegative(1, 'irrelevant');
    }

    /**
     * @expectedException \Symfony\Component\Finder\Exception\AccessDeniedException
     */
    public function testVotePositiveWithAnonymousAndNoIp()
    {
        $referenceVotes = new ReferenceVotes();
        $referenceVotes->setReference(1);

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

        $this->setAnonymousUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(array('irrelevant'), $voter->getNegativeReasons());
        $this->assertSame(0, $voter->getPositiveVotes($referenceVotes->getReference()));

        $voter->votePositive($referenceVotes->getReference());
    }

    public function testPublishReference()
    {
        $referenceVotesMock = $this->getMockBuilder(ReferenceVotes::class)
            ->getMock();
        $referenceVotesMock->method('getReference')->willReturn(1);
        $referenceVotesMock->method('isPublished')->willReturn(true);
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn($referenceVotesMock);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $voter->setPublished(1);
        $this->assertSame(true, $voter->isPublished(1));
    }

    /**
     * @expectedException \Exception
     */
    public function testPublishNoReference()
    {
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn(null);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $voter->setPublished(1);
    }

    public function testIsPublishNoReference()
    {
        $emRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $emRepositoryMock->method('findOneBy')->willReturn(null);
        $this->emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->emMock->method('getRepository')->willReturn($emRepositoryMock);

        $this->setUserMocks();

        $voter = new Voter($this->emMock, $this->tokenStorageMock, $this->requestStackMock, $this->translator, 50, 2, array('irrelevant'));

        $this->assertSame(false, $voter->isPublished(1));
    }

    private function getVotePositiveMock($user, $userIP = '127.0.0.1')
    {
        return $this->getMockBuilder(VotePositiveMock::class)
            ->setConstructorArgs(array($user, $userIP))
            ->getMock();
    }

    private function getVoteNegativeMock($user, $reason = 'irrelevant', $userIP = '127.0.0.1')
    {
        return $this->getMockBuilder(VoteNegativeMock::class)
            ->setConstructorArgs(array($user, $reason, $userIP))
            ->getMock();
    }

}