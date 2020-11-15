<?php
/**
 * Class used to backup and restore the database or the userfiles directory.
 *
 * You can use it to create backup of the site. The backup will contain na sql export of the database
 * and also a zip file with userfiles directory.
 */

namespace Microweber\Utils;


use JsonStreamingParser\Listener\IdleListener;
use JsonStreamingParser\Listener\InMemoryListener;
use ZipArchive;
use Illuminate\Database\QueryException;

api_expose_admin('Microweber\Utils\Backup\delete');
api_expose_admin('Microweber\Utils\Backup\create');
api_expose_admin('Microweber\Utils\Backup\download');
api_expose_admin('Microweber\Utils\Backup\create_full');
api_expose_admin('Microweber\Utils\Backup\move_uploaded_file_to_backup');

api_expose_admin('Microweber\Utils\Backup\restore');
api_expose_admin('Microweber\Utils\Backup\cronjob');

if (defined('INI_SYSTEM_CHECK_DISABLED') == false) {
    define('INI_SYSTEM_CHECK_DISABLED', ini_get('disable_functions'));
}

class Backup
{
    public $backups_folder = false;
    public $backup_file = false;
    public $debug = false;
    public $app;
    /**
     * The backup class is used for making or restoring a backup.
     *
     * @category  mics
     */
    private $file_q_sep = '; /* MW_QUERY_SEPERATOR */';
    private $prefix_placeholder = '/* MW_PREFIX_PLACEHOLDER */';

    public function __construct($app = null)
    {
        if (!defined('USER_IP')) {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                define('USER_IP', $_SERVER['REMOTE_ADDR']);
            } else {
                define('USER_IP', '127.0.0.1');
            }
        }

