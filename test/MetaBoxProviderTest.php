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
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetaBoxProvider::class)]
#[CoversClass(InvalidMetaBoxDefinitionException::class)]
class MetaBoxProviderTest extends TestCase
{
    use PHPMock;

    private MetaBoxProvider $provider;
    private MockObject $containerMock;
    private MockObject $configurationMock;

    #[Before]
    public function setUp(): void
    {
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $this->provider = new MetaBoxProvider();

        $this->containerMock->method('get')
            ->with(ConfigurationInterface::class)
            ->willReturn($this->configurationMock);
    }

    #[Test]
    public function shouldRegisterMetaBoxRegistry(): void
    {
        $containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $containerBuilderMock
            ->expects($this->once())
            ->method('addFactory')
            ->with(MetaBoxRegistry::class, $this->isCallable());

        $this->provider->configure($containerBuilderMock);
    }

    #[Test]
    public function shouldNotRegisterAnyMetaBoxesIfConfigurationIsEmpty(): void
    {
        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('metaBoxes', [])
            ->willReturn([]);
        $this->containerMock->expects($this->never())->method('construct');

        $this->provider->load($this->containerMock);
    }

    #[Test]
    public function shouldRegisterMetaBoxClassesFromConfiguration(): void
    {
        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('metaBoxes', [])
            ->willReturn([['metaBox' => 'class', 'screens' => ['screen']]]);
        $this->containerMock
            ->expects($this->once())
            ->method('construct')
            ->with('class')
            ->willReturn($this->getMockBuilder(MetaBox::class)->getMock());
        $this->getFunctionMock(__NAMESPACE__, 'add_action')
            ->expects($this->once())
            ->with($this->anything(), $this->anything());

        $this->provider->load($this->containerMock);
    }

    #[Test]
    public function shouldThrowExceptionIfInvalidMetaBoxDefinition(): void
    {
        $this->expectException(InvalidMetaBoxDefinitionException::class);

        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('metaBoxes', [])
            ->willReturn([['metaBox' => 'class']]);

        $this->provider->load($this->containerMock);
    }
}
