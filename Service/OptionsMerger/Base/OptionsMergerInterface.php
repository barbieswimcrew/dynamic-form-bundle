<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base;


/**
 * Interface OptionsMergerInterface
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base
 */
interface OptionsMergerInterface
{
    /**
     * @param array $originalOptions
     * @param array $overrideOptions
     * @param bool $hidden
     * @return array
     * @author Anton Zoffmann
     */
    public function mergeOptions(array $originalOptions, array $overrideOptions, $hidden);

    /**
     * returns a fully qualified namespace of the interface
     * @author Anton Zoffmann
     * @return string
     */
    public function getApplicableInterface();

    /**
     * returns an array of strings from the fully qualified namespaces of applicable classes
     * @author Anton Zoffmann
     * @return array
     */
    public function getApplicableClasses();

}