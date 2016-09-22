<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Reconfiguration\MultipleReconfigurationException;
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

    public function __construct(RuleSetInterface $ruleSet, FormBuilderInterface $builder)
    {
        $this->ruleSet = $ruleSet;
        $this->builder = $builder;
        $this->formAccessResolver = new FormAccessResolver();
        $this->formPropertyHelper = new FormPropertyHelper();
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => "setOriginalOptions",
            FormEvents::POST_SET_DATA => "doPostSetDataReconfiguration",
            FormEvents::PRE_SUBMIT => "doPreSubmitReconfiguration",
        );
    }

    public function doPreSubmitReconfiguration(FormEvent $event)
    {
        $this->reconfigureFormWithData($event, false);
    }

    public function doPostSetDataReconfiguration(FormEvent $event)
    {
        $this->reconfigureFormWithData($event, false);
    }

    /**
     * here we always get the finally built form because we subscribed the pre_submit event
     * @param FormEvent $event
     * @param boolean $isAlreadyReconfigured
     * @author Anton Zoffmann
     */
    private function reconfigureFormWithData(FormEvent $event, $isAlreadyReconfigured)
    {

        /** @var FormInterface $originForm */
        $originForm = $event->getForm();
        /** @var FormInterface $parentForm */
        $parentForm = $originForm->getParent();

        $data = $event->getData();

        // special type conversion for boolean data types (e.g. CheckboxType)
        if (is_bool($data)) {
            $data = ($data === true ? 1 : 0);
        }

        // workaround for initially disabled fields
        if (is_string($data) and strlen($data) == 0) {
            $data = $event->getForm()->getConfig()->getOption('data');
            $isAlreadyReconfigured = true;
        }

        //todo array handling (special case for ChoiceType MULTIPLE - here we have checkboxes but no CheckboxTypes)
        //submitted data means selected checkboxes (equal to show fields)
        //not sumbitted data is equal to hide fields
        //PROBLEM: EMPTY DATA IS PER DEFAULT NULL

        # here we need to manually define the value for which the show OR hide algorithm should be executed
        $configuredFormType = $this->formPropertyHelper->getConfiguredFormTypeByForm($originForm);
        if (new ChoiceType() instanceof $configuredFormType and $originForm->getConfig()->getOption('multiple') === true) {
            //todo get values of each chekboxes configuration
            foreach ($this->collectConfiguredValuesForMultipleChoiceType($originForm) as $configuredValue) {
                //todo reconfigure with hide
                $this->reconfigure($configuredValue, $parentForm, true, $isAlreadyReconfigured);

            }

            foreach ($data as $value) {
                //ATTENTION - VALUE IS THE FIELDS VALUE
                //todo reconfigure with show
                $this->reconfigure($value, $parentForm, false, $isAlreadyReconfigured);

            }

        } else {
            # here it is ok to do reconfiguration with the injected rulesets show/hide fields for each rule
            $this->reconfigure($data, $parentForm, false, $isAlreadyReconfigured);

        }

    }

    /**
     * Copying the original field's options data and dumping them into original_options
     * to get constraints and other data after submitting
     * @param FormEvent $event
     * @author Martin Schindler
     */
    public function setOriginalOptions(FormEvent $event)
    {
        /** @var FormInterface $originForm */
        $originForm = $event->getForm();
        /** @var FormInterface $parentForm */
        $parentForm = $originForm->getParent();

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
                if(is_array($originalOptions) and !empty($originalOptions)){
                    # break or continue - with a break we suggest that all fields of this rule are already set
                    continue;
                }

                //todo actually setOriginals is calls multiple times per request per field. this shall not happen because it overrides already set options
                $this->replaceForm($showField, array(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS => $showField->getConfig()->getOptions()), false, false);
            }

        }

    }

    /**
     * sets a new configured form to the parent of the original form
     * @param FormInterface $originForm
     * @param array $overrideOptions
     * @param boolean $hidden
     * @param boolean $isAlreadyReconfigured
     * @author Anton Zoffmann
     * @throws MultipleReconfigurationException
     */
    private function replaceForm(FormInterface $originForm, array $overrideOptions, $hidden, $isAlreadyReconfigured)
    {
        # todo the information we need is not whether the form was already reconfigured but more if further reconfiguration is allowed
        # todo e.g. we have a 2-hierarchy toggle and the "father" toggle is turned on - children should be allowed to do their own reconfiguration
        # todo e.g. BUT if the parent toggle is off - the children SHALL NOT reconfigure any of the fields, already reconfigured from the parent toggle
        try {
            if ($originForm->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ALREADY_RECONFIGURED) === true) {
                throw new MultipleReconfigurationException();
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
            $mergedOptions[RelatedFormTypeExtension::OPTION_NAME_ALREADY_RECONFIGURED] = $isAlreadyReconfigured;

            $replacementBuilder = $this->builder->create($originForm->getName(), $type, $mergedOptions);
            $replacementForm = $replacementBuilder->getForm();

            $parent = $originForm->getParent();
            $parent->offsetSet($replacementForm->getName(), $replacementForm);

        } catch (MultipleReconfigurationException $s) {
            #nothing to do, eventually log but just break the reconfiguration workflow
        }
    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @param boolean $hideFieldsOnly
     * @param boolean $isAlreadyReconfigured
     * @author Anton Zoffmann
     * @throws \Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\UndefinedFormAccessorException
     * @todo different reconfiguration for ChoiceTypes with Multiple Option....
     */
    private function reconfigure($data, FormInterface $parentForm, $hideFieldsOnly = false, $isAlreadyReconfigured)
    {
        /**
         * THIS IS THE DECISION which rule should be effected
         * @var RuleInterface $rule
         */
        try {
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
                    $isAlreadyReconfigured
                );

            }

            if (!$hideFieldsOnly) {
                /** @var array $showFieldIds */
                $showFieldIds = $rule->getShowFields();

                foreach ($showFieldIds as $showFieldId) {
                    $showField = $this->formAccessResolver->getFormById($showFieldId, $parentForm);
                    $this->replaceForm(
                        $showField,
                        $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS),
                        false,
                        $isAlreadyReconfigured
                    );
                }
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
