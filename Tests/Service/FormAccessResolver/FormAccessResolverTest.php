<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\UndefinedFormAccessorException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base\RelatedTypeTestCase;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\FormAccessResolver\Type\ComplexFormType;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\FormAccessResolver\Type\SimpleFormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

/**
 * Class FormAccessResolverTest
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type
 */
class FormAccessResolverTest extends RelatedTypeTestCase
{

    /** @var FormFactory $formFactory */
    private $formFactory;

    /** @var FormAccessResolver $resolver */
    private $resolver;

    /**
     * @author Martin Schindler
     */
    public function setUp()
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->resolver = new FormAccessResolver();
    }

    /**
     * @author Martin Schindler
     */
    public function testIfUndefinedFormAccessorExceptionWillBeThrown()
    {
        $this->expectException(UndefinedFormAccessorException::class);
        $form = $this->formFactory->create();
        $this->resolver->getFormById("text", $form);
    }

    /**
     * @author Martin Schindler
     */
    public function testIfResolverProvidesFormByIdOnNonHierarchicalForm()
    {
        $form = $this->formFactory->create();
        $form->add("textfield", TextType::class);

        $textfield = $this->resolver->getFormById("textfield", $form);

        $this->assertEquals($form->get("textfield"), $textfield);
    }

    /**
     * @author Martin Schindler
     */
    public function testIfResolverProvidesFormByIdOnHierarchicalForm()
    {
        $form = $this->formFactory->create();
        $form->add("simple", SimpleFormType::class);

        $checkbox = $this->resolver->getFormById("simple>checkbox", $form);
        $textfield = $this->resolver->getFormById("simple>textfield", $form);

        $this->assertEquals($form->get('simple')->get("checkbox"), $checkbox);
        $this->assertEquals($form->get('simple')->get("textfield"), $textfield);
    }

    /**
     * @author Martin Schindler
     */
    public function testIfResolverReturnsValidFullNameOnNonHierarchicalForm()
    {

        $form = $this->formFactory->create();
        $form->add("textfield", TextType::class);

        $fullName = $this->resolver->getFullName("textfield", $form);

        $this->assertEquals("form_textfield", $fullName);
    }

    /**
     * @param $id
     * @param $expected
     * @author Martin Schindler
     * @dataProvider hierarchicalFormProvider
     */
    public function testIfResolverReturnsValidFullNameOnHierarchicalForm($id, $expected)
    {

        $form = $this->formFactory->create();
        $form->add("complex", ComplexFormType::class);

        $fullName = $this->resolver->getFullName($id, $form->get("complex"));
        $this->assertEquals($expected, $fullName);
    }

    /**
     * @author Martin Schindler
     * @return array
     */
    public function hierarchicalFormProvider()
    {
        return array(
            array("complex>textfield", "form_complex_textfield"),
            array("complex>checkbox", "form_complex_checkbox"),
            array("complex>simple>textfield", "form_complex_simple_textfield"),
            array("complex>simple>checkbox", "form_complex_simple_checkbox"),
        );
    }
}