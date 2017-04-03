<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 03.04.2017
 * Time: 22:53
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers;


use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReplacement\FormReplacementService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base\AbstractReconfigurationHandler;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base\ReconfigurationHandlerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;

class ChoiceTypeMultipleReconfigurationHandler extends AbstractReconfigurationHandler implements ReconfigurationHandlerInterface
{

    /**
     * @var FormPropertyHelper
     */
    private $formPropertyHelper;

    public function __construct(RuleSetInterface $ruleset, FormAccessResolver $formAccessResolver, FormReplacementService $formReplacementService, FormPropertyHelper $formPropertyHelper)
    {
        parent::__construct($ruleset, $formAccessResolver, $formReplacementService);
        $this->formPropertyHelper = $formPropertyHelper;
    }

    /**
     * @return bool
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     */
    public function isResponsible(FormInterface $toggleForm)
    {
        $this->setToggleForm($toggleForm);
        # here we need to manually define the value for which the show OR hide algorithm should be executed
        $configuredFormType = $this->formPropertyHelper->getConfiguredFormTypeByForm($toggleForm);

        if (new ChoiceType() instanceof $configuredFormType and $toggleForm->getConfig()->getOption('multiple') === true) {
            return true;
        }

        return false;
    }

    public function handle($data, $blockFurtherReconfigurations) {
        //array handling (special case for ChoiceType MULTIPLE - here we have checkboxes but no CheckboxTypes)
        //submitted data means selected checkboxes (equal to show fields)
        //not sumbitted data is equal to hide fields
        //PROBLEM: EMPTY DATA IS PER DEFAULT NULL

        /** @var FormInterface $parentForm */
        $parentForm = $this->toggleForm->getParent();
        # get values of each chekboxes configuration

        foreach ($this->collectConfiguredValuesForMultipleChoiceType($this->toggleForm) as $configuredValue) {
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