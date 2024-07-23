Prerequisites
=============

Make sure you load the [mf-conditional fields JS library](https://github.com/bomsn/mf-conditional-fields) in your
project on the pages where you use conditional fields.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
composer require martin1982/mfconditionalfieldsbundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require martin1982/mfconditionalfieldsbundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Martin1982\MfConditionalFieldsBundle\MfConditionalFieldsBundle::class => ['all' => true],
];
```

### Step 3: Enable the form theme

When using Twig you can initialize a form with conditional fields using a `form_theme` setting in your twig config:

```yaml
twig:
    form_themes: ['@MfConditionalFieldsBundle/conditional_field.html.twig']
```

Usage
=====

On your FormType class implement the `ConditionalRulesInterface` for easy access to all options. When adding a field
using the FormBuilder you can make a field dependent by providing the `conditional_options` option.

The following options are available:

| Name      |  Type  |                                                             Description |
|:----------|:------:|------------------------------------------------------------------------:|
| container | String |                                      The container for the given action |
| action    | String |              The action that needs to be performed when the rules apply |
| logic     | String | OR when only one condition needs to be met, AND when all need to be met |
| rules     | Array  |                           Array of rules to check, with at least 1 rule |

The rules consist of these options:

| Name     |  Type  |                          Description |
|:---------|:------:|-------------------------------------:|
| name     | String |                  Field name to check |
| operator | String |         Operator used to check field |
| value    | String | Expected value for the rule to apply |

Example
=======

In this example the code from the Symfony documentation is used to select if someone is attending. In addition, it'll 
show a reason text element when a user selects 'Maybe' as an option.

```php
<?php

declare(strict_types=1);

namespace App\Form;

use Martin1982\MfConditionalFieldsBundle\Rules\ConditionalRulesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class AttendType extends AbstractType implements ConditionalRulesInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isAttending', ChoiceType::class, [
                'choices'  => [
                    'Maybe' => 2,
                    'Yes' => 1,
                    'No' => 0,
                ],
            ])
            ->add('reason', TextType::class, [
                'conditional_options' => [
                    'container' => 'reason-container',
                    'action' => self::ACTION_SHOW,
                    'logic' => self::LOGIC_OR,
                    'rules' => [
                        [
                            'name' => 'isAttending',
                            'operator' => self::OPERATOR_IS,
                            'value' => '2',
                        ],
                    ],
                ],
            ])
        ;    
    }
}

```

Future releases / Contribute
============================

This bundle includes a basic implementation. If you would like to contribute all options of the mf-conditional-fields
bundle can be added.

Special thanks
==============

Special thanks to [Ali Khallad](https://github.com/bomsn) for creating this JavaScript library.