<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


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
            FormEvents::PRE_SUBMIT => "reconfigureFormWithSubmittedData",
            FormEvents::PRE_SET_DATA => "setOriginalOptions",
            FormEvents::POST_SET_DATA => "reconfigureFormWithSubmittedData",
        );
    }

    /**
     * here we always get the finally built form because we subscribed the pre_submit event
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function reconfigureFormWithSubmittedData(FormEvent $event)
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

        //todo array handling (special case for ChoiceType MULTIPLE - here we have checkboxes but no CheckboxTypes)
        //submitted data means selected checkboxes (equal to show fields)
        //not sumbitted data is equal to hide fields
        //PROBLEM: EMPTY DATA IS PER DEFAULT NULL

        # here we need to manually define the value for which the show OR hide algorithm should be executed
        $configuredFormType = $this->formPropertyHelper->getConfiguredFormTypeByForm($originForm);
        if (new ChoiceType() instanceof $configuredFormType and $originForm->getConfig()->getOption('multiple') === true){
            //todo get values of each chekboxes configuration
            foreach ($this->collectConfiguredValuesForMultipleChoiceType($originForm) as $configuredValue) {
                //todo reconfigure with hide
                $this->reconfigure($configuredValue, $parentForm, true);

            }

            foreach ($data as $value) {
                //ATTENTION - VALUE IS THE FIELDS VALUE
                //todo reconfigure with show
                $this->reconfigure($value, $parentForm);

            }

        }else {
            # here it is ok to do reconfiguration with the injected rulesets show/hide fields for each rule
            $this->reconfigure($data, $parentForm);

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
                $this->replaceForm($showField, array(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS => $showField->getConfig()->getOptions()), false);
            }

        }

    }

    /**
     * sets a new configured form to the parent of the original form
     * @param FormInterface $originForm
     * @param array $overrideOptions
     * @param boolean $hidden
     * @author Anton Zoffmann
     */
    private function replaceForm(FormInterface $originForm, array $overrideOptions, $hidden)
    {
        if (($resolvedType = $originForm->getConfig()->getType()) instanceof ResolvedFormTypeInterface) {
            $type = get_class($resolvedType->getInnerType());
        } else {
            $type = get_class($originForm->getConfig()->getType());
        }

        /** @var OptionsMergerService $optionsMergerService */
        $optionsMergerService = new OptionsMergerService();
        $mergedOptions = $optionsMergerService->getMergedOptions($originForm, $overrideOptions, $hidden);

        $replacementBuilder = $this->builder->create($originForm->getName(), $type, $mergedOptions);
        $replacementForm = $replacementBuilder->getForm();

        $parent = $originForm->getParent();
        $parent->offsetSet($replacementForm->getName(), $replacementForm);
    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @author Anton Zoffmann
     * @throws \Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\UndefinedFormAccessorException
     * @todo different reconfiguration for ChoiceTypes with Multiple Option....
     */
    private function reconfigure($data, FormInterface $parentForm, $hideFieldsOnly = false)
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
                    true
                );

            }

            if (!$hideFieldsOnly) {
                /** @var array $showFieldIds */
                $showFieldIds = $rule->getShowFields();

                foreach ($showFieldIds as $showFieldId) {
                    $showField = $this->formAccessResolver->getFormById($showFieldId, $parentForm);
                    $this->replaceForm($showField, $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS), false);
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
