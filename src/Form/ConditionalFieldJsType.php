<?php

namespace Martin1982\MfConditionalFieldsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConditionalFieldJsType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'conditional_field_js';
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'mapped' => false,
            'label' => null,
        ]);
    }
}
