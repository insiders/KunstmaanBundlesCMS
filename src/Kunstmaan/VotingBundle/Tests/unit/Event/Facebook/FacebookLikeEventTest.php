<?php

namespace Kunstmaan\NodeBundle\Tests\Event;

use Kunstmaan\VotingBundle\Event\Facebook\FacebookLikeEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FacebookLikeEventTest
 */
class FacebookLikeEventTest extends TestCase
{
    public function testGetSet()
    {
        $request = new Request();
        $response = new Response();

        $event = new FacebookLikeEvent($request, $response, 100);

        $this->assertInstanceOf(Request::class, $event->getRequest());
        $this->assertInstanceOf(Response::class, $event->getReference());
        $this->assertEquals(100, $event->getValue());
    }
}
