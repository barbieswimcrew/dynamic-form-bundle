<?php


namespace Barbieswimcrew\Bundle\DynamicFormBundle\Tests\Form\Type;


use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Rule;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\RuleSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SimpleChoiceRulesetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('choices', ChoiceType::class, array(
            'choices' => array(
                'Choice 1' => 1,
                'Choice 2' => 2,
            ),
            'rules' => new RuleSet(array(
                new Rule(1, array('fieldOne'), array('fieldTwo')),
                new Rule(2, array('fieldTwo'), array('fieldOne')),
            )),
        ));

        $builder->add('fieldOne', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
            )
        ));

        $builder->add('fieldTwo', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
            )
        ));
    }

}