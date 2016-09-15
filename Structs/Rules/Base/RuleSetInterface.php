<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;

interface RuleSetInterface
{

    /**
     * @param $value
     * @author Anton Zoffmann
     * @return RuleInterface
     * @throws NoRuleDefinedException
     */
    public function getRule($value);

    /**
     * returns an array with all contained rules
     * @author Anton Zoffmann
     * @return array
     */
    public function getRules();
}