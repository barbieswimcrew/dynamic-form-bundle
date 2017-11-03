<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator;

use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReplacement\FormReplacementService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base\ReconfigurationHandlerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\ChoiceTypeMultipleReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\DefaultReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormReconfigurator
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator
 */
class FormReconfigurator
{

    /** @var RuleSetInterface $ruleSet */
    private $ruleSet;

    /** @var FormAccessResolver $formAccessResolver */
    private $formAccessResolver;

    /** @var FormReplacementService $formReplacer */
    private $formReplacer;

    /** @var array $handlers */
    private $handlers;

    /** @var ReconfigurationHandlerInterface $defaultHandler */
    private $defaultHandler;

    /**
     * FormReconfigurator constructor.
     * @param RuleSetInterface $ruleSet
     * @param FormAccessResolver $formAccessResolver
     * @param FormPropertyHelper $formPropertyHelper
     * @param FormReplacementService $formReplacer
     * @todo implement as service via DI because of interchangability of handlers/defaulthandler
     */
    public function __construct(RuleSetInterface $ruleSet, FormAccessResolver $formAccessResolver, FormPropertyHelper $formPropertyHelper, FormReplacementService $formReplacer)
    {
        $this->ruleSet = $ruleSet;
        $this->formAccessResolver = $formAccessResolver;

        $this->formReplacer = $formReplacer;

        $this->handlers = array();

        $this->handlers[] = new ChoiceTypeMultipleReconfigurationHandler($ruleSet, $formAccessResolver, $formReplacer, $formPropertyHelper);
        $this->defaultHandler = new DefaultReconfigurationHandler($ruleSet, $formAccessResolver, $formReplacer);

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
     * @author Anton Zoffmann
     * @return void
     */
    public function reconfigureTargetFormsByData(FormInterface $toggleForm, $data, $blockFurtherReconfigurations)
    {

        // special type conversion for boolean data types (e.g. CheckboxType)
        if (is_bool($data)) {
            $data = ($data === true ? 1 : 0);
        }

        // workaround for initially disabled fields
        // todo should this happen on pre submit reconfiguration or just on post-set-data???
        if (is_string($data) and strlen($data) == 0 or $data === null) {
            $data = $toggleForm->getConfig()->getOption('data');
        }

        /** @var array $handlers */
        $handlers = $this->handlers;
        $handlers[] = $this->defaultHandler;

        /** @var ReconfigurationHandlerInterface $handler */
        foreach ($handlers as $handler) {

            if (!$handler->isResponsible($toggleForm)) {
                continue;
            }

            $handler->handle($data, $blockFurtherReconfigurations);

            return;
        }

    }

    /**
     * @param array $handlers
     * @return $this
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
        return $this;
    }

    /**
     * @param ReconfigurationHandlerInterface $handler
     * @return $this
     */
    public function addHandler(ReconfigurationHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * @param ReconfigurationHandlerInterface $handler
     * @
     * @return $this
     */
    public function setDefaultHandler(ReconfigurationHandlerInterface $handler)
    {
        $this->defaultHandler = $handler;
        return $this;
    }

}