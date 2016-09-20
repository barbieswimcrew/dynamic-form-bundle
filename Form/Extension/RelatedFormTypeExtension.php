<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber\ReconfigurationSubscriber;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedFormTypeExtension extends AbstractRelatedExtension
{

    const OPTION_NAME_ORIGINAL_OPTIONS = "original_options";

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
     * Registering the new form field options
     * @param OptionsResolver $resolver
     * @author Martin Schindler
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defineNewFieldOption($resolver, self::OPTION_NAME_ORIGINAL_OPTIONS, array(), array('array'));
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