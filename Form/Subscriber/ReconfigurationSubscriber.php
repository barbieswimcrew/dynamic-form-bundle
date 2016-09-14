<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 14.09.16
 * Time: 09:40
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Form\Subscriber;


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
        $data = $event->getData();

        /**
         * THIS IS THE DESICION which rule should be effected
         * @var RuleInterface $rule
         * @todo throw exception if no rule for data exists -> should be done with the builder->add method call because its thrown with programming time
         */
        $rule = $this->ruleSet->getRule($data);

        /** @var array $hideFields */
        $hideFieldIds = $rule->getHideFields();

        foreach ($hideFieldIds as $hideFieldId) {

            $hideField = $this->getFormForIdRecursive($hideFieldId, $originForm->getRoot());

            $this->replaceForm(
                $hideField,
                array(
                    'required' => false,
                    'constraints' => array(),
                    'attr' => array('class' => 'hidden'),
                    'label_attr' => array('class' => 'hidden')
                ));

        }

        //todo show fields

    }

    /**
     * sets a new configured form to the parent of the original form
     * @param FormInterface $originForm
     * @param array $overrideOptions
     * @author Anton Zoffmann
     */
    private function replaceForm(FormInterface $originForm, array $overrideOptions)
    {
        if (($resolvedType = $originForm->getConfig()->getType()) instanceof ResolvedFormTypeInterface) {
            $type = get_class($resolvedType->getInnerType());
        } else {
            $type = get_class($originForm->getConfig()->getType());
        }

        $mergedOptions = $this->mergeOptions($originForm->getConfig()->getOptions(), $overrideOptions);

        $replacementBuilder = $this->builder->create($originForm->getName(), $type, $mergedOptions);

        $replacementForm = $replacementBuilder->getForm();
        $parent = $originForm->getParent();
        $parent->offsetSet($originForm->getName(), $replacementForm);
    }

    /**
     * checks if the property accessor string is valid for the given formBuilder
     * @param string $cssFormId
     * @param FormInterface $form
     * @param bool $firstRecursion
     * @throws \Exception
     * @return FormInterface
     * @author Anton Zoffmann
     */
    private function getFormForIdRecursive($cssFormId, FormInterface $form, $firstRecursion = true)
    {
        if ($cssFormId !== "") {

            $path = explode('_', $cssFormId);
            $currentPath = array_shift($path);

            # because the ids are fully qualified, in the first recursion we need to delete the root elements path
            if ($firstRecursion) {
                $currentPath = array_shift($path);
            }

            if ($form->has($currentPath)) {
                return $this->getFormForIdRecursive(implode('_', $path), $form->get($currentPath), false);
            } else {
                //todo build dynamic exception message to ease the locating of the accessor string
                throw new \Exception('Invalid Accessor');
            }
        } elseif ($cssFormId === "" and $firstRecursion === false) {
            # here we are in our last recursion, so we return the injected form because its the one we want
            return $form;

        }

        # here we got to an unexpected state, throw an exception
        throw new \Exception("Unexpected Recursion state, check your CSS-ID");
    }

    /**
     * @param array $originOptions
     * @param array $overrideOptions
     * @author Anton Zoffmann
     * @return array
     */
    private function mergeOptions(array $originOptions, array $overrideOptions)
    {
        # array recursive because the options array contains other arrays to be merged (attr,...)
        $merged = array_merge($originOptions, $overrideOptions, array('auto_initialize' => false));

        # auto_initialize needfully is false for non root elements
//        $merged = array_merge($merged, array('auto_initialize' => false));

        # string concatenation for css classes
        if(isset($originOptions['attr']['class']) and isset($overrideOptions['attr']['class'])){
            $this->mergeAttrClasses($originOptions['attr']['class'], $overrideOptions['attr']['class'], $merged['attr']['class']);
        }

        # string concatenation for label css classes
        if(isset($originOptions['label_attr']['class']) and isset($overrideOptions['label_attr']['class'])){
            $this->mergeAttrClasses($originOptions['label_attr']['class'], $overrideOptions['label_attr']['class'], $merged['label_attr']['class']);
        }

        return $merged;
    }

    /**
     * merge class strings
     * @param $originClasses
     * @param $overrideClasses
     * @param $mergedClasses
     * @author Anton Zoffmann
     */
    private function mergeAttrClasses($originClasses, $overrideClasses, &$mergedClasses)
    {
        $overrideClasses = explode(' ', $overrideClasses);
        $merged['attr']['class'] = $originClasses;

        foreach ($overrideClasses as $overrideClass) {
            if(strpos($originClasses, $overrideClass) !== false){
                $mergedClasses .= sprintf(' %s', $overrideClass);
            }
        }
    }

}