<?php

namespace Msalsas\VotingBundle\Tests\Entity;

use Msalsas\VotingBundle\Entity\ReferenceVotes;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ReferenceVotesTest extends TestCase
{
    public function testReferenceVotes()
    {
        $referenceVotes = new ReferenceVotes();
        $referenceVotes->setReference(1);
        $referenceVotes->setPositiveVotes(880);
        $referenceVotes->setAnonymousVotes(80);
        $referenceVotes->setUserVotes(800);
        $referenceVotes->setNegativeVotes(8);
        $referenceVotes->setPublished(true);

        $this->assertSame(1, $referenceVotes->getReference());
        $this->assertSame(880, $referenceVotes->getPositiveVotes());
        $this->assertSame(80, $referenceVotes->getAnonymousVotes());
        $this->assertSame(800, $referenceVotes->getUserVotes());
        $this->assertSame(8, $referenceVotes->getNegativeVotes());
        $this->assertSame(true, $referenceVotes->isPublished());
    }
}