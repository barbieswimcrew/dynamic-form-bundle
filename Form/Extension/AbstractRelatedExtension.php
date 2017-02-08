<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractRelatedExtension
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension
 */
abstract class AbstractRelatedExtension extends AbstractTypeExtension
{

    /**
     *
     */
    const OPTION_NAME_RULES = "rules";

    /** @var array $attr */
    protected $attr;

    /** @var FormAccessResolver */
    protected $formAccessResolver;

    /**
     * AbstractRelatedExtension constructor.
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config = array())
    {
        # if at least one config parameter exists in container
        if ($container->hasParameter('barbieswimcrew_dynamic_form.strict_mode')) {
            $this->attr['strictMode'] = $container->getParameter('barbieswimcrew_dynamic_form.strict_mode');
            $this->attr['id'] = $container->getParameter('barbieswimcrew_dynamic_form.data_attr_id');
            $this->attr['targetsShow'] = $container->getParameter('barbieswimcrew_dynamic_form.data_attr_targets_show');
            $this->attr['targetsHide'] = $container->getParameter('barbieswimcrew_dynamic_form.data_attr_targets_hide');
        }

        # override attributes if custom config has been injected
        foreach ($config as $key => $value) {
            $this->attr[$key] = $value;
        }

        $this->formAccessResolver = new FormAccessResolver();
    }

    /**
     * Helper method to create new field options
     * @param OptionsResolver $resolver
     * @param $optionName
     * @param $defaultValue
     * @param array $allowedTypes
     * @author Martin Schindler
     */
    protected function defineNewFieldOption(OptionsResolver $resolver, $optionName, $defaultValue, array $allowedTypes)
    {
        $resolver->setDefined($optionName);
        $resolver->setDefault($optionName, $defaultValue);
        $resolver->setAllowedTypes($optionName, $allowedTypes);
    }

    /**
     * Helper method to replace the form field attributes array data
     * @param FormView|ChoiceView $childView
     * @param FormInterface $form
     * @param RuleInterface $rule
     * @author Martin Schindler
     * @return array
     */
    protected function replaceAttributes($childView, FormInterface $form, RuleInterface $rule)
    {

        $showFields = $rule->getShowFields();
        $hideFields = $rule->getHideFields();

        foreach ($showFields as &$showField) {
            $showField = $this->formAccessResolver->getFullName($showField, $form);
        }

        foreach ($hideFields as &$hideField) {
            $hideField = $this->formAccessResolver->getFullName($hideField, $form);
        }

        $additionalAttributes = array();
        $additionalAttributes[$this->attr['targetsShow']] = implode(',', $showFields);
        $additionalAttributes[$this->attr['targetsHide']] = implode(',', $hideFields);

        return array_replace(isset($childView->attr) ? $childView->attr : array(), $additionalAttributes);
    }
}