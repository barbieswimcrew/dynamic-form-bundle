<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * Class FormReconfigurator
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator
 */
class FormReconfigurator
{

    /** @var RuleSetInterface $ruleSet */
    private $ruleSet;

    /** @var FormBuilderInterface $builder */
    private $builder;

    /** @var FormAccessResolver $formAccessResolver */
    private $formAccessResolver;

    /** @var FormPropertyHelper $formPropertyHelper */
    private $formPropertyHelper;

    /**
     * FormReconfigurator constructor.
     * @param RuleSetInterface $ruleSet
     * @param FormBuilderInterface $builder
     * @param FormAccessResolver $formAccessResolver
     * @param FormPropertyHelper $formPropertyHelper
     */
    public function __construct(RuleSetInterface $ruleSet, FormBuilderInterface $builder, FormAccessResolver $formAccessResolver, FormPropertyHelper $formPropertyHelper)
    {
        $this->ruleSet = $ruleSet;
        $this->builder = $builder;
        $this->formAccessResolver = $formAccessResolver;
        $this->formPropertyHelper = $formPropertyHelper;
    }

    /**
     * Copying the original field's options data and dumping them into original_options
     * to get constraints and other data after submitting
     * @param FormInterface $toggleForm
     * @author Martin Schindler
     */
    public function setOriginalOptionsOnShowFields(FormInterface $toggleForm)
    {
        /** @var FormInterface $parentForm */
        $parentForm = $toggleForm->getParent();

        /**
         * THIS IS THE DECISION which rule should be effected
         * @var RuleInterface $rule
         */

        $rules = $this->ruleSet->getRules();
        /** @var RuleInterface $rule */
        foreach ($rules as $rule) {

            foreach ($rule->getShowFields() as $showFieldId) {
                $showField = $this->formAccessResolver->getFormById($showFieldId, $parentForm);

                # in case of multiple toggles (that means multiple event subscribers) we do not want to override our options again, that will destroy already done reconfigurations
                $originalOptions = $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS);
                if (is_array($originalOptions) and empty($originalOptions)) {
                    # break or continue - with a break we suggest that all fields of this rule are already set
                    $this->replaceForm($showField, array(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS => $showField->getConfig()->getOptions()), false, false);
                }

            }

        }

    }


    /**
     * here we always get the finally built form because we subscribed the pre_submit event
     * @param FormEvent|FormInterface $toggleForm
     * @param mixed $data
     * @param boolean $blockFurtherReconfigurations
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @author Anton Zoffmann
     */
    public function reconfigureTargetFormsByData(FormInterface $toggleForm, $data, $blockFurtherReconfigurations)
    {

        /** @var FormInterface $parentForm */
        $parentForm = $toggleForm->getParent();

        // special type conversion for boolean data types (e.g. CheckboxType)
        if (is_bool($data)) {
            $data = ($data === true ? 1 : 0);
        }

        // if a checkbox returns null, this means disabled so set it to 0
//        if($data === null){
//            $data = 0;
//        }

        // workaround for initially disabled fields
        // todo should this happen on pre submit reconfiguration or just on post-set-data???
        if (is_string($data) and strlen($data) == 0 or $data === null) {
            $data = $toggleForm->getConfig()->getOption('data');
        }


        //array handling (special case for ChoiceType MULTIPLE - here we have checkboxes but no CheckboxTypes)
        //submitted data means selected checkboxes (equal to show fields)
        //not sumbitted data is equal to hide fields
        //PROBLEM: EMPTY DATA IS PER DEFAULT NULL

        # here we need to manually define the value for which the show OR hide algorithm should be executed
        $configuredFormType = $this->formPropertyHelper->getConfiguredFormTypeByForm($toggleForm);
        if (new ChoiceType() instanceof $configuredFormType and $toggleForm->getConfig()->getOption('multiple') === true) {
            # get values of each chekboxes configuration

            foreach ($this->collectConfiguredValuesForMultipleChoiceType($toggleForm) as $configuredValue) {
                # actually we block reconfiguration in presubmit....
                # BUT here we got a second step to do...
                # todo just exclude the data values form the default fields to hide
                if (in_array($configuredValue, $data)) {

                    $this->disableFields($configuredValue, $parentForm, false);

                } else {

                    $this->disableFields($configuredValue, $parentForm, $blockFurtherReconfigurations);

                }

            }

            foreach ($data as $value) {

                //ATTENTION - VALUE IS THE FIELDS VALUE
                $this->enableFields($value, $parentForm);   //todo check if here is a reset or a real enabling required

            }

        } else {
            //todo
            $this->disableFields($data, $parentForm, $blockFurtherReconfigurations);

//            if (!false) {     //this was like it was wrapped into the reconfigure method

            $this->enableFields($data, $parentForm);

//            }
            # here it is ok to do reconfiguration with the injected rulesets show/hide fields for each rule
//            $this->reconfigure($data, $parentForm, false, $blockFurtherReconfigurations);   //todo extract desicion whether to enable or disable to this place, remove reconfigure method

        }

    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @author Anton Zoffmann
     * @throws \Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\UndefinedFormAccessorException
     * @todo this method does an reset, enabling looks different to this code... rename the method and create a new one
     */
    private function enableFields($data, FormInterface $parentForm)
    {
        try {
            /**
             * THIS IS THE DECISION which rule should be effected
             * @var RuleInterface $rule
             */
            $rule = $this->ruleSet->getRule($data);

            /** @var array $showFieldIds */
            $showFieldIds = $rule->getShowFields();
            # reconfiguration restriction shall only be applied if the parend form is disabled. when it is enabled,
            # there is no reason to restrict 2nd level toggles and their targets
            $blockFurtherReconfigurations = false;

            foreach ($showFieldIds as $showFieldId) {
                $showField = $this->formAccessResolver->getFormById($showFieldId, $parentForm);
                $this->replaceForm(
                    $showField,
                    $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS),
                    false,
                    $blockFurtherReconfigurations
                );
            }

        } catch (NoRuleDefinedException $exception) {
            # nothing to to if no rule is defined
        }
    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @param $blockFurtherReconfigurations
     * @author Martin Schindler
     */
    private function disableFields($data, FormInterface $parentForm, $blockFurtherReconfigurations)
    {
        try {
            /**
             * THIS IS THE DECISION which rule should be effected
             * @var RuleInterface $rule
             */
            $rule = $this->ruleSet->getRule($data);

            /** @var array $hideFields */
            $hideFieldIds = $rule->getHideFields();

            foreach ($hideFieldIds as $hideFieldId) {

                $hideField = $this->formAccessResolver->getFormById($hideFieldId, $parentForm);

                $this->replaceForm(
                    $hideField,
                    array(
                        'constraints' => array(),
                        'mapped' => false,
                        'disabled' => true
                    ),
                    true,
                    $blockFurtherReconfigurations
                );

            }

        } catch (NoRuleDefinedException $exception) {
            # nothing to to if no rule is defined
        }
    }

    /**
     * @param FormInterface $originForm
     * @author Anton Zoffmann
     * @return array
     */
    private function collectConfiguredValuesForMultipleChoiceType(FormInterface $originForm)
    {
        $configuredValues = array();
        /** @var FormInterface $child */
        foreach ($originForm as $child) {
            $configuredValues[] = $child->getConfig()->getOption('value');
        }

        return $configuredValues;
    }
}