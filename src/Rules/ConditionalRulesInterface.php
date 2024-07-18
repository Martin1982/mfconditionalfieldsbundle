<?php

namespace Martin1982\MfConditionalFieldsBundle\Rules;

interface ConditionalRulesInterface
{
    public const ACTION_SHOW = 'show';
    public const ACTION_HIDE = 'hide';
    public const ACTION_ENABLE = 'enable';
    public const ACTION_DISABLE = 'disable';
    public const LOGIC_OR = 'or';
    public const LOGIC_AND = 'and';
    public const OPERATOR_IS = 'is';
    public const OPERATOR_IS_NOT = 'isnot';
    public const OPERATOR_GREATER_THAN = 'greaterthan';
    public const OPERATOR_LESS_THAN = 'lessthan';
    public const OPERATOR_CONTAINS = 'contains';
    public const OPERATOR_DOES_NOT_CONTAIN = 'doesnotcontain';
    public const OPERATOR_BEGINS_WITH = 'beginswith';
    public const OPERATOR_DOES_NOT_BEGIN_WITH = 'doesnotbeginwith';
    public const OPERATOR_ENDS_WITH = 'endswith';
    public const OPERATOR_DOES_NOT_END_WITH = 'doesnotendwith';
    public const OPERATOR_IS_EMPTY = 'isempty';
    public const OPERATOR_IS_NOT_EMPTY = 'isnotempty';
}