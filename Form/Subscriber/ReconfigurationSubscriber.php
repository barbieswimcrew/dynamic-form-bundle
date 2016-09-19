<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\WrongIdDefinitionException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension\AbstractRelatedExtension;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\OptionsMergerService;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function __construct(RuleSetInterface $ruleSet, FormBuilderInterface $builder)
    {
        $this->ruleSet = $ruleSet;
        $this->builder = $builder;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => "reconfigureFormWithSumbittedData",
            FormEvents::PRE_SET_DATA => "setOriginalOptions",
            FormEvents::POST_SET_DATA => "reconfigureFormWithSumbittedData",
        );
    }

    /**
     * here we always get the finally built form because we subscribed the pre_submit event
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function reconfigureFormWithSumbittedData(FormEvent $event)
    {

        /** @var FormInterface $originForm */
        $originForm = $event->getForm();
        /** @var FormInterface $parentForm */
        $parentForm = $originForm->getParent();

        $data = $event->getData();

        // special type conversion for boolean data types (e.g. CheckboxType)
        if(is_bool($data)){
            $data = ($data === true ? 1 : 0);
        }

        /**
         * THIS IS THE DESICION which rule should be effected
         * @var RuleInterface $rule
         */
        try {
            $rule = $this->ruleSet->getRule($data);

            /** @var array $hideFields */
            $hideFieldIds = $rule->getHideFields();

            foreach ($hideFieldIds as $hideFieldId) {

                $hideField = $this->getFormById($hideFieldId, $parentForm);

                $this->replaceForm(
                    $hideField,
                    array(
                        'required' => false,
                        'constraints' => array(),
                        'mapped' => false,
                    ),
                    true
                );

            }

            /** @var array $showFieldIds */
            $showFieldIds = $rule->getShowFields();

            foreach ($showFieldIds as $showFieldId) {
                $showField = $this->getFormById($showFieldId, $parentForm);
                $this->replaceForm($showField, $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS), false);
            }
        } catch (NoRuleDefinedException $exception) {
            # nothing to to if no rule is defined
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
                $showField = $this->getFormById($showFieldId, $parentForm);
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
     * checks if the property accessor string is valid for the given formBuilder
     * @param string $cssFormId
     * @param FormInterface $parent
     * @throws \Exception
     * @return FormInterface
     * @author Anton Zoffmann
     */
    private function getFormById($cssFormId, FormInterface $parent)
    {
        $path = explode('_', $cssFormId);

        foreach ($path as $name) {
            if ($parent->has($name)) {
                $parent = $parent->get($name);
            } elseif ($this->isFormNameCamelCased($cssFormId, $parent)) {
                # todo check if element is camelCase, because then it is be seperated by an underscore...
            } else {
                throw new WrongIdDefinitionException($cssFormId, 500);
            }
        }

        return $parent;
    }

    /**
     * @param $cssId
     * @param FormInterface $parent
     * @author Anton Zoffmann
     */
    private function isFormNameCamelCased($cssId, FormInterface $parent)
    {
        //todo implement
    }


}
