<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 14.09.16
 * Time: 09:40
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\WrongIdDefinitionException;
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
    const CSS_HIDDEN_CLASS = "hidden";

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
        /** @var FormInterface $rootForm */
        $rootForm = $originForm->getRoot();

        $data = $event->getData();

        /**
         * THIS IS THE DESICION which rule should be effected
         * @var RuleInterface $rule
         */
        try {
            $rule = $this->ruleSet->getRule($data);

            /** @var array $hideFields */
            $hideFieldIds = $rule->getHideFields();

            foreach ($hideFieldIds as $hideFieldId) {

                $hideField = $this->getFormById($hideFieldId, $rootForm);

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
                $showField = $this->getFormById($showFieldId, $rootForm);

                $this->replaceForm(
                    $showField,
                    array(
                        'required' => true,
                        'mapped' => true,
                    ),
                    false
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
     * @author Anton Zoffmann
     */
    private function replaceForm(FormInterface $originForm, array $overrideOptions, $hidden)
    {
        if (($resolvedType = $originForm->getConfig()->getType()) instanceof ResolvedFormTypeInterface) {
            $type = get_class($resolvedType->getInnerType());
        } else {
            $type = get_class($originForm->getConfig()->getType());
        }

        $mergedOptions = $this->mergeOptions($originForm->getConfig()->getOptions(), $overrideOptions, $hidden);

        $replacementBuilder = $this->builder->create($originForm->getName(), $type, $mergedOptions);

        $replacementForm = $replacementBuilder->getForm();
        $parent = $originForm->getParent();
        $parent->offsetSet($originForm->getName(), $replacementForm);
    }

    /**
     * checks if the property accessor string is valid for the given formBuilder
     * @param string $cssFormId
     * @param FormInterface $child
     * @throws \Exception
     * @return FormInterface
     * @author Anton Zoffmann
     */
    private function getFormById($cssFormId, FormInterface $child)
    {
        $path = explode('_', $cssFormId);

        foreach ($path as $name) {
            if ($child->has($name)) {
                $child = $child->get($name);
            } else {
                throw new WrongIdDefinitionException($cssFormId, 500);
            }
        }
        return $child;
    }

    /**
     * @param array $originOptions
     * @param array $overrideOptions
     * @param boolean $hidden
     * @author Anton Zoffmann
     * @return array
     */
    private function mergeOptions(array $originOptions, array $overrideOptions, $hidden = false)
    {
        # array recursive because the options array contains other arrays to be merged (attr,...)
        $merged = array_merge($originOptions, $overrideOptions, array('auto_initialize' => false));

        # auto_initialize needfully is false for non root elements
//        $merged = array_merge($merged, array('auto_initialize' => false));

        # string concatenation for css classes
        if (isset($originOptions['attr']['class']) and isset($overrideOptions['attr']['class'])) {
            $merged['attr']['class'] = $this->mergeAttrClasses($originOptions['attr']['class'], $overrideOptions['attr']['class']);
        }

        # string concatenation for label css classes
        if (isset($originOptions['label_attr']['class']) and isset($overrideOptions['label_attr']['class'])) {
            $merged['label_attr']['class'] = $this->mergeAttrClasses($originOptions['label_attr']['class'], $overrideOptions['label_attr']['class']);
        }

        $merged['attr']['class'] = $this->handleHiddenClass($merged['attr'], $hidden);
        $merged['label_attr']['class'] = $this->handleHiddenClass($merged['label_attr'], $hidden);

        return $merged;
    }

    /**
     * merge class strings
     * @param string $originClasses
     * @param string $overrideClasses
     * @return string
     * @author Anton Zoffmann
     */
    private function mergeAttrClasses($originClasses, $overrideClasses)
    {
        $originClasses = explode(' ', $originClasses);
        $overrideClasses = explode(' ', $overrideClasses);

        return implode(' ', array_merge($originClasses, $overrideClasses));
    }

    /**
     * @param array $attributes
     * @param boolean $hidden
     * @author Anton Zoffmann
     * @return string
     */
    private function handleHiddenClass(array $attributes, $hidden)
    {
        # define classes string for further handling
        if (isset($attributes['class'])) {
            $classes = $attributes['class'];
        } elseif ($hidden === true) {
            return self::CSS_HIDDEN_CLASS;
        } else {
            $classes = "";
        }

        $hiddenContained = strpos($classes, self::CSS_HIDDEN_CLASS) !== false;

        if ($hiddenContained and $hidden === false) {
            # if hidden must be removed
            $classes = explode(' ', $classes);
            $key = array_search(self::CSS_HIDDEN_CLASS, $classes);
            unset($classes[$key]);

            return implode(' ', $classes);
        }

        if (!$hiddenContained and $hidden === true) {
            # if hidden must be added
            return sprintf("%s %s", $classes, self::CSS_HIDDEN_CLASS);
        }

        return $classes;
    }

}