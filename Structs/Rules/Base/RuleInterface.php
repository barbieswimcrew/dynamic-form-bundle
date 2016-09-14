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

    /**
     * returns ids of fields to be hidden if rule takes effect
     * @author Anton Zoffmann
     * @return array
     */
    public function getHideFields();

    /**
     * returns ids of fields to be shown if rule takes effect
     * @author Anton Zoffmann
     * @return mixed
     */
    public function getShowFields();
}