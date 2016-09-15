<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 11.09.16
 * Time: 11:55
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber\ReconfigurationSubscriber;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class RelatedFormTypeExtension extends AbstractRelatedExtension
{

    /**
     * Returns the name of the form field type being extended
     * @author Martin Schindler
     * @return string
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * Adding data properties to the current form fields view html attributes
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @author Martin Schindler
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        # add valid target data name to itself
        $view->vars['attr'][$this->attr['id']] = $view->vars['id'];

        # add temp required data attribute if field is set required true
        if($options['required'] === true){
            $view->vars['attr'][$this->attr['isRequired']] = "required";
        }
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @author Anton Zoffmann
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($builder->hasOption(RelatedChoiceTypeExtension::OPTION_NAME_RULES) and ($ruleset = $builder->getOption(RelatedChoiceTypeExtension::OPTION_NAME_RULES)) instanceof RuleSetInterface) {
            $builder->addEventSubscriber(new ReconfigurationSubscriber($ruleset, $builder));
        }
    }

}