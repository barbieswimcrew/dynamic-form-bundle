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

        if (array_key_exists('options', $originOptions)) {

            $merged['options']['attr']['class'] = $this->cssHelper->handleHiddenClass($merged['attr']['class'], $hidden);
            $merged['options']['label_attr']['class'] = $this->cssHelper->handleHiddenClass($merged['label_attr']['class'], $hidden);
        }

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