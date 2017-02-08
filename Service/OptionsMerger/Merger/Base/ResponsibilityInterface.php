<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base;

use Symfony\Component\Form\FormInterface;

/**
 * Interface ResponsibilityInterface
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base
 */
interface ResponsibilityInterface
{
    /**
     * checks if the OptionsMerger is responsible for the given form objects class
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return bool
     */
    public function isResponsibleForFormTypeClass(FormInterface $form);

    /**
     * checks if the OptionsMerger is responsible for the given form objects interface
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return bool
     */
    public function isResponsibleForFormTypeInterface(FormInterface $form);
}