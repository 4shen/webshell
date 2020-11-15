<?php

use System\config;
use System\database\query;
use System\input;
use System\route;
use System\uri;
use System\view;

Route::collection(['before' => 'auth,csrf,install_exists'], function () {

    /**
     * List all posts and paginate through them
     */
    Route::get([
        'admin/posts',
        'admin/posts/(:num)'
    ], function ($page = 1) {
        $perpage = Config::get('admin.posts_per_page');
        $total   = Post::count();
        $url     = Uri::to('admin/posts');
        $posts   = Post::sort('created', 'desc')
                       ->take($perpage)
                       ->skip(($page - 1) * $perpage)
                       ->get();

        $pagination = new Paginator($posts, $total, $page, $perpage, $url);

        $vars['posts']      = $pagination;
        $vars['categories'] = Category::sort('title')->get();
        $vars['status']     = 'all';

        return View::create('posts/index', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    /**
     * List posts by category and paginate through them
     */
    Route::get([
        'admin/posts/category/(:any)',
        'admin/posts/category/(:any)/(:num)'
    ], function ($slug, $page = 1) {
        if ( ! $category = Category::slug($slug)) {
            return Response::error(404);
        }

        $query   = Post::where('category', '=', $category->id);
        $perpage = Config::get('admin.posts_per_page');
        $total   = $query->count();
        $url     = Uri::to('admin/posts/category/' . $category->slug);
        $posts   = $query->sort('created', 'desc')
                         ->take($perpage)
                         ->skip(($page - 1) * $perpage)
                         ->get();

        $pagination = new Paginator($posts, $total, $page, $perpage, $url);

        $vars['posts']      = $pagination;
        $vars['category']   = $category;
        $vars['categories'] = Category::sort('title')->get();
        $vars['status']     = 'all';

        return View::create('posts/index', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    /**
     * List posts by status and paginate through them
     */
    Route::get([
        'admin/posts/status/(:any)',
        'admin/posts/status/(:any)/(:num)'
    ], function ($status, $post = 1) {
        $query   = Post::where('status', '=', $status);
        $perpage = Config::get('admin.posts_per_page');
        $total   = $query->count();
        $url     = Uri::to('admin/posts/status/' . $status);
        $posts   = $query->sort('title')
                         ->take($perpage)
                         ->skip(($post - 1) * $perpage)
                         ->get();

        $pagination = new Paginator($posts, $total, $post, $perpage, $url);

        $vars['posts']      = $pagination;
        $vars['status']     = $status;
        $vars['categories'] = Category::sort('title')->get();

        return View::create('posts/index', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer');
    });

    /**
     * Edit post
     */
    Route::get('admin/posts/edit/(:num)', function ($id) {
        $vars['token']   = Csrf::token();
        $vars['article'] = Post::find($id);
        $vars['page']    = Registry::get('posts_page');

        // extended fields
        $vars['fields']     = Extend::fields('post', $id);
        $vars['categories'] = Category::dropdown();
        $vars['statuses']   = [
            'published' => __('global.published'),
            'draft'     => __('global.draft'),
            'archived'  => __('global.archived')
        ];

        return View::create('posts/edit', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer')
                   ->partial('editor', 'partials/editor');
    });

    Route::post('admin/posts/edit/(:num)', function ($id) {
        $input = Input::get([
            'title',
            'slug',
            'description',
            'created',
            'markdown',
            'css',
            'js',
            'category',
            'status',
            'comments'
        ]);

        // if there is no slug try and create one from the title
        if (empty($input['slug'])) {
            $input['slug'] = $input['title'];
        }

        // convert to ascii
        $input['slug'] = slug($input['slug']);

        // an array of items that we shouldn't encode - they're no XSS threat
        $dont_encode = ['description', 'markdown', 'css', 'js'];

        foreach ($input as $key => &$value) {
            if (in_array($key, $dont_encode)) {
                continue;
            }

            $value = eq($value);
        }

        $validator = new Validator($input);

        $validator->add('duplicate', function ($str) use ($id) {
            return Post::where('slug', '=', $str)
                       ->where('id', '<>', $id)
                       ->count() == 0;
        });

        $validator->check('title')
                  ->is_max(3, __('posts.title_missing'));

        $validator->check('slug')
                  ->is_max(3, __('posts.slug_missing'))
                  ->is_duplicate(__('posts.slug_duplicate'))
                  ->not_regex('#^[0-9_-]+$#', __('posts.slug_invalid'));

        $validator->check('created')
                  ->is_regex(
                      '#^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$#',
                      __('posts.time_invalid')
                  );

        if ($errors = $validator->errors()) {
            Input::flash();

            // Notify::error($errors);

            return Response::json([
                'id'     => $id,
                'errors' => array_flatten($errors, [])
            ]);
        }

        if (empty($input['comments'])) {
            $input['comments'] = 0;
        }

        if (empty($input['markdown'])) {
            $input['status'] = 'draft';
        }

        $input['html'] = parse($input['markdown']);

        Post::update($id, $input);
        Extend::process('post', $id);

        // Notify::success(__('posts.updated'));

        return Response::json([
            'id'           => $id,
            'notification' => __('posts.updated')
        ]);
    });

    /**
     * Add new post
     */
    Route::get('admin/posts/add', function () {
        $vars['token'] = Csrf::token();
        $vars['page']  = Registry::get('posts_page');

        // extended fields
        $vars['fields']     = Extend::fields('post');
        $vars['categories'] = Category::dropdown();
        $vars['statuses']   = [
            'published' => __('global.published'),
            'draft'     => __('global.draft'),
            'archived'  => __('global.archived')
        ];

        $table = Base::table('meta');
        $meta  = [];

        // load database metadata
        foreach (Query::table($table)->get() as $item) {
            $meta[$item->key] = $item->value;
        }

        $checked_comments = Input::previous('auto_published_comments', $meta['auto_published_comments']) ? ' checked' : '';

        $vars['checked_comments'] = $checked_comments;

        return View::create('posts/add', $vars)
                   ->partial('header', 'partials/header')
                   ->partial('footer', 'partials/footer')
                   ->partial('editor', 'partials/editor');
    });

    Route::post('admin/posts/add', function () {
        $input = Input::get([
            'title',
            'slug',
            'description',
            'created',
            'markdown',
            'css',
            'js',
            'category',
            'status',
            'comments'
        ]);

        // if there is no slug try and create one from the title
        if (empty($input['slug'])) {
            $input['slug'] = $input['title'];
        }

        // convert to ascii
        $input['slug'] = slug($input['slug']);

        // an array of items that we shouldn't encode - they're no XSS threat
        $dont_encode = ['description', 'markdown', 'css', 'js'];

        foreach ($input as $key => &$value) {
            if (in_array($key, $dont_encode)) {
                continue;
            }

            $value = eq($value);
        }

        $validator = new Validator($input);

        $validator->add('duplicate', function ($str) {
            return Post::where('slug', '=', $str)->count() == 0;
        });

        $validator->check('title')
                  ->is_max(3, __('posts.title_missing'));

        $validator->check('slug')
                  ->is_max(3, __('posts.slug_missing'))
                  ->is_duplicate(__('posts.slug_duplicate'))
                  ->not_regex('#^[0-9_-]+$#', __('posts.slug_invalid'));

        if ($errors = $validator->errors()) {
            Input::flash();

            // Notify::error($errors);

            // $id is undefined and will throw, so we use -1 instead
            //     because this post still isn't saved.
            return Response::json([
                //'id'   => $id,
                'id'     => -1,
                'errors' => array_flatten($errors, [])
            ]);
        }

        if (empty($input['created'])) {
            $input['created'] = Date::mysql('now');
        }

        $user = Auth::user();

        $input['author'] = $user->id;

        if (empty($input['comments'])) {
            $input['comments'] = 0;
        }

        if (empty($input['markdown'])) {
            $input['status'] = 'draft';
        }

        $input['html'] = parse($input['markdown']);
        $post          = Post::create($input);
        $id            = $post->id;

        Extend::process('post', $id);

        // Notify::success(__('posts.created'));

        if (Input::get('autosave') === 'true') {
            return Response::json([
                'id'           => $id,
                'notification' => __('posts.updated'),
            ]);
        } else {
            return Response::json([
                'id'           => $id,
                'notification' => __('posts.created'),
                'redirect'     => Uri::to('admin/posts/edit/' . $id)
            ]);
        }
    });

    /**
     * Preview post
     */
    Route::post('admin/posts/preview', function () {
        $markdown = Input::get('markdown');

        // apply markdown processing
        $output = Json::encode(['markdown' => parse($markdown)]);

        return Response::create($output, 200, ['content-type' => 'application/json']);
    });

    /**
     * Delete post
     */
    Route::get('admin/posts/delete/(:num)', function ($id) {
        Post::find($id)->delete();
        Comment::where('post', '=', $id)->delete();

        Query::table(Base::table('post_meta'))
             ->where('post', '=', $id)
             ->delete();

        Notify::success(__('posts.deleted'));

        return Response::redirect('admin/posts');
    });
});
