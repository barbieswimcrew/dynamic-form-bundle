<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormInterface;

/**
 * Class RepeatedTypeOptionsMerger
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger
 */
class RepeatedTypeOptionsMerger extends ScalarFormTypeOptionsMerger
{
    /**
     * @param FormInterface $form
     * @param array $overrideOptions
     * @param bool $hidden
     * @author Martin Schindler
     * @return array
     */
    public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden)
    {
        /** @var array $originOptions */
        $originOptions = $form->getConfig()->getOptions();

        # do a merge of the standard scalar options
        $merged = parent::getMergedOptions($form, $overrideOptions, $hidden);

        if (isset($originOptions['options'])) {

            $merged['options']['attr']['class'] = $this->handleHiddenClass($merged['attr'], $hidden);
            $merged['options']['label_attr']['class'] = $this->handleHiddenClass($merged['label_attr'], $hidden);
        }

        return $merged;
    }

    /**
     * @author Martin Schindler
     * @return array
     */
    protected function getApplicableClasses()
    {
        return array(
            RepeatedType::class,
        );
    }

    /**
     * @author Martin Schindler
     * @return string
     */
    protected function getApplicableInterface()
    {
        return "";
    }


}