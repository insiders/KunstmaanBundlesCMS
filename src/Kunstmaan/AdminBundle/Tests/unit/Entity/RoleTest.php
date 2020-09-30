<?php

namespace Kunstmaan\AdminBundle\Tests\Entity;

use Kunstmaan\AdminBundle\Entity\Role;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-04 at 16:53:56.
 */
class RoleTest extends TestCase
{
    /**
     * @var Role
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Role('ROLE_TEST');
    }

    public function test__construct()
    {
        $this->assertEquals('ROLE_TEST', $this->object->getRole());
    }

    public function testGetSetRole()
    {
        $this->object->setRole('ROLE_CUSTOM');
        $this->assertEquals('ROLE_CUSTOM', $this->object->getRole());
    }

    public function test__toString()
    {
        $this->assertEquals('ROLE_TEST', $this->object->__toString());
    }

    public function testGetId()
    {
        $this->assertEquals(null, $this->object->getId());
    }
}
