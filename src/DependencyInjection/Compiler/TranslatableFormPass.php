<?php

namespace Simettric\DoctrineTranslatableFormBundle\DependencyInjection\Compiler;

use Simettric\DoctrineTranslatableFormBundle\Form\AbstractTranslatableType;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TranslatableFormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('simettric_doctrine_translatable_form');
        $services = $container->findTaggedServiceIds('form.type');

        foreach ($services as $service => $tags) {
            $definition = $container->getDefinition($service);
            $r = $container->getReflectionClass($definition->getClass());

            if ($r->isSubclassOf(AbstractTranslatableType::class)) {
                $definition
                    ->setArguments([new Reference('sim_trans_form.mapper')])
                    ->addMethodCall('setLocales', [$config[0]['locales']])
                    ->addMethodCall('setRequiredLocale', [$config[0]['required_locale']])
                ;
            }
        }
    }
}
