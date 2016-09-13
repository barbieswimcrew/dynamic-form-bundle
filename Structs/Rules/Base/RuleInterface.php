<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 13.09.16
 * Time: 15:03
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Structs\Rules\Base;


interface RuleInterface
{
    /**
     * @author Anton Zoffmann
     * @return string
     */
    public function getValue();
}