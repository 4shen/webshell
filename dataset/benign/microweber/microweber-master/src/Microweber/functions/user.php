<?php

function user_ip()
{
    if (!defined('MW_USER_IP')) {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            define('MW_USER_IP', $_SERVER['HTTP_CF_CONNECTING_IP']);
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            define('MW_USER_IP', $_SERVER['REMOTE_ADDR']);
        } else {
            define('MW_USER_IP', '127.0.0.1');
        }
    }

    return MW_USER_IP;
}


if (!defined('MW_USER_IP')) {
    user_ip();
}


function forgot_password_url()
{
    return mw()->user_manager->forgot_password_url();
}

function register_url()
{
    return mw()->user_manager->register_url();
}

function get_user_by_id($params = false)
{
    return mw()->user_manager->get_by_id($params);
}

if (!function_exists('mw_csrf_token')) {
    function mw_csrf_token($form_name = false)
    {
        return mw()->user_manager->csrf_token($form_name);
    }
}
function csrf_form($form_name = false)
{
    return mw()->user_manager->csrf_form($form_name);
}

function logout_url()
{
    return mw()->user_manager->logout_url();
}

function login_url()
{
    return mw()->user_manager->login_url();
}

function profile_url()
{
    return mw()->user_manager->profile_url();
}

function session_set($key, $val)
{
    return mw()->user_manager->session_set($key, $val);
}

function session_get($name)
{
    return mw()->user_manager->session_get($name);
}

function session_del($name)
{
    return mw()->user_manager->session_del($name);
}

function session_end()
{
    return mw()->user_manager->session_end();
}

function session_all()
{
    return mw()->user_manager->session_all();
}

function api_login($api_key = false)
{
    return mw()->user_manager->api_login($api_key);
}

api_expose('user_social_login');
function user_social_login($params)
{
    return mw()->user_manager->social_login($params);
}

api_expose('logout');

function logout()
{
    return mw()->user_manager->logout();
}

//api_expose('user_register');
api_expose('user_register');

function user_register($params)
{
    return mw()->user_manager->register($params);
}

api_expose_user('save_user');

/**
 * Allows you to save users in the database.
 *
 * By default it have security rules.
 *
 * If you are admin you can save any user in the system;
 *
 * However if you are regular user you must post param id with the current user id;
 *
 * @param  $params
 * @param  $params ['id'] = $user_id; // REQUIRED , you must set the user id.
 *                 For security reasons, to make new user please use user_register() function that requires captcha
 *                 or write your own save_user wrapper function that sets  mw_var('force_save_user',true);
 *                 and pass its params to save_user();
 * @param  $params ['is_active'] = 1; //default is 'n'
 *
 * @usage
 *
 * $upd = array();
 * $upd['id'] = 1;
 * $upd['email'] = $params['new_email'];
 * $upd['password'] = $params['passwordhash'];
 * mw_var('force_save_user', false|true); // if true you want to make new user or foce save ... skips id check and is admin check
 * mw_var('save_user_no_pass_hash', false|true); //if true skips pass hash function and saves password it as is in the request, please hash the password before that or ensure its hashed
 * $s = save_user($upd);
 *
 * @return bool|int
 */
function save_user($params)
{
    return mw()->user_manager->save($params);
}

api_expose_admin('delete_user');
function delete_user($data)
{
    return mw()->user_manager->delete($data);
}

api_expose('social_login_process');
function social_login_process()
{
    return mw()->user_manager->social_login_process();
}

api_expose('user_reset_password_from_link');
function user_reset_password_from_link($params)
{
    return mw()->user_manager->reset_password_from_link($params);
}

api_expose('user_send_forgot_password');
function user_send_forgot_password($params)
{
    return mw()->user_manager->send_forgot_password($params);
}

api_expose_admin('user_make_logged');
function user_make_logged($params)
{
    return mw()->user_manager->make_logged($params);
}

api_expose('user_login');
function user_login($params)
{
    return mw()->user_manager->login($params);
}

api_expose('is_logged');
function is_logged()
{

    $is = mw()->user_manager->is_logged();
    if (defined('MW_API_CALL')) {
        mw()->event_manager->trigger('mw.user.is_logged');
    }

    return $is;
}

function user_id()
{
    return mw()->user_manager->id();
}

function has_access($function_name)
{
    return mw()->user_manager->has_access($function_name);
}

function only_admin_access()
{
    return mw()->user_manager->admin_access();
}

function is_admin()
{
    return mw()->user_manager->is_admin();
}

function is_live_edit()
{
    $editmode_sess = mw()->user_manager->session_get('editmode');
    if ($editmode_sess == true and !defined('IN_EDIT')) {
        define('IN_EDIT', true);

        return true;
    }

    return $editmode_sess;
}

