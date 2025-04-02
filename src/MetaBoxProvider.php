<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use N7e\Configuration\ConfigurationInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\ServiceProviderInterface;
use Override;

/**
 * Provides WordPress meta boxes.
 */
final class MetaBoxProvider implements ServiceProviderInterface
{
    /**
     * Registered meta boxes.
     *
     * @var \N7e\WordPress\MetaBoxRegistry
     */
    private readonly MetaBoxRegistry $metaBoxes;

    /**
     * Dependency injection container.
     *
     * @var \N7e\DependencyInjection\ContainerInterface
     */
    private readonly ContainerInterface $container;

    /**
     * Create a new service provider instance.
     */
    public function __construct()
    {
        $this->metaBoxes = new MetaBoxRegistry();
    }

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $containerBuilder->addFactory(MetaBoxRegistry::class, fn() => $this->metaBoxes)->singleton();
    }

    #[Override]
    public function load(ContainerInterface $container): void
    {
        $this->container = $container;

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $container->get(ConfigurationInterface::class);

        foreach ($configuration->get('metaBoxes', []) as $metaBox) {
            $this->register($metaBox);
        }
    }

    /**
     * Register given meta box definition.
     *
     * @param array $metaBoxDefinition Arbitrary meta box definition.
     * @throws \N7e\WordPress\InvalidMetaBoxDefinitionException If any meta box definition is invalid.
     * @throws \Psr\Container\ContainerExceptionInterface If unable to construct meta box.
     */
    private function register(array $metaBoxDefinition): void
    {
        if (
            ! array_key_exists('metaBox', $metaBoxDefinition) ||
            ! array_key_exists('screens', $metaBoxDefinition)
        ) {
            throw new InvalidMetaBoxDefinitionException();
        }

        $this->metaBoxes->register(
            $this->container->construct($metaBoxDefinition['metaBox']),
            $metaBoxDefinition['screens']
        );
    }
}
