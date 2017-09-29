<?php

namespace EMC\FileinputBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EMCFileinputExtension extends Extension {

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

	    $env = $container->getParameter("kernel.environment");
	    if ( $env === 'dev') {
		    $loader->load('services_dev.yml');
	    }

        $container->setParameter('emc_fileinput.file_class', $config['file_class']);
        
        if (isset($config['providers'])) {
            foreach ($config['providers'] as $name => $config) {
                switch ($name) {
                    case 'vimeo':
                        $loader->load('vimeo.yml');
                        foreach($config as $key => $value) {
                            $container->setParameter('emc_fileinput.providers.vimeo.' . $key, $value);
                        }
                        break;
                    case 'calameo':
                        $loader->load('calameo.yml');
                        foreach($config as $key => $value) {
                            $container->setParameter('emc_fileinput.providers.calameo.' . $key, $value);
                        }
                        break;
                }
            }
        }
    }

}
