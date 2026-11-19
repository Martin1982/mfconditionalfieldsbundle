<?php

declare(strict_types=1);

namespace Martin1982\MfConditionalFieldsBundle\Form\Extension;

use Martin1982\MfConditionalFieldsBundle\Exception\ConditionalFieldException;
use Martin1982\MfConditionalFieldsBundle\Form\ConditionalFieldJsType;
use Martin1982\MfConditionalFieldsBundle\Rules\ConditionalRulesInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConditionalFieldExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['conditional_options']);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($form->isRoot() && !isset($view->vars['attr']['id'])) {
            $view->vars['attr']['id'] = $view->vars['id'];
        }

        if ($this->viewHasConditionalFields($view)) {
            $factory = $form->getConfig()->getFormFactory();
            $jsBlock = $factory->createNamed('conditionalFieldJs', ConditionalFieldJsType::class);

            if (!isset($view->vars['attr']['id'])) {
                $view->vars['attr']['id'] = $view->vars['id'];
            }
            $view->children['conditionalFieldJs'] = $jsBlock->createView();
            $view->children['conditionalFieldJs']->vars['form_id'] = '#'.$form->getRoot()->getName();
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        if (!\array_key_exists('conditional_options', $options)) {
            return;
        }

        $conditionalOptions = $options['conditional_options'];

        if (!\is_array($conditionalOptions)) {
            throw new ConditionalFieldException('conditional_options must be an array');
        }

        $this->buildRow($view, $conditionalOptions);

        $rules = ['container' => '.'.$conditionalOptions['container']];

        if (!\array_key_exists('action', $conditionalOptions) || !\in_array($conditionalOptions['action'], [
            ConditionalRulesInterface::ACTION_SHOW,
            ConditionalRulesInterface::ACTION_HIDE,
            ConditionalRulesInterface::ACTION_ENABLE,
            ConditionalRulesInterface::ACTION_DISABLE,
        ], true)) {
            throw new ConditionalFieldException('a valid `action` value is required in conditional_options');
        }

        $rules['action'] = $conditionalOptions['action'];

        if (!\array_key_exists('logic', $conditionalOptions) || !\in_array($conditionalOptions['logic'], [
            ConditionalRulesInterface::LOGIC_OR,
            ConditionalRulesInterface::LOGIC_AND,
        ], true)) {
            throw new ConditionalFieldException('a valid `logic` value is required in conditional_options');
        }

        $rules['logic'] = $conditionalOptions['logic'];

        if (
            !\array_key_exists('rules', $conditionalOptions)
            || !\is_array($conditionalOptions['rules'])
            || \count($conditionalOptions['rules']) < 1
        ) {
            throw new ConditionalFieldException('No rules defined');
        }

        $rules['rules'] = [];

        foreach ($conditionalOptions['rules'] as $rule) {
            if (!\is_array($rule)) {
                throw new ConditionalFieldException('every rule in conditional_options must be an array');
            }

            $this->validateRule($rule);

            $rule['name'] = $this->buildFieldName($form, $rule['name']);
            $rules['rules'][] = $rule;
        }

        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'data-conditional-rules' => json_encode($rules, \JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Expresses a sibling field name the way it is rendered in the DOM, taking any
     * parent form types into account (e.g. `root[child][field]`).
     */
    private function buildFieldName(FormInterface $form, string|int $name): string
    {
        $typeNames = [];
        $parent = $form;
        while ($parent = $parent->getParent()) {
            $typeNames[] = $parent->getName();
        }

        $typeNames = array_reverse($typeNames);
        $fieldName = array_shift($typeNames);
        $typeNames[] = (string) $name;

        return $fieldName.'['.implode('][', $typeNames).']';
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function validateRule(array $rule): void
    {
        if (!\array_key_exists('name', $rule) || (!\is_string($rule['name']) && !\is_int($rule['name']))) {
            throw new ConditionalFieldException('a valid `name` value is required in conditional_options');
        }

        if (!\array_key_exists('operator', $rule) || !\in_array($rule['operator'], [
            ConditionalRulesInterface::OPERATOR_IS,
            ConditionalRulesInterface::OPERATOR_IS_NOT,
            ConditionalRulesInterface::OPERATOR_GREATER_THAN,
            ConditionalRulesInterface::OPERATOR_LESS_THAN,
            ConditionalRulesInterface::OPERATOR_CONTAINS,
            ConditionalRulesInterface::OPERATOR_DOES_NOT_CONTAIN,
            ConditionalRulesInterface::OPERATOR_BEGINS_WITH,
            ConditionalRulesInterface::OPERATOR_DOES_NOT_BEGIN_WITH,
            ConditionalRulesInterface::OPERATOR_ENDS_WITH,
            ConditionalRulesInterface::OPERATOR_DOES_NOT_END_WITH,
            ConditionalRulesInterface::OPERATOR_IS_EMPTY,
            ConditionalRulesInterface::OPERATOR_IS_NOT_EMPTY,
        ], true)) {
            throw new ConditionalFieldException('a valid `operator` value is required in conditional_options');
        }

        if (!\array_key_exists('value', $rule)
            && ConditionalRulesInterface::OPERATOR_IS_EMPTY !== $rule['operator']
            && ConditionalRulesInterface::OPERATOR_IS_NOT_EMPTY !== $rule['operator']) {
            throw new ConditionalFieldException('a valid `value` value is required in conditional_options');
        }
    }

    /**
     * @param array<string, mixed> $conditionalOptions
     */
    private function buildRow(FormView $view, array $conditionalOptions): void
    {
        if (!\array_key_exists('container', $conditionalOptions) || !\is_string($conditionalOptions['container'])) {
            throw new ConditionalFieldException('container is required in conditional_options');
        }

        $view->vars['row_attr'] = array_merge(
            $view->vars['row_attr'],
            ['class' => $conditionalOptions['container']],
        );
    }

    private function viewHasConditionalFields(FormView $view): bool
    {
        foreach ($view->children as $child) {
            if (isset($child->vars['attr']['data-conditional-rules'])) {
                return true;
            }

            if ($child->children && $this->viewHasConditionalFields($child)) {
                return true;
            }
        }

        return false;
    }
}
