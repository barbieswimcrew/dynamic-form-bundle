<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger\NoOptionsMergerResponsibleException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\FormPropertyHelper\FormPropertyHelper;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base\AbstractOptionsMerger;
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
     * OptionsMergerService constructor.
     * @param FormPropertyHelper $propertyHelper
     * @param AbstractOptionsMerger[] $mergers
     */
    public function __construct(FormPropertyHelper $propertyHelper, AbstractOptionsMerger ...$mergers)
    {

        $this->optionsMergers = $mergers;

        $this->propertyHelper = $propertyHelper;
    }

    /**
     * a responsible OptionsMerger found by a fitting class will always be returns immediately, found by interface will be cached and
     * returned when no other one can be found by a matching class. this is because we prioritize classes higher than interfaces.
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return AbstractOptionsMerger
     * @throws NoOptionsMergerResponsibleException
     */
    public function getOptionsMerger(FormInterface $form)
    {
        $optionsMergerForInterface = null;

        /** @var FormTypeInterface $formType */
        $formType = $this->propertyHelper->getConfiguredFormTypeByForm($form);

        /** @var AbstractOptionsMerger $optionsMerger */
        foreach ($this->optionsMergers as $optionsMerger) {
            if ($optionsMerger->isResponsibleForClass($formType)) {
                return $optionsMerger;
            }

            # if we find a merger by interface, cache it so all class possibilities get their chances to be found
            if ($optionsMerger->isResponsibleForInterface($formType)) {
                $optionsMergerForInterface = $optionsMerger;
            }
        }

        if (!$optionsMergerForInterface instanceof OptionsMergerInterface) {
            throw new NoOptionsMergerResponsibleException(get_class($form));
        }

        return $optionsMergerForInterface;

    }

}