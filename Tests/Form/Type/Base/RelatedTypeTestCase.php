<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class RelatedTypeTestCase
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type\Base
 */
abstract class RelatedTypeTestCase extends TypeTestCase
{

    /**
     * @author Anton Zoffmann
     */
    protected function setUp()
    {
        $validator = Validation::createValidator();
        $validationExtension = new ValidatorExtension($validator);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtension($validationExtension)
            ->addExtensions($this->getExtensions())
            ->addTypeExtensions($this->getTypeExtensions())
            ->getFormFactory();

        $this->dispatcher = new EventDispatcher();

        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    /**
     * add the rulesetbundle formtype extensions to be tested here
     *
     * @author Anton Zoffmann
     * @return array<TypeExtension>
     */
    protected function getTypeExtensions()
    {
        return array();
    }

}