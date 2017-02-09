<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\FormAccessResolver\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SimpleFormType
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Service\FormAccessResolver\Type
 */
class SimpleFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @author Martin Schindler
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('checkbox', CheckboxType::class);
        $builder->add('textfield', TextType::class);
    }

}