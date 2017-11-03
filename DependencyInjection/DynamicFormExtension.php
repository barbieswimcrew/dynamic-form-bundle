<?php

namespace Barbieswimcrew\Bundle\DynamicFormBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $container->setParameter('barbieswimcrew_dynamic_form.strict_mode', $config['strict_mode']);
        $container->setParameter('barbieswimcrew_dynamic_form.data_attr_id', $config['data_attr_id']);
        $container->setParameter('barbieswimcrew_dynamic_form.data_attr_targets_show', $config['data_attr_targets_show']);
        $container->setParameter('barbieswimcrew_dynamic_form.data_attr_targets_hide', $config['data_attr_targets_hide']);
        $container->setParameter('barbieswimcrew_dynamic_form.data_attr_class_hidden', $config['data_attr_class_hidden']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
