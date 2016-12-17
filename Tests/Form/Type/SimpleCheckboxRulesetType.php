<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 12.12.16
 * Time: 19:39
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Tests\Form\Type;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\CheckboxRuleSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SimpleCheckboxRulesetType extends AbstractType
{
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