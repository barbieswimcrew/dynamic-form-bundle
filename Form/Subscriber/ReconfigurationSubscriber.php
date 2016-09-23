<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Reconfiguration\ReconfigurationNotAllowedException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\OptionsMergerService;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * Class ReconfigurationSubscriber
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber
 */
class ReconfigurationSubscriber implements EventSubscriberInterface
{

    /** @var RuleSetInterface $ruleSet */
    private $ruleSet;

    /** @var FormBuilderInterface $builder */
    private $builder;

    /** @var FormAccessResolver */
    private $formAccessResolver;
    /** @var FormPropertyHelper */
    private $formPropertyHelper;

    /**
     * ReconfigurationSubscriber constructor.
     * @param RuleSetInterface $ruleSet
     * @param FormBuilderInterface $builder
     */
    public function __construct(RuleSetInterface $ruleSet, FormBuilderInterface $builder)
    {
        $this->ruleSet = $ruleSet;
        $this->builder = $builder;
        $this->formAccessResolver = new FormAccessResolver();
        $this->formPropertyHelper = new FormPropertyHelper();
    }

    /**
     * @author Anton Zoffmann
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => "listenToPreSetData",
            FormEvents::POST_SET_DATA => "listenToPostSetData",
            FormEvents::PRE_SUBMIT => "listenToPreSubmit",
        );
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function listenToPreSetData(FormEvent $event)
    {
        /** @var FormInterface $toggledForm */
        $subscribedForm = $event->getForm();
        $event->stopPropagation();
        $this->setOriginalOptionsOnShowFields($subscribedForm);
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function listenToPostSetData(FormEvent $event)
    {
        /** @var FormInterface $subscribedForm */
        $subscribedForm = $event->getForm();
        /** @var mixed $setData */
        $setData = $event->getData();
        $event->stopPropagation();
        $this->reconfigureTargetFormsByData($subscribedForm, $setData, false);
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function listenToPreSubmit(FormEvent $event)
    {
        /** @var FormInterface $subscribedForm */
        $subscribedForm = $event->getForm();
        /** @var mixed $submittedData */
        $submittedData = $event->getData();

        $this->reconfigureTargetFormsByData($subscribedForm, $submittedData, true);
    }

    /**
     * here we always get the finally built form because we subscribed the pre_submit event
     * @param FormEvent|FormInterface $toggleForm
     * @param mixed $data
     * @param boolean $blockFurtherReconfigurations
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @author Anton Zoffmann
     */
    private function reconfigureTargetFormsByData(FormInterface $toggleForm, $data, $blockFurtherReconfigurations)
    {

        /** @var FormInterface $parentForm */
        $parentForm = $toggleForm->getParent();

        // special type conversion for boolean data types (e.g. CheckboxType)
        if (is_bool($data)) {
            $data = ($data === true ? 1 : 0);
        }

        // workaround for initially disabled fields
        if (is_string($data) and strlen($data) == 0) {
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
                if(in_array($configuredValue, $data)){

                    $this->disableFields($configuredValue, $parentForm, false);

                } else{

                    $this->disableFields($configuredValue, $parentForm,$blockFurtherReconfigurations);

                }

            }

            foreach ($data as $value) {

                //ATTENTION - VALUE IS THE FIELDS VALUE
                $this->enableFields($value, $parentForm);

            }

        } else {
            # here it is ok to do reconfiguration with the injected rulesets show/hide fields for each rule
            $this->reconfigure($data, $parentForm, false, $blockFurtherReconfigurations);

        }

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
     * @param $data
     * @param FormInterface $parentForm
     * @param boolean $hideFieldsOnly
     * @param boolean $blockFurtherReconfigurations
     * @author Anton Zoffmann
     * @throws \Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\UndefinedFormAccessorException
     */
    private function reconfigure($data, FormInterface $parentForm, $hideFieldsOnly = false, $blockFurtherReconfigurations)
    {

        $this->disableFields($data, $parentForm, $blockFurtherReconfigurations);

        if (!$hideFieldsOnly) {

            $this->enableFields($data, $parentForm);

        }


    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @author Anton Zoffmann
     * @throws \Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\UndefinedFormAccessorException
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
     * sets a new configured form to the parent of the original form
     * @param FormInterface $originForm
     * @param array $overrideOptions
     * @param boolean $hidden
     * @param boolean $blockFurtherReconfigurations
     * @author Anton Zoffmann
     * @throws ReconfigurationNotAllowedException
     */
    private function replaceForm(FormInterface $originForm, array $overrideOptions, $hidden, $blockFurtherReconfigurations)
    {
        # the information we need is not whether the form was already reconfigured but more if further reconfiguration is allowed
        # e.g. we have a 2-hierarchy toggle and the "father" toggle is turned on - children should be allowed to do their own reconfiguration
        # e.g. BUT if the parent toggle is off - the children SHALL NOT reconfigure any of the fields, already reconfigured from the parent toggle
        try {
            if ($originForm->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ALREADY_RECONFIGURED) === true) {
                throw new ReconfigurationNotAllowedException();
            }

            if (($resolvedType = $originForm->getConfig()->getType()) instanceof ResolvedFormTypeInterface) {
                $type = get_class($resolvedType->getInnerType());
            } else {
                $type = get_class($originForm->getConfig()->getType());
            }

            /** @var OptionsMergerService $optionsMergerService */
            $optionsMergerService = new OptionsMergerService();
            $mergedOptions = $optionsMergerService->getMergedOptions($originForm, $overrideOptions, $hidden);

            # ATTENTION: this desicion-making property shall not be handled by any OptionsMerger which is under users controll.
            $mergedOptions[RelatedFormTypeExtension::OPTION_NAME_ALREADY_RECONFIGURED] = $blockFurtherReconfigurations;

            # setInheritData STOPS EVENT PROPAGATION DURING SAVEDATA()
            $replacementBuilder = $this->builder->create($originForm->getName(), $type, $mergedOptions);

            $replacementForm = $replacementBuilder->getForm();

            $parent = $originForm->getParent();
            $parent->offsetSet($replacementForm->getName(), $replacementForm);

        } catch (ReconfigurationNotAllowedException $s) {
            #nothing to do, eventually log but just break the reconfiguration workflow
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
