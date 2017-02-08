<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\RuleSet;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RelatedChoiceTypeExtension
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension
 */
class RelatedChoiceTypeExtension extends AbstractRelatedExtension
{

    /**
     * Returns the name of the form field type being extended
     * @author Martin Schindler
     * @return string
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /**
     * Registering the new form field options
     * @param OptionsResolver $resolver
     * @author Martin Schindler
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defineNewFieldOption($resolver, self::OPTION_NAME_RULES, null, array(RuleSetInterface::class, 'null'));
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @author Martin Schindler
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        /** @var RuleSet $ruleSet */
        $ruleSet = $options[self::OPTION_NAME_RULES];

        if (!$ruleSet instanceof RuleSetInterface) {
            return;
        }

        //set the data-sfhandler-id to our choice-types label too
        $view->vars['label_attr'][$this->attr['id']] = $view->vars['attr'][$this->attr['id']];

        /** @var ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            try {
                $rule = $ruleSet->getRule($choice->value);
                $choice->attr = $this->replaceAttributes($choice, $form, $rule);
                $choice->attr[$this->attr['id']] = $view->vars['attr'][$this->attr['id']];
            } catch (NoRuleDefinedException $exception) {
                # nothing to do, just interrupt the workflow
            }
        }

        # if expanded option is set true, append the data attributes to the underlying form child radio elements
        if ($options['expanded']) {
            foreach ($view as $childView) {
                try {
                    $rule = $ruleSet->getRule($childView->vars['value']);
                    $childView->vars['attr'] = $this->replaceAttributes($childView, $form, $rule);
                    $childView->vars['attr'][$this->attr['id']] = $view->vars['attr'][$this->attr['id']];
                    $childView->vars['label_attr'][$this->attr['id']] = $view->vars['attr'][$this->attr['id']];
                } catch (NoRuleDefinedException $exception) {
                    # nothing to do, just interrupt the workflow
                }
            }
        }

    }

}