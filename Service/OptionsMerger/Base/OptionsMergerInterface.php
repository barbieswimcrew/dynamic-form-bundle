<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base;

use Symfony\Component\Form\FormInterface;

/**
 * Interface OptionsMergerInterface
 * @author Martin Schindler
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base
 */
interface OptionsMergerInterface
{
    /**
     * @param FormInterface $form
     * @param array $overrideOptions
     * @param bool $hidden
     * @author Anton Zoffmann
     * @return array
     */
    public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden);

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