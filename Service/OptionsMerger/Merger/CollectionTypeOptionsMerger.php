<?php
/**
 * @author Anton Zoffmann
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 16.09.16
 * Time: 13:58
 */

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger;



use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base\AbstractOptionsMerger;
use Symfony\Component\Form\FormInterface;

class CollectionTypeOptionsMerger extends AbstractOptionsMerger
{
    /**
     * @inheritdoc
     * @author Anton Zoffmann
     */
    public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden)
    {
        // TODO: Implement getMergedOptions() method.
    }

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     */
    protected function getApplicableInterface()
    {
        // TODO: Implement getApplicableInterface() method.
    }

    /**
     * @inheritdoc
     * @author Anton Zoffmann
     */
    protected function getApplicableClasses()
    {
        // TODO: Implement getApplicableClasses() method.
    }

}