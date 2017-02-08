<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DynamicFormExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('barbieswimcrew_symfony_form_rule_set.strict_mode', $config['strict_mode']);
        $container->setParameter('barbieswimcrew_symfony_form_rule_set.data_attr_id', $config['data_attr_id']);
        $container->setParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_show', $config['data_attr_targets_show']);
        $container->setParameter('barbieswimcrew_symfony_form_rule_set.data_attr_targets_hide', $config['data_attr_targets_hide']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
