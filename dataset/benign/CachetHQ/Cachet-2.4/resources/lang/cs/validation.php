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

    'accepted'   => 'Je potřeba potvrdit :attribute.',
    'active_url' => ':attribute není platná adresa URL.',
    'after'      => ':attribute musí být datum po :date.',
    'alpha'      => ':attribute může obsahovat pouze písmena.',
    'alpha_dash' => ':attribute může obsahovat pouze písmena, čísla a pomlčky.',
    'alpha_num'  => ':attribute může obsahovat pouze písmena a čísla.',
    'array'      => ':attribute musí být textové pole.',
    'before'     => ':attribute musí být datum před :date.',
    'between'    => [
        'numeric' => ':attribute musí být datum před :date.',
        'file'    => 'The :attribute must be between :min and :max.',
        'string'  => 'The :attribute must be between :min and :max kilobytes.',
        'array'   => ':attribute musí mít mezi :min a :max položkami.',
    ],
    'boolean'        => ':attribute musí mít mezi :min a :max položkami.',
    'confirmed'      => 'The :attribute field must be true or false.',
    'date'           => 'The :attribute confirmation does not match.',
    'date_format'    => 'The :attribute is not a valid date.',
    'different'      => 'The :attribute does not match the format :format.',
    'digits'         => 'The :attribute and :other must be different.',
    'digits_between' => 'The :attribute must be :digits digits.',
    'email'          => 'The :attribute must be between :min and :max digits.',
    'exists'         => 'The :attribute must be a valid email address.',
    'distinct'       => 'The :attribute field has a duplicate value.',
    'filled'         => 'Formát :attribute je neplatný.',
    'image'          => ':attribute musí být obrázek.',
    'in'             => ':attribute musí být obrázek.',
    'in_array'       => 'The :attribute field does not exist in :other.',
    'integer'        => 'The selected :attribute is invalid.',
    'ip'             => 'The :attribute must be an integer.',
    'json'           => 'The :attribute must be a valid JSON string.',
    'max'            => [
        'numeric' => 'The :attribute must be a valid IP address.',
        'file'    => 'The :attribute may not be greater than :max.',
        'string'  => 'The :attribute may not be greater than :max kilobytes.',
        'array'   => 'Atribut nesmí mít více než :max položek.',
    ],
    'mimes' => 'Atribut nesmí mít více než :max položek.',
    'min'   => [
        'numeric' => 'The :attribute must be a file of type: :values.',
        'file'    => 'Atribut musí mít alespoň :min kB.',
        'string'  => 'Atribut musí mít alespoň :min kB.',
        'array'   => 'The :attribute must be at least :min characters.',
    ],
    'not_in'               => 'The :attribute must have at least :min items.',
    'numeric'              => 'The selected :attribute is invalid.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute must be a number.',
    'required'             => 'Formát :attribute je neplatný.',
    'required_if'          => 'The :attribute field is required.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :other is :value.',
    'required_with_all'    => 'Pole :attribute je požadováno, když je zadané :values.',
    'required_without'     => 'Pole :attribute je požadováno, když je zadané :values.',
    'required_without_all' => 'The :attribute field is required when :values is not present.',
    'same'                 => 'The :attribute field is required when none of :values are present.',
    'size'                 => [
        'numeric' => 'The :attribute and :other must match.',
        'file'    => 'Atribut musí mít :size kB.',
        'string'  => 'Atribut musí mít :size znaků.',
        'array'   => 'Atribut musí mít :size znaků.',
    ],
    'string'   => 'The :attribute must contain :size items.',
    'timezone' => ':attribute musí být platná zóna.',
    'unique'   => ':attribute byl už použit.',
    'url'      => 'Formát :attribute je neplatný.',

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
            'rule-name' => 'vlastní zpráva',
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
