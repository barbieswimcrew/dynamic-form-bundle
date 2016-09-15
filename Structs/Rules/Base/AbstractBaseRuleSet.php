<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\DuplicateRuleValueException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\Rules\NoRuleDefinedException;

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
     * @throws DuplicateRuleValueException
     */
    private function addRule(RuleInterface $rule)
    {

        if (array_key_exists($rule->getValue(), $this->rules)) {
            throw new DuplicateRuleValueException($rule->getValue());
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
        if(!array_key_exists($value, $this->rules)){
            throw new NoRuleDefinedException($value);
        }

        return $this->rules[$value];

    }

    public function getRules()
    {
        return $this->rules;
    }

}