        if (is_object($app)) {
            $this->app = $app;
        } else {
            $this->app = mw();
        }
    }

    public static function bgworker_restore($params)
    {
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', 1);
        }


        $api = new \Microweber\Utils\Backup();
        $api->exec_restore($params);
        return;


        $url = site_url();
        // header("Location: " . $url);
        // redirect the url to the 'busy importing' page
        ob_end_clean();
        //Erase the output buffer
        header('Connection: close');
        //Tell the browser that the connection's closed
        ignore_user_abort(true);
        //Ignore the user's abort (which we caused with the redirect).
        set_time_limit(0);
        //Extend time limit
        ob_start();
        //Start output buffering again
        header('Content-Length: 0');
        //Tell the browser we're serious... there's really nothing else to receive from this page.
        ob_end_flush();
        //Send the output buffer and turn output buffering off.
        flush();
        //Yes... flush again.
        //session_write_close();

        $back_log_action = 'Restoring backup';
        self::log_bg_action($back_log_action);
        $api = new \Microweber\Utils\Backup();
        $api->exec_restore($params);
    }

    public static function log_bg_action($back_log_action)
    {
        if ($back_log_action == false) {
            mw()->log_manager->delete('is_system=y&rel=backup&user_ip=' . USER_IP);
        } else {
            $check = mw()->log_manager->get('order_by=created_on desc&one=true&is_system=y&created_on=[mt]30 min ago&field=action&rel=backup&user_ip=' . USER_IP);

            if (is_array($check) and isset($check['id'])) {
                mw()->log_manager->save('is_system=y&field=action&rel=backup&value=' . $back_log_action . '&user_ip=' . USER_IP . '&id=' . $check['id']);
            } else {
                mw()->log_manager->save('is_system=y&field=action&rel=backup&value=' . $back_log_action . '&user_ip=' . USER_IP);
            }
        }
    }

    public static function bgworker()
    {
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', 1);
        }

        $url = site_url();
        //header("Location: " . $url);
        // redirect the url to the 'busy importing' page
        ob_end_clean();
        //Erase the output buffer
        header('Connection: close');
        //Tell the browser that the connection's closed
        ignore_user_abort(true);
        //Ignore the user's abort (which we caused with the redirect).
        set_time_limit(0);
        //Extend time limit
        ob_start();
        //Start output buffering again
        header('Content-Length: 0');
        //Tell the browser we're serious... there's really nothing else to receive from this page.
        ob_end_flush();
        //Send the output buffer and turn output buffering off.
        flush();
        //Yes... flush again.
        //session_write_close();

        //$back_log_action = "Creating full backup";
        //self::log_bg_action($back_log_action);

        if (!defined('MW_BACKUP_BG_WORKER_STARTED')) {
            define('MW_BACKUP_BG_WORKER_STARTED', 1);
            $backup_api = new \Microweber\Utils\Backup();
            $backup_api->exec_create_full();
            unset($backup_api);
        } else {
        }

        //  exit();
    }

    public function exec_create_full()
    {


        if (!defined('MW_BACKUP_STARTED')) {
            define('MW_BACKUP_STARTED', 1);
        } else {
            return false;
        }

        $start = microtime_float();
        if (defined('MW_CRON_EXEC')) {
        } else {
            only_admin_access();
        }

        @ob_end_clean();

        ignore_user_abort(true);
        $back_log_action = 'Preparing to zip';
        $this->log_action($back_log_action);
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $here = $this->get_bakup_location();
        $filename = $here . 'full_backup_' . date('Y-M-d-His') . '_' . uniqid() . '' . '.zip';

        $userfiles_folder = userfiles_path();

        $locations = array();
        $locations[] = userfiles_path();
        //$locations[] = $filename2;
        $fileTime = date('D, d M Y H:i:s T');

        $db_file = $this->create();

        $zip = new \Microweber\Utils\Zip($filename);
        $zip->setZipFile($filename);
        $zip->setComment("Microweber backup of the userfiles folder and db.
                \n The Microweber version at the time of backup was {MW_VERSION}
                \nCreated on " . date('l jS \of F Y h:i:s A'));
        if (isset($db_file['filename'])) {
            $filename2 = $here . $db_file['filename'];
            if (is_file($filename2)) {
                $back_log_action = 'Adding sql restore to zip';
                $this->log_action($back_log_action);
                $zip->addLargeFile($filename2, 'mw_sql_restore.sql', filectime($filename2), 'SQL Restore file');
                //  $zip->addFile(file_get_contents($filename2), 'mw_sql_restore.sql', filectime($filename2));
            }
        }

        $this->log_action(false);

        $back_log_action = 'Adding files to zip';
        $this->log_action($back_log_action);

        $zip->addDirectoryContent(userfiles_path(), '', true);
        $back_log_action = 'Adding userfiles to zip';
        $this->log_action($back_log_action);

        // $zip = $zip->finalize();
        $filename_to_return = $filename;
        $end = microtime_float();
        $end = round($end - $start, 3);

        $back_log_action = "Backup was created for $end sec!";
        $this->log_action($back_log_action);

        sleep(5);
        $back_log_action = 'reload';
        $this->log_action($back_log_action);

        sleep(5);
        $this->log_action(false);

        return array('success' => "Backup was created for $end sec! $filename_to_return", 'filename' => $filename_to_return, 'runtime' => $end);
    }

    public function cronjob_exec($params = false)
    {
        echo 'backup cronjob';
    }

    public function restore($params)
    {
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', 1);
        }
        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        } elseif (isset($_GET['filename'])) {
            $id = $params['filename'];
        } elseif (isset($_GET['file'])) {
            $id = $params['file'];
        }

        if ($id == null) {
            return array('error' => 'You have not provided a backup to restore.');
            die();
        }


        //ob_start();
        $api = new \Microweber\Utils\Backup();
        $this->app->cache_manager->clear();
        $rest = $api->exec_restore($params);



        $this->app->cache_manager->clear();

        //  ob_end_clean();

        return array('success' => 'Backup was restored!');

        return $rest;
    }

    public function exec_restore($params = false)
    {
        ignore_user_abort(true);
        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'memory_limit')) {
            ini_set('memory_limit', '2512M');
            ini_set('memory_limit', "-1");
            ini_set("max_execution_time", "-1");
        }


        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'set_time_limit')) {
            set_time_limit(0);
        }

        $preview_restore = false;

        $loc = $this->backup_file;

        // Get the provided arg
        if (isset($params['id'])) {
            $id = $params['id'];
        } elseif (isset($_GET['filename'])) {
            $id = $params['filename'];
        } elseif (isset($_GET['file'])) {
            $id = $params['file'];
        } elseif ($loc != false) {
            $id = $loc;
        }


        if (isset($params['preview_restore']) and $params['preview_restore']) {
            $preview_restore = $params['preview_restore'];
        }


        if ($id == null) {
            return array('error' => 'You have not provided a backup to restore.');
        }

        $here = $this->get_bakup_location();

        $filename = $here . $id;
        $ext = get_file_extension($filename);
        $ext_error = false;

        $sql_file = false;
        $json_file = false;

        if (!is_file($filename)) {
            return array('error' => 'You have not provided a existing backup to restore.');
            die();
        }

        $temp_dir_restore = false;
        switch ($ext) {
            case 'zip' :
                $back_log_action = 'Unzipping userfiles';
                $this->log_action($back_log_action);

                $exract_folder = md5($filename . filemtime($filename));
                $unzip = new \Microweber\Utils\Unzip();
                $target_dir = mw_cache_path() . 'backup_restore' . DS . $exract_folder . DS;
                if (!is_dir($target_dir)) {
                    mkdir_recursive($target_dir);
                    $result = $unzip->extract($filename, $target_dir, $preserve_filepath = true);
                }


                $temp_dir_restore = $target_dir;
//                $sql_restore = $target_dir . 'mw_sql_restore.sql';
//                if (is_file($sql_restore)) {
//                    $sql_file = $sql_restore;
//                }

                $json_restore = $target_dir . 'mw_content.json';


                if (is_file($json_restore)) {

                    $json_file = $json_restore;
                }


                break;

//            case 'sql' :
//                $sql_file = $filename;
//                break;

            case 'json' :
                $json_file = $filename;
                break;

            default :
                $ext_error = true;
                break;
        }

        if ($ext_error == true) {
            return array('error' => 'Invalid file extension. The restore file must be .sql, .json or .zip');
            die();
        }


        if ($sql_file != false) {
            $back_log_action = 'Restoring database';
            $this->log_action($back_log_action);

            $filename = $sql_file;

            $sqlErrorText = '';
            $sqlErrorCode = 0;
            $sqlStmt = '';

            $sqlFile = file_get_contents($filename);
            $sqlArray = explode($this->file_q_sep, $sqlFile);
            if (!isset($sqlArray[1])) {
                $sqlArray = explode("\n", $sqlFile);
            }
            // Process the sql file by statements
            $engine = mw()->database_manager->get_sql_engine();
            foreach ($sqlArray as $stmt) {
                $stmt = str_replace('/* MW_TABLE_SEP */', ' ', $stmt);
                $stmt = str_ireplace($this->prefix_placeholder, get_table_prefix(), $stmt);

                $stmt = str_replace("\xEF\xBB\xBF", '', $stmt);
//                $stmt = str_replace("\x0D", '', $stmt);
//                $stmt = str_replace("\x09", '', $stmt);
////
//                $stmt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $stmt);

                if ($engine == 'sqlite') {
                    $stmt = str_replace("\'", "''", $stmt);
                }
                if ($engine == 'pgsql') {
                    $stmt = str_replace('REPLACE INTO', 'INSERT INTO', $stmt);
                    $stmt = str_replace("'',", "NULL,", $stmt);
                }

                if ($this->debug) {
                    d($stmt);
                }

                if (strlen(trim($stmt)) > 3) {
                    try {
                        mw()->database_manager->q($stmt, true);
                        // mw()->database_manager->q($stmt);
                    } catch (QueryException $e) {
                        echo 'Caught exception: ' . $e->getMessage() . "\n";
                        $sqlErrorCode = 1;
                    } catch (\Illuminate\Database\QueryException $e) {
                        echo 'Caught exception: ' . $e->getMessage() . "\n";
                        $sqlErrorCode = 1;
                    } catch (\PDOException $e) {
                        echo 'Caught exception: ' . $e->getMessage() . "\n";
                        $sqlErrorCode = 1;
                    } catch (Exception $e) {
                        echo 'Caught exception: ' . $e->getMessage() . "\n";
                        $sqlErrorCode = 1;
                    }
                }
            }

            // Print message (error or success)
            if ($sqlErrorCode == 0) {
                $back_log_action = 'Database restored!';
                $this->log_action($back_log_action);

                echo "Database restored!\n";
                echo 'Backup used: ' . $filename . "\n";
            } else {
                echo "An error occurred while restoring backup!<br><br>\n";
                echo "Error code: $sqlErrorCode<br>\n";
                echo "Error text: $sqlErrorText<br>\n";
                echo "Statement:<br/> $sqlStmt<br>";
            }

            $back_log_action = 'Database restored!';
            $this->log_action($back_log_action);

            echo "Files restored successfully!<br>\n";
            echo 'Backup used: ' . $filename . "<br>\n";
            if ($temp_dir_restore != false) {
                //      @unlink($filename);
            }
        } elseif ($json_file) {

            $back_log_action = 'Restoring content from JSON file';
            $this->log_action($back_log_action);
            $json_restore = $this->_import_content_from_json_file($json_file, $params);



            if($preview_restore){
                return $json_restore;
            }

            $back_log_action = 'Content restored';
            $this->log_action($back_log_action);
            // @unlink($json_file);
        }


        if (userfiles_path()) {
            if (!is_dir(userfiles_path())) {
                mkdir_recursive(userfiles_path());
            }
        }
        if (media_base_path()) {
            if (!is_dir(media_base_path())) {
                mkdir_recursive(media_base_path());
            }
        }

        if ($temp_dir_restore != false and is_dir($temp_dir_restore)) {
            echo "Media restored!<br>\n";
            $srcDir = $temp_dir_restore;
            $destDir = userfiles_path();
            $copy = $this->copyr($srcDir, $destDir);
        }
        mw()->template->clear_cached_custom_css();

        if (function_exists('mw_post_update')) {
            mw_post_update();
        }

        $back_log_action = 'Cleaning up cache';
        $this->log_action($back_log_action);
        mw()->cache_manager->clear();

        $this->log_action(false);
    }

    public function copyr($source, $dest)
    {
        if (is_file($source) and !is_dir($dest)) {
            $dest = normalize_path($dest, false);
            $source = normalize_path($source, false);
            $dest_dir = dirname($dest);
            if (!is_dir($dest_dir)) {
                mkdir_recursive($dest_dir);
            }
            if (!is_writable($dest)) {
                //return;
            }

            return @copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir_recursive($dest);
        }

        if (is_dir($source)) {
            $dir = dir($source);
            if ($dir != false) {
                while (false !== $entry = $dir->read()) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    if ($dest !== "$source/$entry" and $dest !== "$source" . DS . "$entry") {
                        $this->copyr("$source/$entry", "$dest/$entry");
                    }
                }
            }

            $dir->close();
        }

        return true;
    }

    public function cronjob($params = false)
    {
        if (!defined('INI_SYSTEM_CHECK_DISABLED')) {
            define('INI_SYSTEM_CHECK_DISABLED', ini_get('disable_functions'));
        }
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', true);
        }

        if (!defined('IS_ADMIN')) {
            define('IS_ADMIN', true);
        }

        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'ini_set')) {
            ini_set('memory_limit', '512M');
        }
        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'set_time_limit')) {
            set_time_limit(600);
        }

        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'ignore_user_abort')) {
            ignore_user_abort();
        }

        $type = 'full';

        if (isset($params['type'])) {
            $type = trim($params['type']);
        }

        $cache_id = 'backup_queue';
        $cache_id_loc = 'backup_progress';
        $cache_state_id = 'backup_zip_state';

        $cache_content = $this->app->cache_manager->get($cache_id, 'backup');
        $cache_lock = $this->app->cache_manager->get($cache_id_loc, 'backup');
        $cache_state = $this->app->cache_manager->get($cache_state_id, 'backup', 30);

        $time = time();
        $here = $this->get_bakup_location();
        if ($cache_state == 'opened') {
            return $cache_content;
        }

        if ($cache_content == false or empty($cache_content)) {
            $this->app->cache_manager->save(false, $cache_id_loc, 'backup');
            $this->app->cache_manager->save(false, $cache_id, 'backup');

            return true;
        } else {
            $bak_fn = 'backup_' . date('Y-M-d-His') . '_' . uniqid() . '';
            $filename = $here . $bak_fn . '.zip';
            if ($cache_lock == false or !is_array($cache_lock)) {
                $cache_lock = array();
                $cache_lock['processed'] = 0;
                $cache_lock['files_count'] = count($cache_content);
                $cache_lock['time'] = $time;
                $cache_lock['filename'] = $filename;
                $this->app->cache_manager->save($cache_lock, $cache_id_loc, 'backup');
            } else {
                if (isset($cache_lock['filename'])) {
                    $filename = $cache_lock['filename'];
                }
            }

            if (isset($cache_lock['time'])) {
                $time_sec = intval($cache_lock['time']);
                if (($time - 3) < $time_sec) {
                    return $cache_content;
                }
            }

            $backup_actions = $cache_content;

            global $mw_backup_zip_obj;
            if (!is_object($mw_backup_zip_obj)) {
                $mw_backup_zip_obj = new  ZipArchive();
            }
            $zip_opened = false;
            if (is_array($backup_actions)) {
                $i = 0;

                $this->app->cache_manager->save($filename, $cache_id_loc, 'backup');

                if (!$mw_backup_zip_obj->open($filename, ZIPARCHIVE::CREATE)) {
                    $zip_opened = 1;

                    return false;
                }
                $this->app->cache_manager->save('opened', $cache_state_id, 'backup');

                $limit_per_turn = 20;

                foreach ($backup_actions as $key => $item) {
                    $flie_ext = strtolower(get_file_extension($item));

                    if ($flie_ext == 'php' or $flie_ext == 'css' or $flie_ext == 'js') {
                        $limit_per_turn = 150;
                    }

                    if ($i > $limit_per_turn or $cache_lock == $item) {
                        if (isset($mw_backup_zip_obj) and is_object($mw_backup_zip_obj)) {
                            if ($zip_opened == 1) {
                                $mw_backup_zip_obj->close();
                            }
                        }
                        $this->app->cache_manager->save('closed', $cache_state_id, 'backup');
                    } else {
                        ++$cache_lock['processed'];
                        $cache_lock['time'] = time();
                        $cache_lock['filename'] = $filename;

                        $precent = ($cache_lock['processed'] / $cache_lock['files_count']) * 100;
                        $precent = round($precent);
                        $cache_lock['percent'] = $precent;

                        $back_log_action = "Progress  {$precent}% ({$cache_lock['processed']}/{$cache_lock['files_count']}) <br><small>" . basename($item) . '</small>';
                        $this->log_action($back_log_action);

                        $this->app->cache_manager->save($cache_lock, $cache_id_loc, 'backup');

                        if ($item == 'make_db_backup') {
                            $limit_per_turn = 1;
                            $mw_backup_zip_obj->close();
                            $this->app->cache_manager->save('closed', $cache_state_id, 'backup');
                            $db_file = $this->create($bak_fn . '.sql');
                            if (!$mw_backup_zip_obj->open($filename, ZIPARCHIVE::CREATE)) {
                                $zip_opened = 1;

                                return false;
                            }
                            $this->app->cache_manager->save('opened', $cache_state_id, 'backup');
                            if (isset($db_file['filename'])) {
                                $filename2 = $here . $db_file['filename'];
                                if (is_file($filename2)) {
                                    $back_log_action = 'Adding sql restore to zip';
                                    $this->log_action($back_log_action);
                                    $mw_backup_zip_obj->addFile($filename2, 'mw_sql_restore.sql');
                                }
                            }
                        } else if ($item == 'make_json_backup') {
                            $back_log_action = 'Exporting content to JSON';
                            $json_file = $this->export_to_json_file();
                            if (is_file($json_file)) {
                                $back_log_action = 'Adding json restore to zip';
                                $mw_backup_zip_obj->addFile($json_file, 'mw_content.json');

                            }
                        } else {
                            $relative_loc = str_replace(userfiles_path(), '', $item);

                            $new_backup_actions = array();

                            if (is_dir($item)) {
                                $mw_backup_zip_obj->addEmptyDir($relative_loc);
                            } elseif (is_file($item)) {
                                $mw_backup_zip_obj->addFile($item, $relative_loc);
                            }
                        }

                        unset($backup_actions[$key]);

                        if (isset($new_backup_actions) and !empty($new_backup_actions)) {
                            $backup_actions = array_merge($backup_actions, $new_backup_actions);
                            array_unique($backup_actions);
                            $this->app->cache_manager->save($backup_actions, $cache_id, 'backup');
                        } else {
                            $this->app->cache_manager->save($backup_actions, $cache_id, 'backup');
                        }

                        if (empty($backup_actions)) {
                            $this->app->cache_manager->save(false, $cache_id, 'backup');
                        }
                    }
                    ++$i;
                }

                $mw_backup_zip_obj->close();
                if (isset($json_file) and is_file($json_file)) {
                    @unlink($json_file);
                }

                $this->app->cache_manager->save('closed', $cache_state_id, 'backup');
            }
        }

        if (empty($backup_actions)) {
            $this->app->cache_manager->save(false, $cache_id, 'backup');
        }

        return $cache_content;
    }

    public function create($filename = false)
    {
        if (is_array($filename)) {
            $filename = false;
        }

        ignore_user_abort(true);
        $start = microtime_float();
        if (defined('MW_CRON_EXEC')) {
        } else {
            only_admin_access();
        }

        $table = '*';
        if ($table == '*') {
            $extname = 'all';
        } else {
            $extname = str_replace(',', '_', $table);
            $extname = str_replace(' ', '_', $extname);
        }
        $here = $this->get_bakup_location();
        if (!is_dir($here)) {
            if (!mkdir_recursive($here)) {
                $back_log_action = 'Error the dir is not writable: ' . $here;
                $this->log_action($back_log_action);
            }
        }

        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $index1 = $here . 'index.php';
        if ($filename == false) {
            $engine = mw()->database_manager->get_sql_engine();
            $mwv = MW_VERSION;
            $mwv = str_replace('.', '', $mwv);
            //$filename_to_return = 'database_' . date('YMdHis') . '_' . uniqid() . '_' . $mwv . '_' . $engine . '.sql';
            $filename_to_return = 'database_' . date('YMdHis') . '_' . uniqid() . '_' . $mwv . '_' . $engine . '.json';
        } else {
            $filename_to_return = $filename;
        }

        $filess = $here . $filename_to_return;
        if (is_file($filess)) {
            return false;
        }
        touch($filess);
        touch($index1);
        $sql_bak_file = $filess;
        $hta = $here . '.htaccess';

        if (!is_file($hta)) {
            touch($hta);
            file_put_contents($hta, 'Deny from all');
        }


        $this->log_action(false);
        $back_log_action = 'Saving to file ' . basename($filess);
        $this->export_to_json_file($tables = 'all', $db_get_params = false, $json_file_export_path = $filess);


        $this->log_action($back_log_action);
        $end = microtime_float();
        $end = round($end - $start, 3);
        $this->log_action(false);

        return array('success' => "Backup was created for $end sec! $filename_to_return", 'filename' => $filename_to_return, 'runtime' => $end, 'url' => dir2url($filess));


        /* OLD BACKUP BELOW */
        /* DEPRECATED */
        /* DEPRECATED */
        /* DEPRECATED */
        /* DEPRECATED */
        /* DEPRECATED */
        /* DEPRECATED */

        $head = '/* Microweber database backup exported on: ' . date('l jS \of F Y h:i:s A') . " */ \n";
        $head .= '/* get_table_prefix(): ' . get_table_prefix() . " */ \n\n\n";
        file_put_contents($sql_bak_file, $head);
        $return = '';
        $tables = '*';
        // Get all of the tables
        if ($tables == '*') {
            $tables = array();

            $result = mw()->database_manager->get_tables_list(true);
            if (!empty($result)) {
                foreach ($result as $item) {
                    $tables[] = $item;
                }
            }


        } else {
            if (is_array($tables)) {
                $tables = explode(',', $tables);
            }
        }

        $back_log_action = 'Starting database backup';
        $this->log_action($back_log_action);

        // Cycle through each provided table
        foreach ($tables as $table) {
            $is_cms_table = false;

            if (get_table_prefix() == '') {
                $is_cms_table = 1;
            } elseif (stristr($table, get_table_prefix())) {
                $is_cms_table = 1;
            }
            if (stristr($table, 'sessions')) {
                $is_cms_table = false;
            }

            if ($table != false and $is_cms_table) {
                $back_log_action = "Backing up database table $table";
                $this->log_action($back_log_action);
                $qs = 'SELECT * FROM ' . $table;
                $result = mw()->database_manager->query($qs, $cache_id = false, $cache_group = false, $only_query = false);
                $num_fields = 0;
                if (isset($result[0]) and is_array($result[0])) {
                    $num_fields = count($result[0]);
                }

                $table_without_prefix = $this->prefix_placeholder . str_ireplace(get_table_prefix(), '', $table);


                $ddl = mw()->database_manager->get_table_ddl($table);
                $ddl = str_ireplace('CREATE TABLE ', 'CREATE TABLE IF NOT EXISTS ', $ddl);

                //dd($ddl);

                $create_table_without_prefix = str_ireplace(get_table_prefix(), $this->prefix_placeholder, $ddl);

                $return = "\n\n" . $create_table_without_prefix . $this->file_q_sep . "\n\n\n";
                $this->append_string_to_file($sql_bak_file, $return);

                $this->log_action(false);
                if (!empty($result)) {
                    $table_accos = str_replace(get_table_prefix(), '', $table);
                    $columns = $this->app->database_manager->get_fields($table_accos);
                    //   d(get_table_prefix());

                    foreach ($result as $row) {
                        $row = array_values($row);
                        $columns = array_values($columns);

                        $columns_q = false;
                        $columns_temp = array();
                        foreach ($columns as $column) {
                            $columns_temp[] = $column;
                        }
                        if (!empty($columns_temp)) {
                            $columns_q = implode(',', $columns_temp);
                            $columns_q = '(' . $columns_q . ')';
                        }

                        $return = 'REPLACE INTO ' . $table_without_prefix . ' ' . $columns_q . ' VALUES(';
                        for ($j = 0; $j < $num_fields; ++$j) {
                            // $row[$j] = str_replace("'", '&rsquo;', $row[$j]);
                            if (isset($row[$j])) {
                                $return .= "'" . $row[$j] . "'";
                            } else {
                                $return .= "''";
                            }
                            if ($j < ($num_fields - 1)) {
                                $return .= ',';
                            }
                        }
                        $return .= ')' . $this->file_q_sep . "\n\n\n";
                        $this->append_string_to_file($sql_bak_file, $return);
                    }
                }
                $return = "\n\n\n";
                $this->append_string_to_file($sql_bak_file, $return);
            }
        }
        $this->log_action(false);
        $back_log_action = 'Saving to file ' . basename($filess);
        $this->log_action($back_log_action);
        $end = microtime_float();
        $end = round($end - $start, 3);
        $this->log_action(false);

        return array('success' => "Backup was created for $end sec! $filename_to_return", 'filename' => $filename_to_return, 'runtime' => $end, 'url' => dir2url($filess));
    }

    public function _import_content_from_json_file($file, $params = array())
    {
         include_once __DIR__.DS.'lib/json-machine/vendor/autoload.php';




        $preview_items_for_restore = array();
        $preview_restore = false;


        if (isset($params['preview_restore']) and $params['preview_restore']) {
            $preview_restore = $params['preview_restore'];
        }


        if (is_file($file)) {
            ini_set('memory_limit', '-1');
            set_time_limit(0);
          //  $cont = file_get_contents($file);

           // $restore = json_decode($cont, true);
            $restore = \JsonMachine\JsonMachine::fromFile($file);



            if ($restore) {
                foreach ($restore as $table => $data) {

                        $max_items_counter=0;

                        foreach ($data as $item) {

                            $max_items_counter++;

                            if($max_items_counter > 3000){
                                break;
                            }


                            array_walk_recursive($item, function (&$el) {
                                if (is_string($el)) {
                                    $el = utf8_decode($el);
                                    $el = str_replace('Â ', ' ', $el);
                                    $el = str_replace("Â ", ' ', $el);
                                }
                            });



                            if ($preview_restore) {
                                if (!isset($preview_items_for_restore[$table])) {
                                    $preview_items_for_restore[$table] = array();
                                }
                                $preview_items_for_restore[$table][] = $item;
                            } else {

                                $table_exists = mw()->database_manager->table_exists($table);
                                if ($table_exists) {


                                    $item['allow_html'] = true;
                                    $item['allow_scripts'] = true;

                                    db_save($table, $item);
                                }
                            }

                    }
                }
            }

        }


        if($preview_restore){
            return $preview_items_for_restore;
        }
    }

    public function export_to_json_file($tables = 'all', $db_get_params = false, $json_file_export_path = false)
    {


        $skip_tables = array(
            "modules", "elements", "users", "log", "notifications",
            "content_revisions_history", 'content_fields_drafts', "stats_users_online", "system_licenses", "users_oauth",
            "sessions",
            "stats_users_online",
            "stats_browser_agents",  "stats_referrers_paths",  "stats_referrers_domains",  "stats_referrers",  "stats_visits_log",  "stats_urls",  "stats_geopip",

            "jobs", "failed_jobs"
        );


        ini_set('memory_limit', '1512M');
        set_time_limit(0);

        $export_location = $this->get_bakup_location();
        if (!is_dir($export_location)) {
            mkdir_recursive($export_location);
        }

        if ($json_file_export_path) {
            $export_path = $json_file_export_path;
        } else {
            $export_filename = 'content_export_' . date("Y-m-d-his") . '.json';
            $export_path = $export_location . $export_filename;
        }


        $all_tables = array();

        $all_tables_raw = mw()->database_manager->get_tables_list();
        $local_prefix = mw()->database_manager->get_prefix();

        foreach ($all_tables_raw as $k => $v) {
            if ($local_prefix) {
                $v = str_replace_first($local_prefix, '', $v);
                $all_tables[] = $v;
            } else {
                $all_tables[] = $v;
            }

        }
        $exported_tables_data = array();
        if ($all_tables) {
            foreach ($all_tables as $table) {
                if (!in_array($table, $skip_tables)) {
                    $table_exists = mw()->database_manager->table_exists($table);
                    if ($table_exists) {
                        $db_params = array();
                        $db_params['no_limit'] = 1;
                        $db_params['do_not_replace_site_url'] = true;
                        if (is_array($db_get_params) and !empty($db_get_params)) {
                            $db_params = array_merge($db_params, $db_get_params);
                        }
                        $table_conent = db_get($table, $db_params);
                        if ($table_conent) {
                            $exported_tables_data[$table] = $table_conent;
                        }
                    }

                }
            }
        }

        return $this->write_array_to_json_file($exported_tables_data, $export_path);

    }

    public function write_array_to_json_file($array, $json_file_path)
    {
        $exported_tables_data = $array;
        $export_path = $json_file_path;

        $save = $this->__json_encode($exported_tables_data);

        $export_path_d = dirname($export_path);
        if (!is_dir($export_path_d)) {
            mkdir_recursive($export_path_d);
        }


        if (file_put_contents($export_path, $save)) {
            return $export_path;

        } else {
            false;
        }

    }


    private function __json_encode($exported_tables_data)
    {

        array_walk_recursive($exported_tables_data, function (&$item) {
            if (is_string($item)) {
                $item = utf8_encode($item);
                $item = str_replace('Â ', ' ', $item);
                $item = str_replace("Â ", ' ', $item);
            }
        });
        $exported_tables_data = json_encode($exported_tables_data);
        return $exported_tables_data;

    }


    public function append_string_to_file($file_path, $string_to_append)
    {
        file_put_contents($file_path, $string_to_append, FILE_APPEND);
    }

    public function get_bakup_location()
    {
        if (defined('MW_API_CALL')) {
            if (defined('MW_CRON_EXEC')) {
            } elseif (!is_admin()) {
                return 'must be admin';
            }
        }
        $loc = $this->backups_folder;

        if ($loc != false) {
            return $loc;
        }
        $here = userfiles_path() . 'backup' . DS;

        if (!is_dir($here)) {
            mkdir_recursive($here);
            $hta = $here . '.htaccess';
            if (!is_file($hta)) {
                touch($hta);
                file_put_contents($hta, 'Deny from all');
            }
        }
        $environment = mw()->environment();
        $here = userfiles_path() . 'backup' . DS . $environment . DS;

        $here2 = mw()->option_manager->get('backup_location', 'admin/backup');
        if ($here2 != false and is_string($here2) and trim($here2) != 'default' and trim($here2) != '') {
            $here2 = normalize_path($here2, true);

            if (!is_dir($here2)) {
                mkdir_recursive($here2);
            }

            if (is_dir($here2)) {
                $here = $here2;
            }
        }
        if (!is_dir($here)) {
            mkdir_recursive($here);
        }
        $loc = $here;
        $this->backups_folder = $loc;

        return $here;
    }

    public function create_full()
    {
        if (!defined('INI_SYSTEM_CHECK_DISABLED')) {
            define('INI_SYSTEM_CHECK_DISABLED', ini_get('disable_functions'));
        }

        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'ini_set')) {
            ini_set('memory_limit', '512M');
        }
        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'set_time_limit')) {
            set_time_limit(600);
        }

        $backup_actions = array();
        //  $backup_actions[] = 'make_db_backup';
        $backup_actions[] = 'make_json_backup';

        $userfiles_folder = userfiles_path();
        $media_folder = media_base_path();

        $all_images = $this->app->media_manager->get_all('nolimit=1');

        if (!empty($all_images)) {
            foreach ($all_images as $image) {
                if (isset($image['filename']) and $image['filename'] != false) {
                    $fn = url2dir($image['filename']);
                    if (is_file($fn)) {
                        $backup_actions[] = $fn;
                    }
                }
            }
        }

        $media_up_path = media_uploads_path();
        $media_up_path = normalize_path($media_up_path, 1);

        if (is_dir($media_up_path)) {
            $more_folders = \rglob($media_up_path . '*', GLOB_NOSORT);
            if (!empty($more_folders)) {
                $backup_actions = array_merge($more_folders, $backup_actions);
            }
        }


        $host = (parse_url(site_url()));

        $host_dir = false;
        if (isset($host['host'])) {
            $host_dir = $host['host'];
            $host_dir = str_ireplace('www.', '', $host_dir);
            $host_dir = str_ireplace('.', '-', $host_dir);
        }

        $userfiles_folder_uploaded = $media_folder . DS . $host_dir . DS . 'uploaded' . DS;
        $userfiles_folder_uploaded = $media_folder . DS . $host_dir . DS;
        $userfiles_folder_uploaded = \normalize_path($userfiles_folder_uploaded);
        $folders = \rglob($userfiles_folder_uploaded . '*', GLOB_NOSORT);

        if (!is_array($folders)) {
            $folders = array();
        }
        $cust_css_dir = $userfiles_folder . 'css' . DS;
        if (is_dir($cust_css_dir)) {
            $more_folders = \rglob($cust_css_dir . '*', GLOB_NOSORT);
            if (!empty($more_folders)) {
                $folders = array_merge($folders, $more_folders);
            }
        }



        $cust_css_dir = $media_folder . DS . 'content' . DS;
        if (is_dir($cust_css_dir)) {
            $more_folders = \rglob($cust_css_dir . '*', GLOB_NOSORT);
            if (!empty($more_folders)) {
                $folders = array_merge($folders, $more_folders);
            }
        }

        if (!empty($folders)) {
            $text_files = array();
            foreach ($folders as $fold) {
                if (!stristr($fold, 'backup')) {
                    if (stristr($fold, '.php') or stristr($fold, '.js') or stristr($fold, '.css')) {
                        $text_files[] = $fold;
                    } else {
                        $backup_actions[] = $fold;
                    }
                }
            }
            if (!empty($text_files)) {
                $backup_actions = array_merge($text_files, $backup_actions);
            }
        }


        $cache_id = 'backup_queue';
        $cache_id_loc = 'backup_progress';
        $cache_state_id = 'backup_zip_state';
        $this->app->cache_manager->save($backup_actions, $cache_id, 'backup');
        $this->app->cache_manager->save(false, $cache_id_loc, 'backup');
        $this->app->cache_manager->save(false, $cache_state_id, 'backup');
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', 1);
        }

        return;
    }

    public function log_action($back_log_action)
    {
        if (defined('MW_IS_INSTALLED') and MW_IS_INSTALLED == true) {
            if ($back_log_action == false) {
                $this->app->log_manager->delete('is_system=y&rel=backup&user_ip=' . USER_IP);
            } else {
                $check = $this->app->log_manager->get('order_by=created_on desc&one=true&is_system=y&created_on=[mt]30 min ago&field=action&rel=backup&user_ip=' . USER_IP);

                if (is_array($check) and isset($check['id'])) {
                    $this->app->log_manager->save('is_system=y&field=action&rel=backup&value=' . $back_log_action . '&user_ip=' . USER_IP . '&id=' . $check['id']);
                } else {
                    $this->app->log_manager->save('is_system=y&field=action&rel=backup&value=' . $back_log_action . '&user_ip=' . USER_IP);
                }
            }
        }
    }

    public function move_uploaded_file_to_backup($params)
    {
        only_admin_access();
        if (!isset($params['src'])) {
            return array('error' => 'You have not provided src to the file.');
        }

        $check = url2dir(trim($params['src']));
        $here = $this->get_bakup_location();
        if (is_file($check)) {
            $fn = basename($check);
            if (copy($check, $here . $fn)) {
                @unlink($check);

                return array('success' => "$fn was uploaded!");
            } else {
                return array('error' => 'Error moving uploaded file!');
            }
        } else {
            return array('error' => 'Uploaded file is not found!');
        }
    }

    public function get()
    {
        if (!is_admin()) {
            error('must be admin');
        }

        $here = $this->get_bakup_location();

        $files = glob("$here{*.sql,*.zip,*.json}", GLOB_BRACE);
        if (is_array($files)) {
            usort($files, function ($a, $b) {
                return filemtime($a) < filemtime($b);
            });
        }
        $backups = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                if (stripos($file, '.sql', 1) or stripos($file, '.zip', 1) or stripos($file, '.json', 1)) {
                    $mtime = filemtime($file);
                    $date = date('F d Y', $mtime);
                    $time = date('H:i:s', $mtime);
                    $bak = array();
                    $bak['filename'] = basename($file);
                    $bak['date'] = $date;
                    $bak['time'] = str_replace('_', ':', $time);
                    $bak['size'] = filesize($file);
                    $backups[] = $bak;
                }
            }
        }

        return $backups;
    }

    public function delete($params)
    {
        if (!is_admin()) {
            error('must be admin');
        }

        $id = $params['id'];

        if ($id == null) {
            return array('error' => 'You have not provided filename to be deleted.');
        }

        $here = $this->get_bakup_location();
        $filename = $here . $id;

        $id = str_replace('..', '', $id);
        $filename = str_replace('..', '', $filename);

        $ext = get_file_extension(strtolower($filename));
        if ($ext != 'zip' and $ext != 'sql' and $ext != 'json') {
            return array('error' => "You are now allowed to delete {$ext} files.");
        }
        if (is_file($filename)) {
            unlink($filename);

            return array('success' => "$id was deleted!");
        } else {
            $filename = $here . $id . '.sql';
            if (is_file($filename)) {
                unlink($filename);

                return array('success' => "$id was deleted!");
            }
        }
    }

    public function download($params)
    {
        if (!is_admin()) {
            error('must be admin');
        }

        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'memory_limit')) {
            ini_set('memory_limit', '512M');
        }

        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'set_time_limit')) {
            set_time_limit(0);
        }

        if (isset($params['id'])) {
            $id = $params['id'];
        } elseif (isset($_GET['filename'])) {
            $id = $params['filename'];
        } elseif (isset($_GET['file'])) {
            $id = $params['file'];
        }
        $id = str_replace('..', '', $id);

        if ($id == null) {
            return array('error' => 'You have not provided filename to download.');
        }

        $here = $this->get_bakup_location();

        $filename = $here . $id;
        $filename = str_replace('..', '', $filename);
        if (!is_file($filename)) {
            return array('error' => 'You have not provided a existing filename to download.');
        }
        $dl = new \Microweber\Utils\Files();

        return $dl->download_to_browser($filename);
    }
}

$mw_backup_zip_obj = false;
