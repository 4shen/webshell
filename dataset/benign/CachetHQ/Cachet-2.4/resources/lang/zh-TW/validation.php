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

    'accepted'   => '這個 :attribute 必須被接受',
    'active_url' => '這個 :attribute 不是一個有效的網址',
    'after'      => ':attribute 必須在 :date 之後',
    'alpha'      => ':attribute 只能包含字母',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號',
    'alpha_num'  => ':attribute 只能包含字母及數字',
    'array'      => ':attribute 必須是一個陣列',
    'before'     => ':attribute 必須在 :date 之前',
    'between'    => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'        => 'The :attribute field must be true or false.',
    'confirmed'      => 'The :attribute confirmation does not match.',
    'date'           => 'The :attribute is not a valid date.',
    'date_format'    => 'The :attribute does not match the format :format.',
    'different'      => 'The :attribute and :other must be different.',
    'digits'         => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'email'          => 'The :attribute must be a valid email address.',
    'exists'         => 'The selected :attribute is invalid.',
    'distinct'       => 'The :attribute field has a duplicate value.',
    'filled'         => 'The :attribute field is required.',
    'image'          => ':attribute 必須是圖片',
    'in'             => 'The selected :attribute is invalid.',
    'in_array'       => 'The :attribute field does not exist in :other.',
    'integer'        => 'The :attribute must be an integer.',
    'ip'             => 'The :attribute must be a valid IP address.',
    'json'           => 'The :attribute must be a valid JSON string.',
    'max'            => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'min'   => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => ':attribute 格式無效',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => ':attribute 欄位在 :values 存在時，是必填的',
    'required_with_all'    => ':attribute 欄位在 :values 存在時，是必填的',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'   => 'The :attribute must be a string.',
    'timezone' => ':attribute 必須是有效的區域',
    'unique'   => ':attribute 已經被佔用',
    'url'      => ':attribute 格式無效',

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
            'rule-name' => '自訂訊息',
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
