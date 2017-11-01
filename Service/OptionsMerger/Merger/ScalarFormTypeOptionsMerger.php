<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Class ScalarFormTypeOptionsMerger
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger
 */
class ScalarFormTypeOptionsMerger implements OptionsMergerInterface
{

    /**
     * @var CssHelper
     */
    private $cssHelper;

    public function __construct(CssHelper $cssHelper)
    {
        $this->cssHelper = $cssHelper;
    }

    /**
     * @param array $originOptions
     * @param array $overrideOptions
     * @param bool $hidden
     * @return array
     */
    public function mergeOptions(array $originOptions, array $overrideOptions, $hidden)
    {

        # array recursive because the options array contains other arrays to be merged (attr,...)
        $merged = array_merge($originOptions, $overrideOptions, array('auto_initialize' => false));

        $originalAttr = (array_key_exists('attr', $originOptions)) ? $originOptions['attr'] : array();
        $overrideAttr = (array_key_exists('attr', $overrideOptions)) ? $overrideOptions['attr'] : array();

        $merged['attr']['class'] = $this->cssHelper->handleCssClasses($originalAttr, $overrideAttr, $hidden);

        $originalAttr = (array_key_exists('label_attr', $originOptions)) ? $originOptions['label_attr'] : array();
        $overrideAttr = (array_key_exists('label_attr', $overrideOptions)) ? $overrideOptions['label_attr'] : array();

        $merged['label_attr']['class'] = $this->cssHelper->handleCssClasses($originalAttr, $overrideAttr, $hidden);

        return $merged;
    }

    /**
     * @author Anton Zoffmann
     * @return string
     */
    public function getApplicableInterface()
    {
        return FormTypeInterface::class;
    }

    /**
     * @author Anton Zoffmann
     * @return array
     */
    public function getApplicableClasses()
    {
        return array();
    }

}