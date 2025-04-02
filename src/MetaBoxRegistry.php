<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

/**
 * Represents a WordPress meta box registry.
 */
final class MetaBoxRegistry
{
    /**
     * Register meta box for given screens.
     *
     * @param \N7e\WordPress\MetaBox $metaBox Arbitrary meta box.
     * @param array $screens Associated screens.
     */
    public function register(MetaBox $metaBox, array $screens): void
    {
        add_action('add_meta_boxes', fn() => $this->registerMetaBox($metaBox, $screens));
    }

    /**
     * Register meta box for given screens.
     *
     * @param \N7e\WordPress\MetaBox $metaBox Arbitrary meta box.
     * @param array $screens Associated screens.
     */
    private function registerMetaBox(MetaBox $metaBox, array $screens): void
    {
        add_meta_box(
            $metaBox->id(),
            $metaBox->title(),
            static fn($post) => print $metaBox->render($post),
            $screens,
            $metaBox->context(),
            $metaBox->priority()
        );
    }
}
