<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 16.09.16
 * Time: 13:50
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Exceptions\OptionsMerger\OptionsMergerResponsibilityException;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Base\OptionsMergerInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base\AbstractOptionsMerger;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base\ResponsibilityInterface;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\RepeatedTypeOptionsMerger;
use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\ScalarFormTypeOptionsMerger;
use Symfony\Component\Form\FormInterface;

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
    }

    /**
     * @param FormInterface $form
     * @param array $overrideOptions
     * @param bool $hidden
     * @author Anton Zoffmann
     * @return array
     * @throws OptionsMergerResponsibilityException
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
     * @param FormInterface $form
     * @author Anton Zoffmann
     * @return OptionsMergerInterface
     * @throws OptionsMergerResponsibilityException
     */
    private function getResponsibleOptionsMerger(FormInterface $form)
    {
        /** @var ResponsibilityInterface $optionsMerger */
        foreach ($this->optionsMergers as $optionsMerger) {
            if ($optionsMerger->isResponsibleForFormTypeClass($form)) {
                return $optionsMerger;
            }
        }

        /** @var ResponsibilityInterface $optionsMerger */
        foreach ($this->optionsMergers as $optionsMerger) {
            if ($optionsMerger->isResponsibleForFormTypeInterface($form)) {
                return $optionsMerger;
            }
        }

        throw new OptionsMergerResponsibilityException(get_class($form));
    }

}