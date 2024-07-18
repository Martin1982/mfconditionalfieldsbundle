<?php

namespace Martin1982\MfConditionalFieldsBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MfConditionalFieldsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('apcu_prefix')->defaultNull()->end()
            ->end()
        ;
    }
}
