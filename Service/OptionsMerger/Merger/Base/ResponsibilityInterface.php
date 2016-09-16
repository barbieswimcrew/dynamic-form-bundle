<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 16.09.16
 * Time: 13:55
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base;


use Symfony\Component\Form\FormInterface;

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