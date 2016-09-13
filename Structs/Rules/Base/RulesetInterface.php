<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 13.09.16
 * Time: 15:06
 */

namespace barbieswimcrew\DynamicFormsBundle\Structs\Rules\Base;


interface RulesetInterface
{

    /**
     * @param $value
     * @author Anton Zoffmann
     * @return RuleInterface
     */
    public function getRule($value);
}