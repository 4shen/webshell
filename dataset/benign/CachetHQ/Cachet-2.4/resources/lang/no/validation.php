<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'   => ':attribute må være godkjent.',
    'active_url' => ':attribute er ikke en gyldig URL.',
    'after'      => ':attribute må være en dato etter :date.',
    'alpha'      => ':attribute kan kun inneholde bokstaver.',
    'alpha_dash' => ':attribute kan kun inneholde bokstaver, tall og bindestreker.',
    'alpha_num'  => ':attribute kan bare inneholde bokstaver og tall.',
    'array'      => ':attribute må være en matrise.',
    'before'     => ':attribute må være en dato før :date.',
    'between'    => [
        'numeric' => ':attribute må være en dato før :date.',
        'file'    => 'The :attribute must be between :min and :max.',
        'string'  => 'The :attribute must be between :min and :max kilobytes.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'        => 'The :attribute must have between :min and :max items.',
    'confirmed'      => 'The :attribute field must be true or false.',
    'date'           => 'The :attribute confirmation does not match.',
    'date_format'    => 'The :attribute is not a valid date.',
    'different'      => 'The :attribute does not match the format :format.',
    'digits'         => 'The :attribute and :other must be different.',
    'digits_between' => 'The :attribute must be :digits digits.',
    'email'          => 'The :attribute must be between :min and :max digits.',
    'exists'         => 'The :attribute must be a valid email address.',
    'distinct'       => 'The :attribute field has a duplicate value.',
    'filled'         => ':attribute formatet er ugyldig.',
    'image'          => ':attribute må være et bilde.',
    'in'             => ':attribute må være et bilde.',
    'in_array'       => 'The :attribute field does not exist in :other.',
    'integer'        => 'The selected :attribute is invalid.',
    'ip'             => 'The :attribute must be an integer.',
    'json'           => 'The :attribute must be a valid JSON string.',
    'max'            => [
        'numeric' => 'The :attribute must be a valid IP address.',
        'file'    => 'The :attribute may not be greater than :max.',
        'string'  => 'The :attribute may not be greater than :max kilobytes.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute may not have more than :max items.',
    'min'   => [
        'numeric' => 'The :attribute must be a file of type: :values.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min kilobytes.',
        'array'   => 'The :attribute must be at least :min characters.',
    ],
    'not_in'               => 'The :attribute must have at least :min items.',
    'numeric'              => 'The selected :attribute is invalid.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute must be a number.',
    'required'             => ':attribute formatet er ugyldig.',
    'required_if'          => 'The :attribute field is required.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :other is :value.',
    'required_with_all'    => ':attribute feltet kreves når :values er til stede.',
    'required_without'     => ':attribute feltet kreves når :values er til stede.',
    'required_without_all' => 'The :attribute field is required when :values is not present.',
    'same'                 => 'The :attribute field is required when none of :values are present.',
    'size'                 => [
        'numeric' => 'The :attribute and :other must match.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must be :size characters.',
    ],
    'string'   => 'The :attribute must contain :size items.',
    'timezone' => ':attribute må være en gyldig tidssone.',
    'unique'   => ':attribute er allerede tatt.',
    'url'      => ':attribute formatet er ugyldig.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'tilpasset melding',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
