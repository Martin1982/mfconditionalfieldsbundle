<?php

declare(strict_types=1);

namespace Martin1982\MfConditionalFieldsBundle\Tests\Form;

use Martin1982\MfConditionalFieldsBundle\Form\ConditionalFieldJsType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class ConditionalFieldJsTypeTest extends TestCase
{
    public function testTheBlockPrefixMatchesTheShippedFormTheme(): void
    {
        self::assertSame('conditional_field_js', (new ConditionalFieldJsType())->getBlockPrefix());
    }

    public function testTheTypeIsCompoundAndUnmapped(): void
    {
        $form = Forms::createFormFactory()->create(ConditionalFieldJsType::class);

        self::assertTrue($form->getConfig()->getCompound());
        self::assertFalse($form->getConfig()->getMapped());
        self::assertNull($form->getConfig()->getOption('label'));
    }
}
