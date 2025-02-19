<?php

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
            $view->children['conditionalFieldJs']->vars['form_id'] = '#' . $form->getRoot()->getName();
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        if (!array_key_exists('conditional_options', $options)) {
            return;
        }

        $conditionalOptions = $options['conditional_options'];
        $this->buildRow($view, $conditionalOptions);

        $attributes['data-conditional-rules'] = [ 'container' => '.' . $conditionalOptions['container']];

        if (!array_key_exists('action', $conditionalOptions) || !in_array($conditionalOptions['action'], [
                ConditionalRulesInterface::ACTION_SHOW,
                ConditionalRulesInterface::ACTION_HIDE,
                ConditionalRulesInterface::ACTION_ENABLE,
                ConditionalRulesInterface::ACTION_DISABLE,
            ])) {
            throw new ConditionalFieldException('a valid `action` value is required in conditional_options');
        }

        $attributes['data-conditional-rules']['action'] = $conditionalOptions['action'];

        if (!array_key_exists('logic', $conditionalOptions) || !in_array($conditionalOptions['logic'], [
                ConditionalRulesInterface::LOGIC_OR,
                ConditionalRulesInterface::LOGIC_AND,
            ])) {
            throw new ConditionalFieldException('a valid `logic` value is required in conditional_options');
        }

        $attributes['data-conditional-rules']['logic'] = $conditionalOptions['logic'];

        if (
            !array_key_exists('rules', $conditionalOptions) ||
            !is_array($conditionalOptions['rules']) ||
            count($conditionalOptions['rules']) < 1
        ) {
            throw new ConditionalFieldException('No rules defined');
        }

        $attributes['data-conditional-rules']['rules'] = [];

        foreach ($conditionalOptions['rules'] as $rule) {
            $this->validateRule($rule);

            $parent = $form;
            $typeNames = [];
            while ($parent = $parent->getParent()) {
                $typeNames[] = $parent->getName();
            }
            $typeNames = array_reverse($typeNames);
            $fieldName = array_shift($typeNames);
            $typeNames[] = $rule['name'];
            $fieldName .= '[' . implode('][', $typeNames) . ']';

            $rule['name'] = $fieldName;
            $attributes['data-conditional-rules']['rules'][] = $rule;
        }

        $attributes['data-conditional-rules'] = json_encode($attributes['data-conditional-rules']);

        $view->vars['attr'] = array_merge($view->vars['attr'], $attributes);
    }

    private function validateRule(array $rule): void
    {
        if (!array_key_exists('name', $rule)) {
            throw new ConditionalFieldException('a valid `name` value is required in conditional_options');
        }

        if (!array_key_exists('operator', $rule) || !in_array($rule['operator'], [
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
            ])) {
            throw new ConditionalFieldException('a valid `operator` value is required in conditional_options');
        }

        if (!array_key_exists('value', $rule) &&
            $rule['operator'] !== ConditionalRulesInterface::OPERATOR_IS_EMPTY &&
            $rule['operator'] !== ConditionalRulesInterface::OPERATOR_IS_NOT_EMPTY) {
            throw new ConditionalFieldException('a valid `value` value is required in conditional_options');
        }
    }

    private function buildRow(FormView $view, array $conditionalOptions): void
    {
        if (!array_key_exists('container', $conditionalOptions)) {
            throw new ConditionalFieldException('container is required in conditional_options');
        }

        $view->vars['row_attr'] =  array_merge(
            $view->vars['row_attr'],
            ['class' => $conditionalOptions['container']],
        );
    }

    private function viewHasConditionalFields(FormView $view): bool
    {
        $hasConditionalFields = false;
        foreach ($view->children as $child) {
            if (array_key_exists('attr', $child->vars) && array_key_exists('data-conditional-rules', $child->vars['attr'])) {
                $hasConditionalFields = true;
                break;
            }

            if ($child->children) {
                $hasConditionalFields = $this->viewHasConditionalFields($child);
                break;
            }
        }
        return $hasConditionalFields;
    }
}
