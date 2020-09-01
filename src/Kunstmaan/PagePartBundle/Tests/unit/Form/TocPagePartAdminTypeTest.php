<?php

namespace Kunstmaan\PagePartBundle\Tests\Form;

use Kunstmaan\PagePartBundle\Form\TocPagePartAdminType;
use Kunstmaan\PagePartBundle\Tests\unit\Form\PagePartAdminTypeTestCase;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-08-20 at 12:29:42.
 */
class TocPagePartAdminTypeTest extends PagePartAdminTypeTestCase
{
    /**
     * @var TocPagePartAdminType
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->object = new TocPagePartAdminType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $this->object->buildForm($builder, array());
    }

    public function testConfigureOptions()
    {
        $this->object->configureOptions($this->resolver);
        $resolve = $this->resolver->resolve();
        $this->assertEquals($resolve['data_class'], 'Kunstmaan\PagePartBundle\Entity\TocPagePart');
    }
}
