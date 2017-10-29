<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base;

use Symfony\Component\Form\FormTypeInterface;

/**
 * Interface ResponsibilityInterface
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base
 */
interface ResponsibilityInterface
{
    /**
     * checks if the OptionsMerger is responsible for the given form objects class
     * @param FormTypeInterface $formType
     * @return bool
     * @author Anton Zoffmann
     */
    public function isResponsibleForClass(FormTypeInterface $formType);

    /**
     * checks if the OptionsMerger is responsible for the given form objects interface
     * @param FormTypeInterface $formType
     * @return bool
     * @author Anton Zoffmann
     */
    public function isResponsibleForInterface(FormTypeInterface $formType);

}
