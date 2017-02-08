<?php


namespace Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules;


use Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base\AbstractBaseRuleSet;

class CheckboxRuleSet extends AbstractBaseRuleSet
{
    public function __construct(array $toggleFields)
    {
        parent::__construct(array(
            new Rule(1, $toggleFields, array()),
            new Rule(0, array(), $toggleFields)
        ));
    }
}