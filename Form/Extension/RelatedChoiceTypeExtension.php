<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\RuleSet;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedChoiceTypeExtension extends AbstractRelatedExtension
{

    const OPTION_NAME_RULES = "rules";

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

        /** @var ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            try {
                $rule = $ruleSet->getRule($choice->value);
                $choice->attr = $this->replaceAttributes($choice, $rule);
            } catch (NoRuleDefinedException $exception) {
                # nothing to do, just interrupt the workflow
            }
        }

        # if expanded option is set true, append the data attributes to the underlying form child radio elements
        if ($options['expanded']) {
            foreach ($view as $childView) {
                try {
                    $rule = $ruleSet->getRule($childView->vars['value']);
                    $childView->vars['attr'] = $this->replaceAttributes($childView, $rule);
                } catch (NoRuleDefinedException $exception) {
                    # nothing to do, just interrupt the workflow
                }
            }
        }

    }

    /**
     * Helper method to replace the form field attributes array data
     * @param $childView
     * @param RuleInterface $rule
     * @author Martin Schindler
     * @return array
     */
    private function replaceAttributes($childView, RuleInterface $rule)
    {

        $additionalAttributes = array();
        $showFields = $rule->getShowFields();
        $hideFields = $rule->getHideFields();

        if (count($showFields) > 0) {
            $additionalAttributes[$this->attr['targetsShow']] = implode(',', $showFields);
        }

        if (count($hideFields) > 0) {
            $additionalAttributes[$this->attr['targetsHide']] = implode(',', $hideFields);
        }

        return array_replace(isset($childView->attr) ? $childView->attr : array(), $additionalAttributes);
    }

}