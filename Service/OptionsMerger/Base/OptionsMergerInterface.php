<?php

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Base;

use Symfony\Component\Form\FormInterface;

/**
 * @author Anton Zoffmann
 * Date: 16.09.16
 * Time: 13:10
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