<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules;

use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\AbstractBaseRuleSet;

/**
 * Class CheckboxRuleSet
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules
 */
class CheckboxRuleSet extends AbstractBaseRuleSet
{
    /**
     * CheckboxRuleSet constructor.
     * @param array $toggleFields
     */
    public function __construct(array $toggleFields)
    {
        parent::__construct(array(
            new Rule(1, $toggleFields, array()),
            new Rule(0, array(), $toggleFields)
        ));
    }
}