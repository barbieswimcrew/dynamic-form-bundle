<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;

use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedChoiceTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base\RelatedTypeTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;

/**
 * Class SimpleChoiceRulesetTypeTest
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type
 */
class SimpleChoiceRulesetTypeTest extends RelatedTypeTestCase
{

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceOneWithoutData()
    {
        $formData = array(
            'choices' => 1,
            'fieldOne' => '',
            'fieldTwo' => '',
        );

        $form = $this->factory->create(SimpleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(1, $form->getErrors(true)->count());
        $this->assertEquals(1, $form->get('fieldOne')->getErrors()->count());

    }

    /**
     * @author Anton Zoffmann
     */
    public function testThatNoDataIsSubmittedForInactiveChoices()
    {
        $formData = array(
            'choices' => 1,
            'fieldOne' => '',
            'fieldTwo' => 'asdf',
        );

        $form = $this->factory->create(SimpleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('', $form->get('fieldTwo')->getData());
    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceOneWithData()
    {
        $formData = array(
            'choices' => 1,
            'fieldOne' => 'asdf',
            'fieldTwo' => '',
        );

        $form = $this->factory->create(SimpleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->getErrors(true)->count());
        $this->assertEquals('asdf', $form->get('fieldOne')->getData());
    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceTwoWithoutData()
    {
        $formData = array(
            'choices' => 2,
            'fieldOne' => '',
            'fieldTwo' => '',
        );

        $form = $this->factory->create(SimpleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(1, $form->getErrors(true)->count());
        $this->assertEquals(1, $form->get('fieldTwo')->getErrors()->count());
    }

    /**
     * @author Anton Zoffmann
     */
    public function testSubmitChoiceTwoWithData()
    {
        $formData = array(
            'choices' => 2,
            'fieldOne' => '',
            'fieldTwo' => 'asdf',
        );

        $form = $this->factory->create(SimpleChoiceRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->getErrors(true)->count());
        $this->assertEquals('asdf', $form->get('fieldTwo')->getData());
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

    /**
     * private helper method for debugging issues
     * @param Form $form
     * @author Anton Zoffmann
     */
    private function dumpFormErrors(Form $form)
    {
        $iter = $form->getErrors(true);
        while (($error = $iter->current()) !== false) {
            var_dump($error->getMessage());
            $iter->next();
        }
    }
}