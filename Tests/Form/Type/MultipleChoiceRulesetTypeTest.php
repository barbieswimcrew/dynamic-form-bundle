<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 09.04.2017
 * Time: 11:09
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;


use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedChoiceTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base\RelatedTypeTestCase;
use Symfony\Component\DependencyInjection\Container;

class MultipleChoiceRulesetTypeTest extends RelatedTypeTestCase
{
    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceOneWithoutData()
    {
        $formData = array(
            'multipleChoiceField' => array(1),
            'dependency-1' => '',
            'dependency-2' => 'asdf',
        );

        $form = $this->factory->create(MultipleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(1, $form->get('dependency-1')->getErrors()->count());
    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceTwoWithFalseData()
    {
        $formData = array(
            'multipleChoiceField' => array(1),
            'dependency-1' => '',
            'dependency-2' => 'asdf',
        );

        $form = $this->factory->create(MultipleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(1, $form->get('dependency-1')->getErrors()->count());

        $this->assertEquals(0, $form->get('dependency-2')->getErrors()->count());
        $this->assertEquals('', $form->get('dependency-2')->getData());
    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceOneAndTwoWithoutData()
    {
        $formData = array(
            'multipleChoiceField' => array(1,2),
            'dependency-1' => '',
            'dependency-2' => '',
        );

        $form = $this->factory->create(MultipleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(2, $form->getErrors(true)->count());
        $this->assertEquals(1, $form->get('dependency-1')->getErrors()->count());
        $this->assertEquals(1, $form->get('dependency-2')->getErrors()->count());

    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceOneWithDataAndTwoWithoutData()
    {
        $formData = array(
            'multipleChoiceField' => array(1),
            'dependency-1' => 'asdf',
            'dependency-2' => '',
        );

        $form = $this->factory->create(MultipleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->get('dependency-1')->getErrors()->count());
        $this->assertEquals('asdf', $form->get('dependency-1')->getData());

        $this->assertEquals(0, $form->get('dependency-2')->getErrors()->count());

    }

    /**
     * @author Anton Zoffmann
     * @return array
     */
    protected function getTypeExtensions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Container $containerMock */
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('hasParameter')->willReturn(false);

        return array(
            new RelatedChoiceTypeExtension($containerMock),
            new RelatedFormTypeExtension($containerMock),
        );
    }
}