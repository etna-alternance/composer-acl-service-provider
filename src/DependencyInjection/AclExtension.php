<?php
/**
 * PHP version 7.1
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Acl\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * On définit cette classe pour personnaliser le processus de parsing de la configuration de notre bundle.
 *
 * Entre autres on ajoute la configuration dans les paramêtres du container Symfony
 */
class AclExtension extends Extension
{
    /**
     * Cette fonction est appelée par symfony et permet le chargement de la configuration du bundle
     * Ici on va chercher la config des services dans le dossier Resources/config.
     *
     * @param array            $configs   Les éventuels paramètres
     * @param ContainerBuilder $container Le container de la configuration
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        unset($configs);
        if (!$container->hasParameter('auth.app_name')) {
            throw new \Exception('AclBundle requires AuthBundle');
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
