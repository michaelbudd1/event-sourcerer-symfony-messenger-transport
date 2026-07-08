<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\SymfonyClient\DependencyInjection\Compiler;

use PearTreeWeb\EventSourcerer\Common\Model\IsEvent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

final class RegisterEventsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        $srcDir = $projectDir . '/src';

        if (!is_dir($srcDir)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($srcDir)->name('*.php');

        foreach ($finder as $file) {
            $className = $this->extractClassName($file->getRealPath());
            if ($className && is_subclass_of($className, IsEvent::class)) {
                if (!$container->hasDefinition($className)) {
                    $container->register($className, $className)
                        ->setAutoconfigured(true)
                        ->setAutowired(false)
                        ->setLazy(true)
                        ->addTag('eventsourcerer.event');
                }
            }
        }
    }

    private function extractClassName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if (preg_match('/namespace\s+(.+?);/s', $content, $namespaceMatches) &&
            preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return $namespaceMatches[1] . '\\' . $classMatches[1];
        }

        return null;
    }
}
