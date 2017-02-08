<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base;

/**
 * Interface RuleInterface
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Structs\Rules\Base
 */
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