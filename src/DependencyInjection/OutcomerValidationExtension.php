<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\DependencyInjection;

use Outcomer\ValidationBundle\Schema\SchemaValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Container extension for configuring validation bundle services
 */
final class OutcomerValidationExtension extends Extension
{
    /**
     * Loads bundle configuration and registers services
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration(configuration: $configuration, configs: $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load(resource: 'services.yaml');
        $container->setParameter(name: 'outcomer_validation.schemas_path', value: $config['schemas_path']);
        $container->setParameter(name: 'outcomer_validation.schema_domain', value: $config['schema_domain']);
        $container->setParameter(name: 'outcomer_validation.filters', value: $config['filters']);
        $container->setParameter(name: 'outcomer_validation.auto_cast_query', value: $config['auto_cast_query']);
        $container->setParameter(name: 'outcomer_validation.auto_cast_path', value: $config['auto_cast_path']);

        $schemaValidatorDefinition = $container->getDefinition(SchemaValidator::class);
        foreach ($config['schemas'] as $schemaDir) {
            $schemaValidatorDefinition->addMethodCall('registerSchemaDir', [$schemaDir['domain'], $schemaDir['path']]);
        }

        $enableExamples = ($_ENV['OUTCOMER_VALIDATION_ENABLE_EXAMPLES'] ?? 'false') === 'true';
        if ($enableExamples) {
            $loader->load(resource: 'services_examples.yaml');

            $schemaValidatorDefinition->addMethodCall('registerSchemaDir', [
                'https://outcomer.dev/__examples__',
                __DIR__.'/../Examples/Schemas',
            ]);
            $schemaValidatorDefinition->addMethodCall('registerSchemaDir', [
                'https://outcomer.dev/__examples_common__',
                __DIR__.'/../Examples/Schemas/common',
            ]);
        }

        $filterReferences = [];
        foreach ($config['filters'] as $filterName => $filterConfig) {
            $filterClass                   = is_array($filterConfig) ? $filterConfig['class'] : $filterConfig;
            $filterReferences[$filterName] = new Reference($filterClass);
        }

        if ($enableExamples) {
            $filterReferences['valid_promo_code'] ??= new Reference('Outcomer\ValidationBundle\Examples\Filter\PromoCodeFilter');
        }

        $filterLocatorDefinition = $container->getDefinition('outcomer_validation.filter_locator');
        $filterLocatorDefinition->setArguments([$filterReferences]);

        if (class_exists('Nelmio\ApiDocBundle\NelmioApiDocBundle')) {
            $container
                ->register('outcomer_validation.api_doc_describer', 'Outcomer\ValidationBundle\ApiDoc\MapRequestArgumentDescriber')
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setArgument('$schemasPath', '%outcomer_validation.schemas_path%')
                ->addTag('nelmio_api_doc.route_argument_describer');
        }
    }
}
