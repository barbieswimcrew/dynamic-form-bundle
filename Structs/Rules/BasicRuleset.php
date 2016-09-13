<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 13.09.16
 * Time: 15:05
 */

namespace barbieswimcrew\DynamicFormsBundle\Structs\Rules;


use barbieswimcrew\DynamicFormsBundle\Exceptions\Rules\DuplicateRuleValueException;
use barbieswimcrew\DynamicFormsBundle\Structs\Rules\Base\RuleInterface;
use barbieswimcrew\DynamicFormsBundle\Structs\Rules\Base\RulesetInterface;

class BasicRuleset implements RulesetInterface
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

        if(array_key_exists($rule->getValue(), $this->rules)){
            throw new DuplicateRuleValueException($rule->getValue());
        }

        $this->rules[$rule->getValue()] = $rule;
    }

    /**
     * @param $value
     * @author Anton Zoffmann
     * @return RuleInterface
     */
    public function getRule($value)
    {
        return $this->rules[$value];
    }


}