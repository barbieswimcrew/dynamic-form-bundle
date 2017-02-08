<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger;

use Barbieswimcrew\Bundle\DynamicFormBundle\Exceptions\OptionsMerger\NoOptionsMergerResponsibleException;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\Base\AbstractOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\RepeatedTypeOptionsMerger;
use Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger\Merger\ScalarFormTypeOptionsMerger;
use Symfony\Component\Form\FormInterface;

/**
 * Class OptionsMergerService
 * @author Anton Zoffmann
 * @package Barbieswimcrew\Bundle\DynamicFormBundle\Service\OptionsMerger
 */
class OptionsMergerService implements OptionsMergerInterface
{

    /** @var array $optionsMergers */
    private $optionsMergers;

    /**
     * OptionsMergerService constructor.
     */
    public function __construct()
    {
        $this->setDefaultOptionsMergers();
        # todo make OptionsMergers open for extension, eventually injectable customized OptionsMergers
        # todo make hidden css class configurable
    }

    /**
     * @param FormInterface $form
     * @param array $overrideOptions
     * @param bool $hidden
     * @author Anton Zoffmann
     * @return array
     * @throws NoOptionsMergerResponsibleException
     */
    public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden)
    {
        /** @var OptionsMergerInterface $optionsMerger */
        $optionsMerger = $this->getResponsibleOptionsMerger($form);

        return $optionsMerger->getMergedOptions($form, $overrideOptions, $hidden);
    }

    /**
     * @author Anton Zoffmann
     */
    private function setDefaultOptionsMergers()
    {
        $defaultOptionMergers = array(
            new ScalarFormTypeOptionsMerger(),
            new RepeatedTypeOptionsMerger(),
//            new CollectionTypeOptionsMerger(), //todo implement
        );

        foreach ($defaultOptionMergers as $defaultOptionMerger) {
            $this->addOptionsMerger($defaultOptionMerger);
        }
    }

    /**
     * @param AbstractOptionsMerger $optionsMerger
     * @author Anton Zoffmann
     */
    private function addOptionsMerger(AbstractOptionsMerger $optionsMerger)
    {
        # here we could decide if we want to allow multiple optionsMergers for the same class/interface it is responsible for
        $this->optionsMergers[] = $optionsMerger;
    }

    /**
     * a responsible OptionsMerger found by a fitting class will always be returns immediately, found by interface will be cached and
     * returned when no other one can be found by a matching class. this is because we prioritize classes higher than interfaces.
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return AbstractOptionsMerger
     * @throws NoOptionsMergerResponsibleException
     */
    private function getResponsibleOptionsMerger(FormInterface $form)
    {
        $optionsMergerForInterface = null;

        /** @var AbstractOptionsMerger $optionsMerger */
        foreach ($this->optionsMergers as $optionsMerger) {
            if ($optionsMerger->isResponsibleForFormTypeClass($form)) {
                return $optionsMerger;
            }

            # if we find a merger by interface, cache it so all class possibilities get their chances to be found
            if ($optionsMerger->isResponsibleForFormTypeInterface($form)) {
                $optionsMergerForInterface = $optionsMerger;
            }
        }

        if (!$optionsMergerForInterface instanceof OptionsMergerInterface) {
            throw new NoOptionsMergerResponsibleException(get_class($form));
        }

        return $optionsMergerForInterface;

    }

}