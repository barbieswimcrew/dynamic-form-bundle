<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 03.04.17
 * Time: 18:56
 */

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\ReconfigurationHandlers\Base;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\NoRuleDefinedException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Form\Extension\RelatedFormTypeExtension;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormAccessResolver\FormAccessResolver;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormReconfigurator\FormReplacement\FormReplacementService;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\RuleSetInterface;
use Symfony\Component\Form\FormInterface;

class AbstractReconfigurationHandler
{

    /**
     * @var RuleSetInterface
     */
    private $ruleSet;
    /**
     * @var FormAccessResolver
     */
    private $formAccessResolver;
    /**
     * @var FormReplacementService
     */
    private $formReplacer;

    /** @var FormInterface */
    protected $toggleForm;

    public function __construct(RuleSetInterface $ruleset, FormAccessResolver $formAccessResolver, FormReplacementService $replacementService)
    {

        $this->ruleSet = $ruleset;
        $this->formAccessResolver = $formAccessResolver;
        $this->formReplacer = $replacementService;
    }

    /**
     * @param FormInterface $form
     * @return self
     */
    public function setToggleForm(FormInterface $form)
    {
        $this->toggleForm = $form;
        return $this;
    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @param $blockFurtherReconfigurations
     * @author Martin Schindler
     * @return void
     */
    protected function disableFields($data, FormInterface $parentForm, $blockFurtherReconfigurations)
    {
        try {
            /**
             * THIS IS THE DECISION which rule should be effected
             * @var RuleInterface $rule
             */
            $rule = $this->ruleSet->getRule($data);

        } catch (NoRuleDefinedException $exception) {
            # nothing to to if no rule is defined
            return;
        }

        /** @var array $hideFields */
        $hideFieldIds = $rule->getHideFields();

        foreach ($hideFieldIds as $hideFieldId) {

            $hideField = $this->formAccessResolver->getFormById($hideFieldId, $parentForm);

            $this->formReplacer->replaceForm(
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

    }

    /**
     * @param $data
     * @param FormInterface $parentForm
     * @author Anton Zoffmann
     * @return void
     * @todo this method does an reset, enabling looks different to this code... rename the method and create a new one
     */
    protected function enableFields($data, FormInterface $parentForm)
    {
        try {
            /**
             * THIS IS THE DECISION which rule should be effected
             * @var RuleInterface $rule
             */
            $rule = $this->ruleSet->getRule($data);

        } catch (NoRuleDefinedException $exception) {
            # nothing to to if no rule is defined
            return;
        }

        /** @var array $showFieldIds */
        $showFieldIds = $rule->getShowFields();
        # reconfiguration restriction shall only be applied if the parend form is disabled. when it is enabled,
        # there is no reason to restrict 2nd level toggles and their targets
        $blockFurtherReconfigurations = false;

        foreach ($showFieldIds as $showFieldId) {
            $showField = $this->formAccessResolver->getFormById($showFieldId, $parentForm);
            $this->formReplacer->replaceForm(
                $showField,
                $showField->getConfig()->getOption(RelatedFormTypeExtension::OPTION_NAME_ORIGINAL_OPTIONS),
                false,
                $blockFurtherReconfigurations
            );
        }

    }

}