<?php
/**
 * PHP-REST-API-JS - PHP Restful API using custom MVC style structure
 * PHP Version 5.6.18
 * @package PHP-REST-API-JS
 * @author Marc Godard <godardm@gmail.com>
 * @copyright 2016 Marc Godard
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

error_reporting(E_ERROR);
date_default_timezone_set('UTC');

/* LIBRARIES */
require_once('system/Libraries/autoload.php');

/* SYSTEM */
require_once('system/Constants.php');
require_once('system/ApiAuth.php');
require_once('system/StatusReturn.php');
require_once('system/MySQL.php');
require_once('system/BaseClasses.php');

/* CONTROLLERS */
require_once('system/Controllers/WebSPA.php');
require_once('system/Controllers/InitiateConnection.php');
require_once('system/Controllers/ForgotPassword.php');
require_once('system/Controllers/SignUp.php');
require_once('system/Controllers/HelloWorld.php');
require_once('system/Controllers/CheckLogin.php');

/* MODELS */
require_once('system/Models/AuthUser.php');
require_once('system/Models/EmailTemplates.php');

/* DATA */
require_once('system/Data/AuthUserData.php');
require_once('system/Data/EmailTemplatesData.php');

ApiAuthRouterHook::add("404", function() {
    echo json_encode(StatusReturn::E404('404 Not Found!'));
});

ApiAuthRouterHook::add("404Web", function() {
    StatusReturn::WEB404();
});

ApiAuthRouter::serve(Array(
    '/'
            => Array('controller' => 'WebSPA', 'auth' => false),

    '/api/check-username/:alphaNumPlus/'
            => Array('controller' => 'SignUpUserName', 'auth' => false),

    '/api/check-email/:email/'
            => Array('controller' => 'SignUpEmail', 'auth' => false),

    '/api/sign-up/'
            => Array('controller' => 'SignUp', 'auth' => false),

    '/api/forgot-password/'
            => Array('controller' => 'ForgotPassword', 'auth' => false),

    '/api/initiate/'
            => Array('controller' => 'InitiateConnection', 'auth' => true, 'roles' => Array('Admin', 'User'), 'initialize' => true),

    '/api/check-login/'
            => Array('controller' => 'CheckLogin', 'auth' => true, 'roles' => Array('Admin', 'User')),

    '/api/hello-world/'
            => Array('controller' => 'HelloWorld', 'auth' => true, 'roles' => Array('Admin', 'User')),
));
