<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Form\Subscriber;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReconfigurator;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReplacement\FormReplacementService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\CssHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\RepeatedTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\ScalarFormTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\OptionsMergerService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\ResponsibilityChecker;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class ReconfigurationSubscriber
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Form\Subscriber
 */
class ReconfigurationSubscriber implements EventSubscriberInterface
{

    /** @var FormReconfigurator */
    private $reconfigurator;

    /**
     * ReconfigurationSubscriber constructor.
     * @param RuleSetInterface $ruleSet
     * @param FormBuilderInterface $builder
     * @param string $hiddenClass
     */
    public function __construct(RuleSetInterface $ruleSet, FormBuilderInterface $builder, $hiddenClass)
    {

        $cssHelper = new CssHelper($hiddenClass);

        $scalarMerger = new ScalarFormTypeOptionsMerger($cssHelper);

        $repeatedMerger = new RepeatedTypeOptionsMerger($scalarMerger, $cssHelper);

        $formPropertyHelper = new FormPropertyHelper();

        $optionsMerger = new OptionsMergerService($formPropertyHelper, new ResponsibilityChecker(), $scalarMerger, $repeatedMerger);

        $formReplacer = new FormReplacementService($builder, $optionsMerger, $formPropertyHelper);

        $this->reconfigurator = new FormReconfigurator($ruleSet, $builder, new FormAccessResolver(), $formPropertyHelper, $formReplacer);
    }

    /**
     * @author Anton Zoffmann
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => "onPreSetData",
            FormEvents::POST_SET_DATA => "onPostSetData",
            FormEvents::PRE_SUBMIT => "onPreSubmit",
        );
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var FormInterface $toggledForm */
        $subscribedForm = $event->getForm();
        $event->stopPropagation();

        $this->reconfigurator->setOriginalOptionsOnShowFields($subscribedForm);
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function onPostSetData(FormEvent $event)
    {
        /** @var FormInterface $subscribedForm */
        $subscribedForm = $event->getForm();
        $setData = $event->getData();
        $event->stopPropagation();

        $this->reconfigurator->reconfigureTargetFormsByData($subscribedForm, $setData, false);
    }

    /**
     * @param FormEvent $event
     * @author Anton Zoffmann
     */
    public function onPreSubmit(FormEvent $event)
    {
        /** @var FormInterface $subscribedForm */
        $subscribedForm = $event->getForm();
        $submittedData = $event->getData();

        $this->reconfigurator->reconfigureTargetFormsByData($subscribedForm, $submittedData, true);
    }

}
