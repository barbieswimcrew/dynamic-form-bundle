<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;

use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedCheckboxTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base\RelatedTypeTestCase;
use Symfony\Component\DependencyInjection\Container;


/**
 * @author Anton Zoffmann
 * Date: 12.12.16
 * Time: 19:29
 */
class SimpleCheckboxRulesetTypeTest extends RelatedTypeTestCase
{

    /**
     * test if the a disabled checkbox disabled validation of a related FormType
     * @author Anton Zoffmann
     */
    public function testSubmitUncheckedWithoutData()
    {
        $formData = array(
            'checkbox' => false,
            'textfield' => ''
        );

        $form = $this->factory->create(SimpleCheckboxRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->getErrors(true)->count());
    }

    /**
     * assert that entered data will not be submitted if the related checkbox is unchecked
     * @author Anton Zoffmann
     */
    public function testSubmitUncheckedWithData()
    {
        $formData = array(
            'checkbox' => false,
            'textfield' => 'asdf'
        );

        $form = $this->factory->create(SimpleCheckboxRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->getErrors(true)->count());

        // ASSERT that submitted data will not be available in the form because it's disabled
        $this->assertTrue($form->get('textfield')->isEmpty());
    }

    /**
     * assert that an error will be thrown during validation when the checkbox is marked and no data is submitted in the related field
     * @author Anton Zoffmann
     */
    public function testSubmitCheckedWithoutData()
    {
        $formData = array(
            'checkbox' => true,
            'textfield' => ''
        );

        $form = $this->factory->create(SimpleCheckboxRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(1, $form->getErrors(true)->count());
    }

    /**
     * assert that everything works as usual if checked and data is submitted
     * @author Anton Zoffmann
     */
    public function testSubmitCheckedWithData()
    {
        $data = "asdf";

        $formData = array(
            'checkbox' => true,
            'textfield' => $data
        );

        $form = $this->factory->create(SimpleCheckboxRulesetType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(0, $form->getErrors(true)->count());
        $this->assertEquals($data, $form->get('textfield')->getData());
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
            new RelatedCheckboxTypeExtension($containerMock),
            new RelatedFormTypeExtension($containerMock),
        );
    }


}