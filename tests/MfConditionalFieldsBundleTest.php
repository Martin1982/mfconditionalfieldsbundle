<?php

declare(strict_types=1);

namespace Martin1982\MfConditionalFieldsBundle\Tests;

use Generator;
use Martin1982\MfConditionalFieldsBundle\Form\Extension\ConditionalFieldExtension;
use Martin1982\MfConditionalFieldsBundle\MfConditionalFieldsBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class MfConditionalFieldsBundleTest extends TestCase
{
    public function testTheFormExtensionIsRegisteredAndTagged(): void
    {
        $bundle = new MfConditionalFieldsBundle();
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.debug' => false,
            'kernel.build_dir' => sys_get_temp_dir(),
        ]));
        $extension = $bundle->getContainerExtension();
        self::assertInstanceOf(ExtensionInterface::class, $extension);

        $extension->load([[]], $container);

        self::assertTrue($container->hasDefinition('mfconditionalfields.form_extension'));
        self::assertSame(
            ConditionalFieldExtension::class,
            $container->getDefinition('mfconditionalfields.form_extension')->getClass(),
        );
        self::assertTrue($container->getDefinition('mfconditionalfields.form_extension')->hasTag('form.type_extension'));
    }

    public function testTheBundlePathExposesTheShippedFormTheme(): void
    {
        self::assertFileExists((new MfConditionalFieldsBundle())->getPath().'/templates/conditional_field.html.twig');
    }

    public function testTheExtensionIsRegisteredForTheBaseFormType(): void
    {
        self::assertSame(
            [\Symfony\Component\Form\Extension\Core\Type\FormType::class],
            iterator_to_array((function (): Generator { yield from ConditionalFieldExtension::getExtendedTypes(); })()),
        );
    }
}
