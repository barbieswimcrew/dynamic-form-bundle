<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\ResponsibilityInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Class ResponsibilityChecker
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger
 */
class ResponsibilityChecker implements ResponsibilityInterface
{
    /**
     * @inheritdoc
     * @param FormTypeInterface $formType
     * @author Anton Zoffmann
     */
    public function isResponsibleForClass(OptionsMergerInterface $merger, FormTypeInterface $formType)
    {

        $applicableClasses = (is_array($merger->getApplicableClasses())) ? $merger->getApplicableClasses() : array();

        foreach ($applicableClasses as $applicableClass) {

            if (!class_exists($applicableClass)) {
                throw new \Exception(sprintf('Class "%s" not found', $applicableClass), null);
            }

            if ($formType instanceof $applicableClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     */
    public function isResponsibleForInterface(OptionsMergerInterface $merger, FormTypeInterface $formType)
    {

        $applicableInterface = (string)$merger->getApplicableInterface();

        if ($formType instanceof $applicableInterface) {
            return true;
        }

        return false;
    }
}