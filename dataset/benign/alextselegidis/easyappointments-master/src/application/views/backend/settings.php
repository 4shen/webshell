<script src="<?= asset_url('assets/js/backend_settings_system.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_settings_user.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_settings.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-jeditable/jquery.jeditable.min.js') ?>"></script>
<script>
    var GlobalVariables = {
        'csrfToken'     : <?= json_encode($this->security->get_csrf_hash()) ?>,
        'baseUrl'       : <?= json_encode($base_url) ?>,
        'dateFormat'    : <?= json_encode($date_format) ?>,
        'timeFormat'    : <?= json_encode($time_format) ?>,
        'userSlug'      : <?= json_encode($role_slug) ?>,
        'settings'      : {
            'system'    : <?= json_encode($system_settings) ?>,
            'user'      : <?= json_encode($user_settings) ?>
        },
        'user'          : {
            'id'        : <?= $user_id ?>,
            'email'     : <?= json_encode($user_email) ?>,
            'role_slug' : <?= json_encode($role_slug) ?>,
            'privileges': <?= json_encode($privileges) ?>
        }
    };

    $(document).ready(function() {
        BackendSettings.initialize(true);
    });
</script>

<div id="settings-page" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li role="presentation" class="active">
                <a href="#general" aria-controls="general" role="tab" data-toggle="tab"><?= lang('general') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li role="presentation">
                <a href="#business-logic" aria-controls="business-logic" role="tab" data-toggle="tab"><?= lang('business_logic') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li role="presentation">
                <a href="#legal-contents" aria-controls="legal-contents" role="tab" data-toggle="tab"><?= lang('legal_contents') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_USER_SETTINGS]['view'] == TRUE): ?>
            <li role="presentation">
                <a href="#current-user" aria-controls="current-user" role="tab" data-toggle="tab"><?= lang('current_user') ?></a>
            </li>
        <?php endif ?>
        <li role="presentation">
            <a href="#about-app" aria-controls="about-app" role="tab" data-toggle="tab"><?= lang('about_app') ?></a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- GENERAL TAB -->

        <?php $hidden = ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE) ? '' : 'hidden' ?>
        <div role="tabpanel" class="tab-pane active <?= $hidden ?>" id="general">
            <form>
                <fieldset>
                    <legend>
                        <?= lang('general_settings') ?>
                        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['edit'] == TRUE): ?>
                            <button type="button" class="save-settings btn btn-primary btn-xs"
                                    title="<?= lang('save') ?>">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                <?= lang('save') ?>
                            </button>
                        <?php endif ?>
                    </legend>

                    <div class="wrapper row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="company-name"><?= lang('company_name') ?> *</label>
                                <input id="company-name" data-field="company_name" class="required form-control">
                                <span class="help-block">
                                    <?= lang('company_name_hint') ?>
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="company-email"><?= lang('company_email') ?> *</label>
                                <input id="company-email" data-field="company_email" class="required form-control">
                                <span class="help-block">
                                    <?= lang('company_email_hint') ?>
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="company-link"><?= lang('company_link') ?> *</label>
                                <input id="company-link" data-field="company_link" class="required form-control">
                                <span class="help-block">
                                    <?= lang('company_link_hint') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="google-analytics-code">
                                    Google Analytics ID</label>
                                <input id="google-analytics-code" placeholder="UA-XXXXXXXX-X"
                                       data-field="google_analytics_code" class="form-control">
                                <span class="help-block">
                                    <?= lang('google_analytics_code_hint') ?>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="date-format">
                                    <?= lang('date_format') ?>
                                </label>
                                <select class="form-control" id="date-format" data-field="date_format">
                                    <option value="DMY">DMY</option>
                                    <option value="MDY">MDY</option>
                                    <option value="YMD">YMD</option>
                                </select>
                                <span class="help-block">
                                    <?= lang('date_format_hint') ?>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="time-format">
                                    <?= lang('time_format') ?>
                                </label>
                                <select class="form-control" id="time-format" data-field="time_format">
                                    <option value="<?= TIME_FORMAT_REGULAR ?>">H:MM AM/PM</option>
                                    <option value="<?= TIME_FORMAT_MILITARY ?>">HH:MM</option>
                                </select>
                                <span class="help-block">
                                    <?= lang('time_format_hint') ?>
                                </span>
                            </div>
                            <div class="form-group">
                                <label><?= lang('customer_notifications') ?></label>
                                <br>
                                <button type="button" id="customer-notifications" class="btn btn-default" data-toggle="button" aria-pressed="false">
                                    <span class="glyphicon glyphicon-envelope"></span>
                                    <?= lang('receive_notifications') ?>
                                </button>
                                <span class="help-block">
                                    <?= lang('customer_notifications_hint') ?>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="require-captcha">
                                    CAPTCHA
                                </label>
                                <br>
                                <button type="button" id="require-captcha" class="btn btn-default" data-toggle="button" aria-pressed="false">
                                    <span class="glyphicon glyphicon-lock"></span>
                                    <?= lang('require_captcha') ?>
                                </button>
                                <span class="help-block">
                                    <?= lang('require_captcha_hint') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <!-- BUSINESS LOGIC TAB -->

        <?php $hidden = ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE) ? '' : 'hidden' ?>
        <div role="tabpanel" class="tab-pane <?= $hidden ?>" id="business-logic">
            <form>
                <fieldset>
                    <legend>
                        <?= lang('business_logic') ?>
                        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['edit'] == TRUE): ?>
                            <button type="button" class="save-settings btn btn-primary btn-xs"
                                    title="<?= lang('save') ?>">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                <?= lang('save') ?>
                            </button>
                        <?php endif ?>
                    </legend>

                    <div class="row">
                        <div class="col-xs-12 col-sm-7 working-plan-wrapper">
                            <h4><?= lang('working_plan') ?></h4>
                            <span class="help-block">
                                <?= lang('edit_working_plan_hint') ?>
                            </span>

                            <table class="working-plan table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= lang('day') ?></th>
                                        <th><?= lang('start') ?></th>
                                        <th><?= lang('end') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="sunday">
                                                    <?= lang('sunday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="sunday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="sunday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="monday">
                                                    <?= lang('monday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="monday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="monday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="tuesday">
                                                    <?= lang('tuesday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="tuesday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="tuesday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="wednesday">
                                                    <?= lang('wednesday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="wednesday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="wednesday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="thursday">
                                                    <?= lang('thursday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="thursday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="thursday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="friday">
                                                    <?= lang('friday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="friday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="friday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="saturday">
                                                    <?= lang('saturday') ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td><input id="saturday-start" class="work-start form-control input-sm"></td>
                                        <td><input id="saturday-end" class="work-end form-control input-sm"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <br>

                            <h4><?= lang('book_advance_timeout') ?></h4>
                            <div class="form-group">
                                <label for="book-advance-timeout" class="control-label"><?= lang('timeout_minutes') ?></label>
                                <input id="book-advance-timeout" data-field="book_advance_timeout" class="form-control" type="number" min="15">
                                <p class="help-block">
                                    <?= lang('book_advance_timeout_hint') ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-5 breaks-wrapper">
                            <h4><?= lang('breaks') ?></h4>

                            <span class="help-block">
                                <?= lang('edit_breaks_hint') ?>
                            </span>

                            <div>
                                <button type="button" class="add-break btn btn-primary">
                                    <span class="glyphicon glyphicon-white glyphicon glyphicon-plus"></span>
                                    <?= lang('add_break');?>
                                </button>
                            </div>

                            <br>

                            <table class="breaks table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= lang('day') ?></th>
                                        <th><?= lang('start') ?></th>
                                        <th><?= lang('end') ?></th>
                                        <th><?= lang('actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody><!-- Dynamic Content --></tbody>
                            </table>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <!-- LEGAL CONTENTS TAB -->

        <?php $hidden = ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE) ? '' : 'hidden' ?>
        <div role="tabpanel" class="tab-pane <?= $hidden ?>" id="legal-contents">
            <form>
                <fieldset>
                    <legend>
                        <?= lang('legal_contents') ?>
                        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['edit'] == TRUE): ?>
                            <button type="button" class="save-settings btn btn-primary btn-xs"
                                    title="<?= lang('save') ?>">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                <?= lang('save') ?>
                            </button>
                        <?php endif ?>
                    </legend>

                    <div class="row">
                        <div class="col-xs-12 col-sm-11 col-md-10 col-lg-9">
                            <h4><?= lang('cookie_notice') ?></h4>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="display-cookie-notice">
                                        <?= lang('display_cookie_notice') ?>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= lang('cookie_notice_content') ?></label>
                                <textarea id="cookie-notice-content" cols="30" rows="10" class="form-group"></textarea>
                            </div>

                            <br>

                            <h4><?= lang('terms_and_conditions') ?></h4>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="display-terms-and-conditions">
                                        <?= lang('display_terms_and_conditions') ?>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= lang('terms_and_conditions_content') ?></label>
                                <textarea id="terms-and-conditions-content" cols="30" rows="10" class="form-group"></textarea>
                            </div>

                            <h4><?= lang('privacy_policy') ?></h4>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="display-privacy-policy">
                                        <?= lang('display_privacy_policy') ?>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= lang('privacy_policy_content') ?></label>
                                <textarea id="privacy-policy-content" cols="30" rows="10" class="form-group"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <!-- CURRENT USER TAB -->

        <?php $hidden = ($privileges[PRIV_USER_SETTINGS]['view'] == TRUE) ? '' : 'hidden' ?>
        <div role="tabpanel" class="tab-pane <?= $hidden ?>" id="current-user">
            <form>
                <div class="row">
                    <fieldset class="col-xs-12 col-sm-6 personal-info-wrapper">
                        <legend>
                            <?= lang('personal_information') ?>
                            <?php if ($privileges[PRIV_USER_SETTINGS]['edit'] == TRUE): ?>
                                <button type="button" class="save-settings btn btn-primary btn-xs"
                                        title="<?= lang('save') ?>">
                                    <span class="glyphicon glyphicon-floppy-disk"></span>
                                    <?= lang('save') ?>
                                </button>
                            <?php endif ?>
                        </legend>

                        <input type="hidden" id="user-id">

                        <div class="form-group">
                            <label for="first-name"><?= lang('first_name') ?> *</label>
                            <input id="first-name" class="form-control required">
                        </div>

                        <div class="form-group">
                            <label for="last-name"><?= lang('last_name') ?> *</label>
                            <input id="last-name" class="form-control required">
                        </div>

                        <div class="form-group">
                            <label for="email"><?= lang('email') ?> *</label>
                            <input id="email" class="form-control required">
                        </div>

                        <div class="form-group">
                            <label for="phone-number"><?= lang('phone_number') ?> *</label>
                            <input id="phone-number" class="form-control required">
                        </div>

                        <div class="form-group">
                            <label for="mobile-number"><?= lang('mobile_number') ?></label>
                            <input id="mobile-number" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="address"><?= lang('address') ?></label>
                            <input id="address" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="city"><?= lang('city') ?></label>
                            <input id="city" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="state"><?= lang('state') ?></label>
                            <input id="state" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="zip-code"><?= lang('zip_code') ?></label>
                            <input id="zip-code" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="notes"><?= lang('notes') ?></label>
                            <textarea id="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </fieldset>

                    <fieldset class="col-xs-12 col-sm-6 miscellaneous-wrapper">
                    <legend><?= lang('system_login') ?></legend>

                    <div class="form-group">
                        <label for="username"><?= lang('username') ?> *</label>
                        <input id="username" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label for="password"><?= lang('password') ?></label>
                        <input type="password" id="password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="retype-password"><?= lang('retype_password') ?></label>
                        <input type="password" id="retype-password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="calendar-view"><?= lang('calendar') ?> *</label>
                        <select id="calendar-view" class="form-control required">
                            <option value="default">Default</option>
                            <option value="table">Table</option>
                        </select>
                    </div>

                    <button type="button" id="user-notifications" class="btn btn-default" data-toggle="button">
                        <span class="glyphicon glyphicon-envelope"></span>
                        <?= lang('receive_notifications') ?>
                    </button>
                </fieldset>
                </div>
            </form>
        </div>

        <!-- ABOUT TAB -->

        <div role="tabpanel" class="tab-pane" id="about-app">
            <h3>Easy!Appointments</h3>

            <p>
                <?= lang('about_app_info') ?>
            </p>

            <div class="current-version well">
                <?= lang('current_version') ?>
                <?= $this->config->item('version') ?>
                <?php if ($this->config->item('release_label')): ?>
                    - <?= $this->config->item('release_label') ?>
                <?php endif ?>
            </div>

            <h3><?= lang('support') ?></h3>
            <p>
                <?= lang('about_app_support') ?>

                <br><br>

                <a href="http://easyappointments.org">
                    <?= lang('official_website') ?>
                </a>
                |
                <a href="https://groups.google.com/forum/#!forum/easy-appointments">
                    <?= lang('support_group') ?>
                </a>
                |
                <a href="https://github.com/alextselegidis/easyappointments/issues">
                    <?= lang('project_issues') ?>
                </a>
                |
                <a href="http://easyappointments.wordpress.com">
                    E!A Blog
                </a>
                |
                <a href="https://www.facebook.com/easyappointments.org">
                    Facebook
                </a>
                |
                <a href="https://plus.google.com/+EasyappointmentsOrg">
                    Google+
                </a>
                |
                <a href="https://twitter.com/EasyAppts">
                    Twitter
                </a>
                |
                <a href="https://plus.google.com/communities/105333709485142846840">
                    <?= lang('google_plus_community') ?>
                </a>
            </p>

            <h3><?= lang('license') ?></h3>

            <p>
                <?= lang('about_app_license') ?>
                <a href="http://www.gnu.org/copyleft/gpl.html">http://www.gnu.org/copyleft/gpl.html</a>
            </p>
        </div>

    </div>
</div>
