<?php

declare(strict_types=1);

use Martin1982\MfConditionalFieldsBundle\Form\Extension\ConditionalFieldExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('mfconditionalfields.form_extension', ConditionalFieldExtension::class)
            ->tag('form.type_extension');
};
