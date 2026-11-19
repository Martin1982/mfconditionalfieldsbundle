<?php

declare(strict_types=1);

namespace Martin1982\MfConditionalFieldsBundle\Tests\Form\Extension;

use Martin1982\MfConditionalFieldsBundle\Exception\ConditionalFieldException;
use Martin1982\MfConditionalFieldsBundle\Form\Extension\ConditionalFieldExtension;
use Martin1982\MfConditionalFieldsBundle\Rules\ConditionalRulesInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;

final class ConditionalFieldExtensionTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new ConditionalFieldExtension())
            ->getFormFactory();
    }

    public function testFieldWithoutConditionalOptionsIsUntouched(): void
    {
        $view = $this->factory->createNamed('attend', FormType::class)
            ->add('reason', TextType::class)
            ->createView();

        self::assertArrayNotHasKey('data-conditional-rules', $view->children['reason']->vars['attr']);
        self::assertArrayNotHasKey('conditionalFieldJs', $view->children);
    }

    public function testConditionalOptionsAreRenderedAsDataAttribute(): void
    {
        $view = $this->createAttendForm()->createView();

        self::assertSame(
            [
                'container' => '.reason-container',
                'action' => ConditionalRulesInterface::ACTION_SHOW,
                'logic' => ConditionalRulesInterface::LOGIC_OR,
                'rules' => [
                    [
                        'name' => 'attend[isAttending]',
                        'operator' => ConditionalRulesInterface::OPERATOR_IS,
                        'value' => '2',
                    ],
                ],
            ],
            $this->decodeRules($this->attributeFor($view->children['reason'])),
        );
    }

    public function testContainerIsAppliedAsRowClass(): void
    {
        $view = $this->createAttendForm()->createView();

        self::assertSame('reason-container', $view->children['reason']->vars['row_attr']['class']);
    }

    public function testRootFormReceivesAnIdAndTheJavaScriptBlock(): void
    {
        $view = $this->createAttendForm()->createView();

        self::assertSame('attend', $view->vars['attr']['id']);
        self::assertArrayHasKey('conditionalFieldJs', $view->children);
        self::assertSame('#attend', $view->children['conditionalFieldJs']->vars['form_id']);
    }

    public function testRuleNamesAreBuiltFromTheFullFormHierarchy(): void
    {
        $builder = $this->factory->createNamedBuilder('attend', FormType::class);
        $details = $builder->create('details', FormType::class);
        $details->add('isAttending', ChoiceType::class, ['choices' => ['Maybe' => 2, 'Yes' => 1, 'No' => 0]]);
        $details->add('reason', TextType::class, [
            'conditional_options' => $this->conditionalOptions(),
        ]);
        $builder->add($details);

        $view = $builder->getForm()->createView();
        $rules = $this->decodeRules($this->attributeFor($view->children['details']->children['reason']));

        self::assertSame('attend[details][isAttending]', $rules['rules'][0]['name']);
    }

    /**
     * A compound child without conditional fields used to abort the search, hiding
     * conditional fields declared on any sibling rendered after it.
     */
    public function testConditionalFieldsAreDetectedAfterACompoundSiblingWithoutRules(): void
    {
        $builder = $this->factory->createNamedBuilder('attend', FormType::class);
        $personal = $builder->create('personal', FormType::class);
        $personal->add('name', TextType::class);
        $builder->add($personal);
        $builder->add('isAttending', ChoiceType::class, ['choices' => ['Maybe' => 2, 'Yes' => 1, 'No' => 0]]);
        $builder->add('reason', TextType::class, ['conditional_options' => $this->conditionalOptions()]);

        $view = $builder->getForm()->createView();

        self::assertArrayHasKey('conditionalFieldJs', $view->children);
    }

    public function testNumericRuleNamesAreSupported(): void
    {
        $options = self::optionsWith([
            'rules' => [['name' => 0, 'operator' => ConditionalRulesInterface::OPERATOR_IS, 'value' => '2']],
        ]);

        $view = $this->factory->createNamed('attend', FormType::class)
            ->add('reason', TextType::class, ['conditional_options' => $options])
            ->createView();

        self::assertSame('attend[0]', $this->decodeRules($this->attributeFor($view->children['reason']))['rules'][0]['name']);
    }

    public function testTheJavaScriptBlockIsAddedOnlyOnceForNestedForms(): void
    {
        $builder = $this->factory->createNamedBuilder('attend', FormType::class);
        $details = $builder->create('details', FormType::class);
        $guest = $details->create('guest', FormType::class);
        $guest->add('reason', TextType::class, ['conditional_options' => self::conditionalOptions()]);
        $details->add($guest);
        $builder->add($details);

        $view = $builder->getForm()->createView();

        self::assertSame(1, $this->countJavaScriptBlocks($view));
    }

    public function testTheJavaScriptBlockIsAddedToTheRootFormOnly(): void
    {
        $builder = $this->factory->createNamedBuilder('attend', FormType::class);
        $details = $builder->create('details', FormType::class);
        $details->add('reason', TextType::class, ['conditional_options' => self::conditionalOptions()]);
        $builder->add($details);

        $view = $builder->getForm()->createView();

        self::assertArrayHasKey('conditionalFieldJs', $view->children);
        self::assertArrayNotHasKey('conditionalFieldJs', $view->children['details']->children);
        self::assertSame('#attend', $view->children['conditionalFieldJs']->vars['form_id']);
    }

    public function testNestedCompoundViewsKeepTheirOwnRenderedId(): void
    {
        $builder = $this->factory->createNamedBuilder('attend', FormType::class);
        $details = $builder->create('details', FormType::class);
        $details->add('reason', TextType::class, ['conditional_options' => self::conditionalOptions()]);
        $builder->add($details);

        $view = $builder->getForm()->createView();

        self::assertSame('attend', $view->vars['attr']['id']);
        self::assertArrayNotHasKey('id', $view->children['details']->vars['attr']);
    }

    public function testTheJavaScriptBlockTargetsACustomRootFormId(): void
    {
        $view = $this->factory->createNamed('attend', FormType::class, null, ['attr' => ['id' => 'custom-form-id']])
            ->add('reason', TextType::class, ['conditional_options' => self::conditionalOptions()])
            ->createView();

        self::assertSame('custom-form-id', $view->vars['attr']['id']);
        self::assertSame('#custom-form-id', $view->children['conditionalFieldJs']->vars['form_id']);
    }

    public function testEmptyOperatorsDoNotRequireAValue(): void
    {
        $options = $this->conditionalOptions();
        $options['rules'] = [
            ['name' => 'isAttending', 'operator' => ConditionalRulesInterface::OPERATOR_IS_EMPTY],
            ['name' => 'isAttending', 'operator' => ConditionalRulesInterface::OPERATOR_IS_NOT_EMPTY],
        ];

        $view = $this->factory->createNamed('attend', FormType::class)
            ->add('reason', TextType::class, ['conditional_options' => $options])
            ->createView();

        self::assertCount(2, $this->decodeRules($this->attributeFor($view->children['reason']))['rules']);
    }

    /**
     * @param array<string, mixed> $conditionalOptions
     */
    #[DataProvider('invalidConditionalOptionsProvider')]
    public function testInvalidConditionalOptionsAreRejected(array $conditionalOptions, string $expectedMessage): void
    {
        $form = $this->factory->createNamed('attend', FormType::class)
            ->add('reason', TextType::class, ['conditional_options' => $conditionalOptions]);

        $this->expectException(ConditionalFieldException::class);
        $this->expectExceptionMessage($expectedMessage);

        $form->createView();
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidConditionalOptionsProvider(): iterable
    {
        yield 'missing container' => [
            self::optionsWithout('container'),
            'container is required in conditional_options',
        ];

        yield 'missing action' => [
            self::optionsWithout('action'),
            'a valid `action` value is required in conditional_options',
        ];

        yield 'unknown action' => [
            self::optionsWith(['action' => 'explode']),
            'a valid `action` value is required in conditional_options',
        ];

        yield 'missing logic' => [
            self::optionsWithout('logic'),
            'a valid `logic` value is required in conditional_options',
        ];

        yield 'unknown logic' => [
            self::optionsWith(['logic' => 'xor']),
            'a valid `logic` value is required in conditional_options',
        ];

        yield 'missing rules' => [
            self::optionsWithout('rules'),
            'No rules defined',
        ];

        yield 'empty rules' => [
            self::optionsWith(['rules' => []]),
            'No rules defined',
        ];

        yield 'rule without name' => [
            self::optionsWith(['rules' => [['operator' => ConditionalRulesInterface::OPERATOR_IS, 'value' => '2']]]),
            'a valid `name` value is required in conditional_options',
        ];

        yield 'rule without operator' => [
            self::optionsWith(['rules' => [['name' => 'isAttending', 'value' => '2']]]),
            'a valid `operator` value is required in conditional_options',
        ];

        yield 'rule with unknown operator' => [
            self::optionsWith(['rules' => [['name' => 'isAttending', 'operator' => 'vibes', 'value' => '2']]]),
            'a valid `operator` value is required in conditional_options',
        ];

        yield 'rule without value' => [
            self::optionsWith(['rules' => [['name' => 'isAttending', 'operator' => ConditionalRulesInterface::OPERATOR_IS]]]),
            'a valid `value` value is required in conditional_options',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function conditionalOptions(): array
    {
        return [
            'container' => 'reason-container',
            'action' => ConditionalRulesInterface::ACTION_SHOW,
            'logic' => ConditionalRulesInterface::LOGIC_OR,
            'rules' => [
                [
                    'name' => 'isAttending',
                    'operator' => ConditionalRulesInterface::OPERATOR_IS,
                    'value' => '2',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private static function optionsWith(array $overrides): array
    {
        return array_merge(self::conditionalOptions(), $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function optionsWithout(string $key): array
    {
        $options = self::conditionalOptions();
        unset($options[$key]);

        return $options;
    }

    private function createAttendForm(): FormInterface
    {
        return $this->factory->createNamed('attend', FormType::class)
            ->add('isAttending', ChoiceType::class, ['choices' => ['Maybe' => 2, 'Yes' => 1, 'No' => 0]])
            ->add('reason', TextType::class, ['conditional_options' => self::conditionalOptions()]);
    }

    private function attributeFor(FormView $view): string
    {
        self::assertIsString($view->vars['attr']['data-conditional-rules']);

        return $view->vars['attr']['data-conditional-rules'];
    }

    private function countJavaScriptBlocks(FormView $view): int
    {
        $count = 0;
        foreach ($view->children as $name => $child) {
            if ('conditionalFieldJs' === $name) {
                ++$count;
            }

            $count += $this->countJavaScriptBlocks($child);
        }

        return $count;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeRules(string $json): array
    {
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
