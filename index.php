<?php
error_reporting(E_ERROR);
date_default_timezone_set('UTC');

/* SYSTEM */
require_once('system/Constants.php');
require_once('system/ApiAuth.php');
require_once('system/StatusReturn.php');
require_once('system/MySQL.php');
require_once('system/BaseClasses.php');

/* CONTROLLERS */
require_once('system/Controllers/WebSPA.php');
require_once('system/Controllers/InitiateConnection.php');
require_once('system/Controllers/GetGeneralData.php');

/* MODELS */
require_once('system/Models/AuthUser.php');

ApiAuthRouterHook::add("404", function() {
    echo json_encode(StatusReturn::E404('404 Not Found!'));
});

ApiAuthRouterHook::add("404Web", function() {
    StatusReturn::WEB404();
});

ApiAuthRouter::serve(Array(
    '/'                   => Array('controller' => 'WebSPA', 'auth' => false),
    '/api/initiate/'      => Array('controller' => 'InitiateConnection', 'auth' => true, 'roles' => Array('Admin', 'User'), 'second-factor' => true),
    '/api/general/'       => Array('controller' => 'GetGeneralData', 'auth' => false),
));
