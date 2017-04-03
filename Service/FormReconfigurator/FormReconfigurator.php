<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReplacement\FormReplacementService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\ChoiceTypeMultipleReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\DefaultReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\OptionsMergerService;
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

    /** @var FormReplacementService $formReplacer */
    private $formReplacer;

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
        $this->formReplacer = new FormReplacementService($builder, new OptionsMergerService());

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
                    $this->formReplacer->replaceForm($showField, array(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS => $showField->getConfig()->getOptions()), false, false);
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

        $choiceHandler = new ChoiceTypeMultipleReconfigurationHandler($this->ruleSet, $this->formAccessResolver, $this->formReplacer, $this->formPropertyHelper);
        $defaultHandler = new DefaultReconfigurationHandler($this->ruleSet, $this->formAccessResolver, $this->formReplacer);

        if ($choiceHandler->isResponsible($toggleForm)) {
            $choiceHandler->handle($data, $blockFurtherReconfigurations);
        } elseif ($defaultHandler->isResponsible($toggleForm)) {
            $defaultHandler->handle($data, $blockFurtherReconfigurations);
        }

    }
    
}