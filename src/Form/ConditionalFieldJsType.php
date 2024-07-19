<?php

namespace Martin1982\MfConditionalFieldsBundle\Form;

use Symfony\Component\Form\AbstractType;

class ConditionalFieldJsType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'conditional_field_js';
    }
}
