<?php

namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('barbieswimcrew_symfony_form_rule_set');

        $rootNode
            ->children()
                ->booleanNode('strict_mode')
                    ->defaultFalse()
                ->end()
                ->scalarNode('data_attr_id')
                    ->defaultValue('data-sfhandler-id')
                ->end()
                ->scalarNode('data_attr_targets_show')
                    ->defaultValue('data-sfhandler-targets-show')
                ->end()
                ->scalarNode('data_attr_targets_hide')
                    ->defaultValue('data-sfhandler-targets-hide')
                ->end()
                ->scalarNode('data_attr_is_required')
                    ->defaultValue('data-sfhandler-required')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
