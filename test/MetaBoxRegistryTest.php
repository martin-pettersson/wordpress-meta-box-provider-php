<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetaBoxRegistry::class)]
final class MetaBoxRegistryTest extends TestCase
{
    use PHPMock;

    private MetaBoxRegistry $registry;

    private MockObject $metaBoxMock;

    #[Before]
    public function setUp(): void
    {
        $this->registry = new MetaBoxRegistry();
        $this->metaBoxMock = $this->getMockBuilder(MetaBox::class)->getMock();
    }

    #[Test]
    public function shouldRegisterMetaBoxAtAppropriateHook(): void
    {
        $this->getFunctionMock(__NAMESPACE__, 'add_action')
            ->expects($this->once())
            ->with('add_meta_boxes', $this->isCallable());
        $this->getFunctionMock(__NAMESPACE__, 'add_meta_box')
            ->expects($this->never());
        $this->metaBoxMock->expects($this->never())->method($this->anything());

        $this->registry->register($this->metaBoxMock, ['screen']);
    }

    #[Test]
    public function shouldRegisterMetaBoxes(): void
    {
        $this->getFunctionMock(__NAMESPACE__, 'add_action')
            ->expects($this->once())
            ->with('add_meta_boxes', $this->isCallable())
            ->willReturnCallback(static fn($hook, $callback) => $callback());
        $this->getFunctionMock(__NAMESPACE__, 'add_meta_box')
            ->expects($this->once())
            ->with(
                $this->metaBoxMock->id(),
                $this->metaBoxMock->title(),
                $this->isCallable(),
                ['screen'],
                $this->metaBoxMock->context(),
                $this->metaBoxMock->priority()
            );

        $this->registry->register($this->metaBoxMock, ['screen']);
    }
}
