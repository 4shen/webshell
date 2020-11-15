<?php
/**
 * Copyright 2010 Cyrille Mahieux
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations
 * under the License.
 *
 * ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°>
 *
 * Executing commands
 *
 * @author elijaa@free.fr
 * @since 06/04/2010
 */
# Require
require_once 'Library/Bootstrap.php';

# Initializing requests & response
$request = (isset($_REQUEST['request_command'])) ? $_REQUEST['request_command'] : null;

# Starting
ob_start();

# Display by request rype
switch ($request) {
    # Memcache::get command
    case 'get' :
        # Ask for get on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server get command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->get($server['hostname'], $server['port'], $_REQUEST['request_key']));
            }
        }        # Ask for get on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server get command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->get($server['hostname'], $server['port'], $_REQUEST['request_key']));
        }         # Ask for get on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server get command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->get($server['hostname'], $server['port'], $_REQUEST['request_key']));
                }
            }
        }
        break;

    # Memcache::set command
    case 'set' :
        # Ask for set on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server get command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->set($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_data'], $_REQUEST['request_duration']));
            }
        }        # Ask for set on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server set command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->set($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_data'], $_REQUEST['request_duration']));
        }         # Ask for set on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server set command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->set($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_data'], $_REQUEST['request_duration']));
                }
            }
        }
        break;

    # Memcache::delete command
    case 'delete' :
        # Ask for delete on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server get command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->delete($server['hostname'], $server['port'], $_REQUEST['request_key']));
            }
        }        # Ask for delete on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server delete command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->delete($server['hostname'], $server['port'], $_REQUEST['request_key']));
        }         # Ask for delete on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server delete command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->delete($server['hostname'], $server['port'], $_REQUEST['request_key']));
                }
            }
        }
        break;

    # Memcache::increment command
    case 'increment' :
        # Checking value
        if (! isset($_REQUEST['request_value']) || ! is_numeric($_REQUEST['request_value'])) {
            $_REQUEST['request_value'] = 1;
        }

        # Ask for increment on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server increment command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->increment($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
            }
        }        # Ask for increment on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server increment command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->increment($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
        }         # Ask for increment on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server increment command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->increment($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
                }
            }
        }
        break;

    # Memcache::decrement command
    case 'decrement' :
        # Checking value
        if (! isset($_REQUEST['request_value']) || ! is_numeric($_REQUEST['request_value'])) {
            $_REQUEST['request_value'] = 1;
        }

        # Ask for decrement on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server decrement command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->decrement($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
            }
        }        # Ask for decrement on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server decrement command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->decrement($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
        }         # Ask for decrement on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server decrement command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->decrement($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_value']));
                }
            }
        }
        break;

    # Memcache::flush_all command
    case 'flush_all' :
        # Checking delay
        if (! isset($_REQUEST['request_delay']) || ! is_numeric($_REQUEST['request_delay'])) {
            $_REQUEST['request_delay'] = 0;
        }

        # Ask for flush_all on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server get command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->flush_all($server['hostname'], $server['port'], $_REQUEST['request_delay']));
            }
        }        # Ask for flush_all on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server flush_all command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->flush_all($server['hostname'], $server['port'], $_REQUEST['request_delay']));
        }         # Ask for flush_all on all servers
        else {
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server flush_all command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api($_REQUEST['request_api'])->flush_all($server['hostname'], $server['port'], $_REQUEST['request_delay']));
                }
            }
        }
        break;

    # Memcache::search command
    case 'search' :
        # Ask for search on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server get command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->search($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_level'], $_REQUEST['request_more']));
            }
        }        # Ask for search on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server search command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->search($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_level'], $_REQUEST['request_more']));
        }         # Ask for search on all servers
        else {
            # Looking into each cluster
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server search command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->search($server['hostname'], $server['port'], $_REQUEST['request_key'], $_REQUEST['request_level'], $_REQUEST['request_more']));
                }
            }
        }
        break;

    # Memcache::telnet command
    case 'telnet' :
        # Ask for a telnet command on a cluster
        if (isset($_REQUEST['request_server']) && ($cluster = $_ini->cluster($_REQUEST['request_server']))) {
            foreach ($cluster as $server) {
                # Dumping server telnet command response
                echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->telnet($server['hostname'], $server['port'], $_REQUEST['request_telnet']));
            }
        }        # Ask for a telnet command on one server
        elseif (isset($_REQUEST['request_server']) && ($server = $_ini->server($_REQUEST['request_server']))) {
            # Dumping server telnet command response
            echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->telnet($server['hostname'], $server['port'], $_REQUEST['request_telnet']));
        }         # Ask for a telnet command on all servers
        else {
            # Looking into each cluster
            foreach ($_ini->get('servers') as $cluster => $servers) {
                # Asking for each server stats
                foreach ($servers as $server) {
                    # Dumping server telnet command response
                    echo Library_HTML_Components::serverResponse($server['hostname'], $server['port'], Library_Command_Factory::api('Server')->telnet($server['hostname'], $server['port'], $_REQUEST['request_telnet']));
                }
            }
        }
        break;
    # Default : No command
    default :
        # Showing header
        include 'View/Header.phtml';

        # Showing formulary
        include 'View/Commands/Commands.phtml';

        # Showing footer
        include 'View/Footer.phtml';
        break;
}

ob_end_flush();