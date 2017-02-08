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

}