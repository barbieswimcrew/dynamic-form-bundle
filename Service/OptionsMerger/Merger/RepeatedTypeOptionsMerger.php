<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Class RepeatedTypeOptionsMerger
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger
 */
class RepeatedTypeOptionsMerger implements OptionsMergerInterface
{

    /**
     * @var OptionsMergerInterface
     */
    private $scalarMerger;
    /**
     * @var CssHelper
     */
    private $cssHelper;

    /**
     * RepeatedTypeOptionsMerger constructor.
     * @param OptionsMergerInterface $scalarMerger
     * @param CssHelper $cssHelper
     */
    public function __construct(OptionsMergerInterface $scalarMerger, CssHelper $cssHelper)
    {
        $this->scalarMerger = $scalarMerger;
        $this->cssHelper = $cssHelper;
    }

    /**
     * @param array $originOptions
     * @param array $overrideOptions
     * @param bool $hidden
     * @return array
     * @author Martin Schindler
     */
    public function mergeOptions(array $originOptions, array $overrideOptions, $hidden)
    {

        # do a merge of the standard scalar options
        $merged = $this->scalarMerger->mergeOptions($originOptions, $overrideOptions, $hidden);

        $attrClasses = array();

        $labelAttrClasses = array();

        if (isset($originOptions['options']['attr']['class'])) {
            $attrClasses = $this->cssHelper->explodeClasses($originOptions['options']['attr']['class']);
        }

        if (isset($originOptions['options']['label_attr']['class'])) {
            $labelAttrClasses = $this->cssHelper->explodeClasses($originOptions['options']['label_attr']['class']);
        }

        $attrClasses = $this->cssHelper->handleHiddenClass($attrClasses, $hidden);

        $labelAttrClasses = $this->cssHelper->handleHiddenClass($labelAttrClasses, $hidden);

        $merged['options']['attr']['class'] = $this->cssHelper->implodeClasses($attrClasses);

        $merged['options']['label_attr']['class'] = $this->cssHelper->implodeClasses($labelAttrClasses);

        return $merged;
    }

    /**
     * @author Martin Schindler
     * @return array
     */
    public function getApplicableClasses()
    {
        return array(
            RepeatedType::class,
        );
    }

    /**
     * @author Martin Schindler
     * @return string
     */
    public function getApplicableInterface()
    {
        return "";
    }


}