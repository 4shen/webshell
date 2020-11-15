<?php

use System\database\query;
use System\input;
use System\route;
use System\view;

Route::collection(['before' => 'auth,csrf,install_exists'], function () {

    /**
     * List Vars
     */
    Route::get('admin/extend/variables', function () {
        $vars['token'] = Csrf::token();
        $variables     = [];

        foreach (
            Query::table(Base::table('meta'))
                 ->sort('key')
                 ->get() as $meta
        ) {
            if (strpos($meta->key, 'custom_') === 0) {
                $variables[] = $meta;
            }
        }

        $vars['variables'] = $variables;

        return View::create('extend/variables/index', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    /**
     * Add Var
     */
    Route::get('admin/extend/variables/add', function () {
        $vars['token'] = Csrf::token();

        return View::create('extend/variables/add', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    Route::post('admin/extend/variables/add', function () {
        $input        = Input::get([
            'key',
            'value'
        ]);
        $input['key'] = 'custom_' . slug($input['key'], '_');

        foreach ($input as $key => &$value) {
            $value = eq($value);
        }

        $validator = new Validator($input);

        $validator->add('valid_key', function ($str) {
            return Query::table(Base::table('meta'))
                        ->where('key', '=', $str)
                        ->count() == 0;
        });

        // include prefix length 'custom_'
        $validator->check('key')
                  ->is_max(8, __('extend.name_missing'))
                  ->is_valid_key(__('extend.name_exists'));

        if ($errors = $validator->errors()) {
            Input::flash();
            Notify::error($errors);

            return Response::redirect('admin/extend/variables/add');
        }

        Query::table(Base::table('meta'))
             ->insert($input);

        Notify::success(__('extend.variable_created'));

        return Response::redirect('admin/extend/variables');
    });

    /**
     * Edit Var
     */
    Route::get('admin/extend/variables/edit/(:any)', function ($key) {

        $vars['token']    = Csrf::token();
        $vars['variable'] = Query::table(Base::table('meta'))
                                 ->where('key', '=', $key)
                                 ->fetch();

        // remove prefix
        $vars['variable']->user_key = substr($vars['variable']->key, strlen('custom_'));

        return View::create('extend/variables/edit', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    Route::post('admin/extend/variables/edit/(:any)', function ($key) {
        $input = Input::get(['key', 'value']);

        $original = slug($input['key'], '_');

        $validator = new Validator($input);

        $validator->add('valid_key', function ($str) use ($original) {

            // Don't check against the same key
            if ($str == $original) {
                return true;
            }

            return Query::table(Base::table('meta'))
                        ->where('key', '=', $str)
                        ->count() == 0;
        });

        $validator->check('key')
                  ->is_max(1, __('extend.name_missing'))
                  ->is_valid_key(__('extend.name_exists'));

        $key = $input['key'] = 'custom_' . $original;
        $value = $input['value'];

        if ($errors = $validator->errors()) {
            Input::flash();
            Notify::error($errors);

            return Response::redirect('admin/extend/variables/edit/' . $key);
        }

        Query::table(Base::table('meta'))
             ->where('key', '=', $key)
             ->update($input);

        Notify::success(__('extend.variable_updated'));

        return Response::redirect('admin/extend/variables');
    });

    /**
     * Delete Var
     */
    Route::get('admin/extend/variables/delete/(:any)', function ($key) {
        Query::table(Base::table('meta'))
             ->where('key', '=', $key)
             ->delete();

        Notify::success(__('extend.variable_deleted'));

        return Response::redirect('admin/extend/variables');
    });
});
