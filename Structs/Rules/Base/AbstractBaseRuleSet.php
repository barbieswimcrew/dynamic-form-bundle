<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\MoreThanOneRuleForValueException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\Rules\NoRuleDefinedException;

/**
 * Class AbstractBaseRuleSet
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base
 */
abstract class AbstractBaseRuleSet implements RuleSetInterface
{
    /**
     * @var array
     */
    private $rules;

    /**
     * BasicRuleset constructor.
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = array();
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * @param RuleInterface $rule
     * @author Anton Zoffmann
     * @throws MoreThanOneRuleForValueException
     */
    private function addRule(RuleInterface $rule)
    {

        if (array_key_exists($rule->getValue(), $this->rules)) {
            throw new MoreThanOneRuleForValueException($rule->getValue());
        }

        $this->rules[$rule->getValue()] = $rule;
    }

    /**
     * @param $value
     * @author Anton Zoffmann
     * @return RuleInterface|bool
     * @throws NoRuleDefinedException
     */
    public function getRule($value)
    {
        if (!array_key_exists($value, $this->rules)) {
            throw new NoRuleDefinedException($value);
        }

        return $this->rules[$value];

    }

    /**
     * returns an array with all contained rules
     * @author Anton Zoffmann
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

}
