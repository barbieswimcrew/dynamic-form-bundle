<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger\NoOptionsMergerResponsibleException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\ResponsibilityInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Class OptionsMergerService
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger
 */
class OptionsMergerService
{

    /** @var array $optionsMergers */
    private $optionsMergers;
    /**
     * @var FormPropertyHelper
     */
    private $propertyHelper;
    /**
     * @var ResponsibilityInterface
     */
    private $responsibility;

    /**
     * OptionsMergerService constructor.
     * @param FormPropertyHelper $propertyHelper
     * @param ResponsibilityInterface $responsibility
     * @param OptionsMergerInterface[] ...$mergers
     */
    public function __construct(FormPropertyHelper $propertyHelper, ResponsibilityInterface $responsibility, OptionsMergerInterface ...$mergers)
    {
        $this->optionsMergers = $mergers;
        $this->propertyHelper = $propertyHelper;
        $this->responsibility = $responsibility;
    }

    /**
     * a responsible OptionsMerger found by a fitting class will always be returns immediately, found by interface will be cached and
     * returned when no other one can be found by a matching class. this is because we prioritize classes higher than interfaces.
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return OptionsMergerInterface
     * @throws NoOptionsMergerResponsibleException
     */
    public function getOptionsMerger(FormInterface $form)
    {
        $optionsMergerForInterface = null;

        /** @var FormTypeInterface $formType */
        $formType = $this->propertyHelper->getConfiguredFormTypeByForm($form);

        /** @var OptionsMergerInterface $optionsMerger */
        foreach ($this->optionsMergers as $optionsMerger) {
            if ($this->responsibility->isResponsibleForClass($optionsMerger, $formType)) {
                return $optionsMerger;
            }

            # if we find a merger by interface, cache it so all class possibilities get their chances to be found
            if ($this->responsibility->isResponsibleForInterface($optionsMerger, $formType)) {
                $optionsMergerForInterface = $optionsMerger;
            }
        }

        if (!$optionsMergerForInterface instanceof OptionsMergerInterface) {
            throw new NoOptionsMergerResponsibleException(get_class($form));
        }

        return $optionsMergerForInterface;

    }

}