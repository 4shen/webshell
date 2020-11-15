<?php

/*

type: layout

name: Login admin

description: Admin login style

*/

?>
<?php $user = user_id(); ?>
<?php

$selected_lang = 'en';

if (isset($_COOKIE['lang'])) {
    $selected_lang = $_COOKIE['lang'];


}

$current_lang = current_lang();


?>

<div id="mw-login">
    <script>mw.require("session.js");</script>
    <script>

        mw.session.checkPauseExplicitly = true;

        $(document).ready(function () {
            mw.tools.dropdown();

            mw.session.checkPause = true;

            mw.$("#lang_selector").bind("change", function () {
                mw.cookie.set("lang", $(this).getDropdownValue());
            });

        });


    </script>
    <style type="text/css">
        body {
            background: #F4F4F4;
        }

        .mw-ui-col.main-bar-column {
            display: none;
        }

        .main-admin-row {
            max-width: none;
        }

    </style>
    <?php

    if (!isset(mw()->ui->admin_logo_login_link) or mw()->ui->admin_logo_login_link == false) {
        $link = site_url();

    } else {
        $link = mw()->ui->admin_logo_login_link;
    }

    ?>
    <a href="<?php print $link; ?>" target="_blank" id="login-logo"> <img src="<?php print mw()->ui->admin_logo_login(); ?>" alt="Logo"> <span class="mw-sign-version">v. <?php print MW_VERSION; ?></span> </a>
    <div class="mw-ui-box">
        <div class="mw-ui-box-content" id="admin_login">
            <?php if ($user != false): ?>
                <div>
                    <?php _e("Welcome"); ?>
                    <?php print user_name(); ?> </div>
                <a href="<?php print site_url() ?>">
                    <?php _e("Go to"); ?>
                    <?php print site_url() ?></a> <a href="<?php print api_link('logout') ?>">
                    <?php _e("Log Out"); ?>
                </a>
            <?php else: ?>


            <?php if (get_option('enable_user_microweber_registration', 'users') == 'y' and get_option('microweber_app_id', 'users') != false and get_option('microweber_app_secret', 'users') != false): ?>
                <?php

                /*
                // TBA


                   <div style="text-align: center; margin-bottom: 1em;">
                      <p>
                        <a href="<?php echo api_url(); ?>user_social_login?provider=microweber" class="mw-ui-btn mw-ui-btn-big mw-ui-btn-info">
                          Use <?php echo mw()->ui->brand_name; ?> Account
                        </a>
                      </p>
                      <div style="margin-top: 1em;"><i>- or -</i></div>
                    </div>

                */


                ?>
            <?php endif; ?>
            <?php event_trigger('mw.ui.admin.login.form.before'); ?>

            <form autocomplete="on" method="post" id="user_login_<?php print $params['id'] ?>" action="<?php print api_link('user_login') ?>">
                <div class="mw-ui-field-holder">
                    <input class="mw-ui-field mw-ui-field-big silver-field" autofocus="" tabindex="1" required name="username" type="text" placeholder="<?php _e("Username or Email"); ?>" <?php if (isset($input['username']) != false): ?> value="<?php print $input['username'] ?>"  <?php endif;  ?> />
                </div>
                <div class="mw-ui-field-holder">
                    <input class="mw-ui-field mw-ui-field-big silver-field" name="password" tabindex="2" required type="password" <?php if (isset($input['password']) != false): ?> value="<?php print $input['password'] ?>"  <?php endif; ?> placeholder="<?php _e("Password"); ?>"/>
                </div>


                <?php if (isset($login_captcha_enabled) and $login_captcha_enabled): ?>
                    <div class="mw-ui-field-holder">

                        <div class="mw-ui-row captcha-holder">
                            <div class="mw-ui-col">
                                <img onclick="mw.tools.refresh_image(this);" class="mw-captcha-img" id="captcha-<?php print $params['id']; ?>" src="<?php print api_link('captcha') ?>"/>
                            </div>

                            <div class="mw-ui-col">
                                <input name="captcha" type="text" required class="mw-ui-field mw-captcha-input" tabindex="3" placeholder="<?php _e("Security code"); ?>"/>
                            </div>

                        </div>
                    </div>
                <?php endif; ?>


                <div class="mw-ui-field-holder">
                    <ul class="mw-ui-inline-list pull-left">
                        <li><span>
              <?php _e("Language"); ?>
              </span></li>
                        <li>
                            <div data-value="" title="" class="mw-dropdown mw-dropdown-type-wysiwyg" id="lang_selector"> <span class="mw-dropdown-value">
                                    <?php if ($current_lang != 'en'): ?>
                                        <span class="mw-dropdown-val"><?php print strtoupper($current_lang); ?></span>
                                    <?php else: ?>
                                        <span class="mw-dropdown-val">EN</span>
                                    <?php endif; ?>
                </span>
                                <div class="mw-dropdown-content">
                                    <ul>
                                        <?php

                                        $langs = get_available_languages(); ?>
                                        <?php foreach ($langs as $lang): ?>
                                            <li value="<?php print $lang; ?>" <?php if ($selected_lang == $lang) { ?> class="active" <?php } ?>><a href="javascript:;"><?php print strtoupper($lang); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <input type="hidden" name="where_to" value="admin_content"/>
                    <input class="mw-ui-btn mw-ui-btn-big mw-ui-btn-info mw-ui-btn-outline pull-right" type="submit" tabindex="4" value="<?php _e("Login"); ?>"/>
                </div>
                <?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" value="<?php echo $_GET['redirect']; ?>" name="redirect">
                <?php endif; ?>
            </form>
            <?php event_trigger('mw.ui.admin.login.form.after'); ?>
        </div>
    </div>
    <div id="login_foot"><a href="<?php print site_url() ?>" class="pull-left"><span class="mw-icon-back"></span>
            <?php _e("Back to My WebSite"); ?>
        </a> <a href="#" onClick="mw.load_module('users/forgot_password', '#admin_login', false, {template:'admin'});" class="mw-ui-link pull-right">
            <?php _e("Forgot my password"); ?>
            ?</a></div>
<?php endif; ?>
</div>
