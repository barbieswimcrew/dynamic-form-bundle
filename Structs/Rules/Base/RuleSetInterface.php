<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 13.09.16
 * Time: 15:06
 */

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
}