/**
 * @function user_name
 * gets the user's FULL name
 *
 * @param        $user_id the id of the user. If false it will use the curent user (you)
 * @param string $mode full|first|last|username
 *                        'full' //prints full name (first +last)
 *                        'first' //prints first name
 *                        'last' //prints last name
 *                        'username' //prints username
 *
 * @return string
 */
function user_name($user_id = false, $mode = 'full')
{
    return mw()->user_manager->name($user_id, $mode);
}


function user_email($user_id = false)
{
    return user_name($user_id, $mode = 'email');
}

function user_picture($user_id = false)
{
    return mw()->user_manager->picture($user_id);
}

/**
 * @function get_users
 *
 * @param array|bool|string $params array|string;
 * @params $params['username'] string username for user
 * @params $params['email'] string email for user
 *
 *
 * @usage    get_users('email=my_email');
 *
 * @return array of users;
 */
function get_users($params = false)
{
    return mw()->user_manager->get_all($params);
}

/**
 * get_user.
 *
 * get_user get the user info from the DB
 *
 * @category users
 *
 * @author   Microweber
 *
 * @link     http://microweber.com
 *
 * @param bool $id
 *
 *
 * @return array
 */
function get_user($id = false)
{
    return mw()->user_manager->get($id);
}


api_expose_admin('users/register_email_send_test', function () {
	
	try {
		mw()->option_manager->override('users', 'register_email_enabled', true);
		return mw()->user_manager->register_email_send();
	} catch (Exception $e) {
		echo "Error Message: <br />" . $e->getMessage();
	}
    
});

api_expose('users/register_email_send', function ($params = false) {
    $uid = null;
    if (isset($params['user_id']) and is_admin()) {
        $uid = intval($params['user_id']);
    }
    return mw()->user_manager->register_email_send($uid);
});

api_expose_admin('users/forgot_password_email_send_test', function () {
	
	echo '';
	
	/* try {
		mw()->option_manager->override('users', 'forgot_pass_email_enabled', true);
		return mw()->user_manager->send_forgot_password();
	} catch (Exception $e) {
		echo "Error Message: <br />" . $e->getMessage();
	} */
	
});

api_expose('users/search_authors', function ($params = false) {

    $return = array();

    $kw = false;
    if (isset($params['kw'])) {
        $kw = $params['kw'];
    }


    $all_users_search = array();
    $all_users_search['limit'] = 100;
    $all_users_search['fields'] = 'id,username,first_name,last_name,email,is_admin';
    if ($kw) {
        $all_users_search['keyword'] = $kw;
        $all_users_search['search_in_fields'] = 'id,username,first_name,last_name,email';
    }

    $all_users = get_users($all_users_search);
    if ($all_users) {
        foreach ($all_users as $user) {
            if (isset($user['id'])) {
                $user['display_name'] = user_name($user['id']);
                $user['picture'] = user_picture($user['id']);
                $return[] = $user;
            }
        }
    }
    return $return;
});


api_expose('users/verify_email_link', function ($params) {
    if (isset($params['key'])) {

        try {
            $decoded = mw()->format->decrypt($params['key']);
            if ($decoded) {
                $decoded = intval($decoded);
                $adminUser = \User::findOrFail($decoded);
                $adminUser->is_verified = 1;
                $adminUser->save();
                mw()->cache_manager->delete('users/global');
                mw()->cache_manager->delete('users/' . $decoded);
                $params['user_id'] = $decoded;
                mw()->event_manager->trigger('mw.user.verify_email_link', $params);

                return mw()->url_manager->redirect(site_url());
            }

        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
    }

});


api_expose_user('users/export_my_data', function ($params) {
    if (!is_logged()) {
        return array('error' => 'You must be logged');
    }
    $user_id = user_id();
    if (isset($params['user_id']) and $params['user_id'] and is_admin()) {
        $user_id = $params['user_id'];
    }


    $email = user_email($user_id);

    $sid = mw()->user_manager->session_id();
    $backup_manager = new \Microweber\Utils\Backup();

    $export_location = $backup_manager->get_bakup_location();


    $export_path = $export_location . 'user_data_exports' . DS . $sid . '.json';
    $export_path_zip = $export_location . 'user_data_exports' . DS . $sid . '.zip';
    $db_params = array();
    $db_params['created_by'] = $user_id;
    $db_params['require_table_to_have_any_of_columns'] = array('created_by');

    $export = $backup_manager->export_to_json_file('all', $db_params, $export_path);

    return response()->download($export_path)->deleteFileAfterSend(true);


});
