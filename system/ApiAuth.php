<?php

/*
 *  Router for the API
 */
class ApiAuthRouter {
    public static function serve($routes) {
        ApiAuthRouterHook::fire('before_request', compact('routes'));

        $request_method = strtolower($_SERVER['REQUEST_METHOD']);

        $path_info = '/';
        if (!empty($_SERVER['PATH_INFO'])) {
            $path_info = $_SERVER['PATH_INFO'];
        } else if (!empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['ORIG_PATH_INFO'] !== '/index.php') {
            $path_info = $_SERVER['ORIG_PATH_INFO'];
        } else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                $path_info = (strpos($_SERVER['REQUEST_URI'], '?') > 0) ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
            }
        }

        $discovered_handler_arr = Array();
        $regex_matches = array();

        if (isset($routes[$path_info])) {
            $discovered_handler_arr = $routes[$path_info];
        } else if ($routes) {
            $tokens = array(
                ':string'       => '([a-zA-Z]+)',
                ':number'       => '([0-9]+)',
                ':alphaNum'     => '([a-zA-Z0-9]+)',
                ':alphaNumPlus' => '([a-zA-Z0-9_-]+)',
                ':email'        => '([a-zA-Z0-9@._-]+)',
                ':key'          => '([a-zA-Z0-9]{40})',
                ':bool'         => '(true|false)',
                ':lang'         => '([a-zA-Z]{2})'
            );
            foreach ($routes as $pattern => $handler_name) {
                $pattern = strtr($pattern, $tokens);
                if (preg_match('#^/?' . $pattern . '/?$#', $path_info, $matches)) {
                    $discovered_handler_arr = $handler_name;
                    $regex_matches = $matches;
                    break;
                }
            }
        }

        $result = null;
        $handler_instance = null;

        if (!empty($discovered_handler_arr)) {
            $authPassed = (
                ($discovered_handler_arr['auth']
                    && ApiAuthCheck::checkAuth($discovered_handler_arr['roles'], (isset($discovered_handler_arr['initialize']) ? $discovered_handler_arr['initialize'] : false))
                ) || (
                    (isset($discovered_handler_arr['auth']) && !$discovered_handler_arr['auth']) || !isset($discovered_handler_arr['auth'])
                ));

            if (class_exists($discovered_handler_arr['controller'])) {
                $handler_instance = new $discovered_handler_arr['controller']($authPassed);
            } else if (is_callable($discovered_handler_arr['controller'])) {
                $handler_instance = $discovered_handler_arr['controller']($authPassed);
            }
        }

        if ($handler_instance) {

            unset($regex_matches[0]);

            if (self::is_xhr_request() && method_exists($handler_instance, $request_method . '_xhr')) {
                header('Content-type: application/json');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                $request_method .= '_xhr';
            }

            if (method_exists($handler_instance, $request_method)) {
                ApiAuthRouterHook::fire('before_handler', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
                $result = call_user_func_array(array($handler_instance, $request_method), $regex_matches);
                ApiAuthRouterHook::fire('after_handler', compact('routes', 'discovered_handler', 'request_method', 'regex_matches', 'result'));
            } else {
                if (self::is_xhr_request()) {
                    ApiAuthRouterHook::fire('404', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
                } else {
                    ApiAuthRouterHook::fire('404Web', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
                }
            }
        } else {
            if (self::is_xhr_request()) {
                ApiAuthRouterHook::fire('404', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
            } else {
                ApiAuthRouterHook::fire('404Web', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
            }
        }

        ApiAuthRouterHook::fire('after_request', compact('routes', 'discovered_handler', 'request_method', 'regex_matches', 'result'));
    }

    private static function is_xhr_request() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || (array_key_exists('X-Requested-With', getallheaders()));
    }
}

/*
 * Add extra hooks to teh router
 */
class ApiAuthRouterHook {
    private static $instance;

    private $hooks = array();

    private function __construct() {}
    private function __clone() {}

    public static function add($hook_name, $fn) {
        $instance = self::get_instance();
        $instance->hooks[$hook_name][] = $fn;
    }

    public static function fire($hook_name, $params = null) {
        $instance = self::get_instance();
        if (isset($instance->hooks[$hook_name])) {
            foreach ($instance->hooks[$hook_name] as $fn) {
                call_user_func_array($fn, array(&$params));
            }
        }
    }

    public static function get_instance() {
        if (empty(self::$instance)) {
            self::$instance = new ApiAuthRouterHook();
        }
        return self::$instance;
    }
}

/*
 * Authentication Check
 */
class ApiAuthCheck {
    public static function checkAuth($roles, $initialize = false) {
        $headers = getallheaders();

        if (!isset($headers['Auth-User']) || !isset($headers['Auth-Timestamp']) || !isset($headers['Auth-Signature'])) return false;
        if (!is_numeric($headers['Auth-Timestamp']) || $headers['Auth-Timestamp'] < strtotime("-" . _TIME_TO_LIVE_IN_MINUTES_ . " minute", time())) return false;

        $requestedURI = parse_url($_SERVER['REQUEST_URI']);
        if (_USE_HTTPS_ONLY_ && $requestedURI['scheme'] != 'https') return false;

        $userData = new AuthUser();
        if (!$userData->loadUser(strtolower($headers['Auth-User']), $initialize)) return false;

        $userSecret = null;

        if ($initialize) {
            $userSecret = $userData->getUserPassword();
            $salt = $userData->getSalt();
            $challenge = $userData->getChallengeKey();

            if (!array_key_exists('challenge', $_POST)) {
                if (hash_equals(hash_pbkdf2('sha512', $_POST['passwordHash'], $salt, 1000), $userSecret)) {
                    $userData->askClientChallenge();
                    return true;
                } else {
                    $userData->addFailedLogin();
                    return false;
                }
            } else if ($_POST['challenge'] != $challenge) {
                $userData->addFailedLogin();
                return false;
            } else if ($_POST['challenge'] == $challenge) {
                $userData->initiateConnection();
            }
        }
        else $userSecret = $userData->getUserSecret();

        $data = '';
        foreach ($_POST AS $key => $value) {
            if ($data != "") $data .= "&";
            $data .= $key . '=' . $value;
        }

        $signatureData = $_SERVER['REQUEST_METHOD'] . _DOMAIN_API_HOST_ . $_SERVER['REQUEST_URI'] . $data . $headers['Auth-Timestamp'];

        $newAuthSignature = hash_hmac('sha512', $signatureData, $userSecret, true);
        $newAuthSignature = base64_encode($newAuthSignature);

        if (hash_equals($newAuthSignature, $headers['Auth-Signature']) && !empty(array_intersect($userData->getUserRoles(), $roles))) {
            $userData->makeSuccessfulLogin($initialize);
            return true;
        }

        // initiate connection add secret, but the hash test needs to pass, so if it fails, remove secret and 2nd factor header.
        header_remove('Auth-Secret');
        header_remove('Auth-Second-Factor');
        $userData->addFailedLogin();
        return false;
    }
}
