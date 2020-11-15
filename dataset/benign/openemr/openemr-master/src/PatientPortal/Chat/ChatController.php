<?php

/**
 *  Chat Class ChatController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PatientPortal\Chat;

use OpenEMR\PatientPortal\Chat\ChatDispatcher;
use OpenEMR\PatientPortal\Chat\ChatModel;

class ChatController extends ChatDispatcher
{
    protected $_model;

    public function __construct()
    {
        $this->setModel('OpenEMR\PatientPortal\Chat\ChatModel');
        parent::__construct();
    }

    public function indexAction()
    {
    }

    public function authusersAction()
    {
        return $this->getModel()->getAuthUsers(true);
    }

    public function listAction()
    {
        $this->setHeader(array('Content-Type' => 'application/json'));
        $messages = $this->getModel()->getMessages();
        foreach ($messages as &$message) {
            $message['me'] = C_USER === $message['sender_id']; // $this->getServer('REMOTE_ADDR') === $message['ip'];
        }

        return json_encode($messages);
    }

    public function saveAction()
    {
        $username = $this->getPost('username');
        $message = $this->getPost('message');
        $ip = $this->getServer('REMOTE_ADDR');
        $this->setCookie('username', $username, 9999 * 9999);
        $recipid = $this->getPost('recip_id');

        if (IS_PORTAL) {
            $senderid = IS_PORTAL;
        } else {
            $senderid = IS_DASHBOARD;
        }

        $result = array('success' => false);
        if ($username && $message) {
            $cleanUsername = preg_replace('/^' . ADMIN_USERNAME_PREFIX . '/', '', $username);
            $result = array(
                'success' => $this->getModel()->addMessage($cleanUsername, $message, $ip, $senderid, $recipid)
            );
        }

        if ($this->_isAdmin($username)) {
            $this->_parseAdminCommand($message);
        }

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($result);
    }

    private function _isAdmin($username)
    {
        return IS_DASHBOARD ? true : false;
        //return preg_match('/^'.ADMIN_USERNAME_PREFIX.'/', $username);
    }

    private function _parseAdminCommand($message)
    {
        if (strpos($message, '/clear') !== false) {
            $this->getModel()->removeMessages();
            return true;
        }

        if (strpos($message, '/online') !== false) {
            $online = $this->getModel()->getOnline(false);
            $ipArr = array();
            foreach ($online as $item) {
                $ipArr[] = $item->ip;
            }

            $message = 'Online: ' . implode(", ", $ipArr);
            $this->getModel()->addMessage('Admin Command', $message, '0.0.0.0');
            return true;
        }
    }

    private function _getMyUniqueHash()
    {
        $unique = $this->getServer('REMOTE_ADDR');
        $unique .= $this->getServer('HTTP_USER_AGENT');
        $unique .= $this->getServer('HTTP_ACCEPT_LANGUAGE');
        $unique .= C_USER;
        return md5($unique);
    }

    public function pingAction()
    {
        $ip = $this->getServer('REMOTE_ADDR');
        $hash = $this->_getMyUniqueHash();
        $user = $this->getRequest('username', 'No Username');
        if ($user == 'currentol') {
            $onlines = $this->getModel()->getOnline(false);
            $this->setHeader(array('Content-Type' => 'application/json'));
            return json_encode($onlines);
        }

        if (IS_PORTAL) {
            $userid = IS_PORTAL;
        } else {
            $userid = IS_DASHBOARD;
        }

        $this->getModel()->updateOnline($hash, $ip, $user, $userid);
        $this->getModel()->clearOffline();
        // $this->getModel()->removeOldMessages(); // @todo For soft delete when I decide. DO NOT REMOVE

        $onlines = $this->getModel()->getOnline();

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($onlines);
    }
}
