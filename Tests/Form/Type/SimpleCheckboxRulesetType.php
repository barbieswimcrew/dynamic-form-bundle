<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;

use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\CheckboxRuleSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SimpleCheckboxRulesetType
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type
 */
class SimpleCheckboxRulesetType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @author Martin Schindler
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('checkbox', CheckboxType::class, array(
            'rules' => new CheckboxRuleSet(array('textfield')),
            'required' => false,
        ));

        $builder->add('textfield', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
            ),
        ));
    }

}