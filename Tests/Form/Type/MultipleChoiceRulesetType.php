<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 09.04.2017
 * Time: 10:59
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;


use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Rule;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\RuleSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MultipleChoiceRulesetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multipleChoiceField', ChoiceType::class, array(
                'choices' => array(
                    'Choice 1' => 1,
                    'Choice 2' => 2,
                ),
                'rules' => new RuleSet(array(
                    new Rule(1, array('dependency-1'), array('dependency-1')),
                    new Rule(2, array('dependency-2'), array('dependency-2')),
                )),
                'multiple' => true,
            ))
            ->add('dependency-1', TextType::class, array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('dependency-2', TextType::class, array(
                'constraints' => array(
                    new NotBlank()
                )
            ));
    }

}