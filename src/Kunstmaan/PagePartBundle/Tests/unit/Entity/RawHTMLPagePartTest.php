<?php

namespace Kunstmaan\PagePartBundle\Tests\Entity;

use Kunstmaan\PagePartBundle\Entity\RawHTMLPagePart;
use PHPUnit\Framework\TestCase;

/**
 * Class RawHTMLPagePartTest
 */
class RawHTMLPagePartTest extends TestCase
{
    /**
     * @var RawHTMLPagePart
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new RawHTMLPagePart();
    }

    public function testToString()
    {
        $this->assertEquals('RawHTMLPagePart ' . htmlentities($this->object->getContent()), $this->object->__toString());
    }

    public function testGetDefaultView()
    {
        $this->assertEquals('@KunstmaanPagePart/RawHTMLPagePart/view.html.twig', $this->object->getDefaultView());
    }

    public function testSetGetContent()
    {
        $this->object->setContent('tèst content with s3ç!àL');
        $this->assertEquals('tèst content with s3ç!àL', $this->object->getContent());
    }

    public function testGetDefaultAdminType()
    {
        $this->assertEquals('Kunstmaan\PagePartBundle\Form\RawHTMLPagePartAdminType', $this->object->getDefaultAdminType());
    }
}
