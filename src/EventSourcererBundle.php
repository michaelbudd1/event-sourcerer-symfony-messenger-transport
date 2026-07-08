<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\SymfonyClient;

use PearTreeWeb\EventSourcerer\Common\Model\IsEvent;
use PearTreeWeb\EventSourcerer\SymfonyClient\DependencyInjection\Compiler\RegisterEventsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EventSourcererBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterEventsPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $builder->registerForAutoconfiguration(IsEvent::class)
            ->addTag('eventsourcerer.event');
    }
}
