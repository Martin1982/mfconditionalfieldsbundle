<?php

namespace Martin1982\MfConditionalFieldsBundle\Form\Extension;

use Martin1982\MfConditionalFieldsBundle\Exception\ConditionalFieldException;
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!array_key_exists('conditional_options', $options)) {
            return;
        }

        $conditionalOptions = $options['conditional_options'];

        if (!array_key_exists('container', $conditionalOptions)) {
            throw new ConditionalFieldException('container is required in conditional_options');
        }

        if (!array_key_exists('action', $conditionalOptions) || !in_array($conditionalOptions['action'], [
            ConditionalRulesInterface::ACTION_SHOW,
            ConditionalRulesInterface::ACTION_HIDE,
            ConditionalRulesInterface::ACTION_ENABLE,
            ConditionalRulesInterface::ACTION_DISABLE,
        ])) {
            throw new ConditionalFieldException('a valid `action` value is required in conditional_options');
        }

        if (!array_key_exists('logic', $conditionalOptions) || !in_array($conditionalOptions['logic'], [
            ConditionalRulesInterface::LOGIC_OR,
            ConditionalRulesInterface::LOGIC_AND,
        ])) {
            throw new ConditionalFieldException('a valid `logic` value is required in conditional_options');
        }

        if (!array_key_exists('rules', $conditionalOptions) || count($conditionalOptions['rules']) < 1) {
            throw new ConditionalFieldException('No rules defined');
        }
    }
}
