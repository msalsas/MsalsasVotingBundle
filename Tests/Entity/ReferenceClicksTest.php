<?php

namespace Msalsas\VotingBundle\Tests\Entity;

use Msalsas\VotingBundle\Entity\ReferenceClicks;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ReferenceClicksTest extends TestCase
{
    public function testReferenceClicks()
    {
        $referenceClicks = new ReferenceClicks();
        $referenceClicks->setReference(1);
        $referenceClicks->setClicks(100);

        $this->assertSame(1, $referenceClicks->getReference());
        $this->assertSame(100, $referenceClicks->getClicks());
    }
}