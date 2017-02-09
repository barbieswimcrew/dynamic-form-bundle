<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base\RelatedTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

/**
 * Class FormPropertyHelperTest
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type
 */
class FormPropertyHelperTest extends RelatedTypeTestCase
{

    /** @var FormFactory $formFactory */
    private $formFactory;

    /** @var FormPropertyHelper $resolver */
    private $helper;

    /**
     * @author Martin Schindler
     */
    public function setUp()
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->helper = new FormPropertyHelper();
    }

    /**
     * @author Martin Schindler
     */
    public function testIfHelperReturnsConfiguredFormType()
    {
        $form = $this->formFactory->create();
        $form->add("textfield", TextType::class);
        $configuredFormType = $this->helper->getConfiguredFormTypeByForm($form->get("textfield"));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\Type\TextType', $configuredFormType);
    }

}