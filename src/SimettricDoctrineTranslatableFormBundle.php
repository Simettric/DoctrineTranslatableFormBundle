<?php

namespace Simettric\DoctrineTranslatableFormBundle;

use Simettric\DoctrineTranslatableFormBundle\DependencyInjection\Compiler\TranslatableFormPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SimettricDoctrineTranslatableFormBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TranslatableFormPass());
    }
}
