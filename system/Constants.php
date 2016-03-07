<?php

// todo: change in production
define('_ALLOW_CORS_', true);
define('_IN_DEVELOPMENT_', true);
define('_FULL_DEBUG_', false);

/*
 * 	Business Variables
 */
define('_COMPANY_NAME_', 'Company Name');
define('_DEFAULT_LANGUAGE_', 'en');

/*
 * 	General Variables
 */
define('_DOMAIN_API_HOST_', 'http://edc2.auto-cms.com');
define('_MANDATORY_EMAIL_CONFIRMATION_', true); // one of these two should be true
define('_MANDATORY_PHONE_CONFIRMATION_', false);
define('_USE_HTTPS_ONLY_', false);
define('_TWO_FACTOR_TYPE_NONE_', 0);
define('_TWO_FACTOR_TYPE_EMAIL_', 1);
define('_TWO_FACTOR_TYPE_SMS_', 2);
define('_TIME_TO_LIVE_IN_MINUTES_', 20);
define('_SESSION_EXPIRE_SECONDS_', 3600);
define('_SECOND_FACTOR_EXPIRE_SECONDS_', 900);
define('_USER_LOGIN_FAILED_COUNT_', 3);   // how many times does a user login fail before we lock them out
define('_USER_LOGIN_LOCKOUT_TIME_', 600); // how long we lock them out
define('_CHARS_FOR_SECOND_FACTOR_KEYS_', '5s3kzx2n7hmqd4w169tlgjpycbrfv8'); // 'bcdfghjklmnpqrstvwxyz123456789' randomized

define('_LOGIN_FAILED_COUNT_BEFORE_NOTIFICATION_', 3);
define('_LOGIN_FAILED_TIME_BEFORE_RESET_COUNT_SECONDS_', 3600);

define('_PIN_LOWEST_NUMBER_OF_CHARS_', 4);
define('_PIN_HIGH_RANGE_NUMBER_OF_CHARS_', 6);
define('_PIN_SIGN_UP_PLUS_CHARS_', 6); // this number is added on the top two to make a larger base range
define('_PIN_FORGOT_PASSWORD_PLUS_CHARS_', 6); // this number is added on the top two to make a larger base range

define('_PASSWORD_SALT_IV_SIZE_', 32); // changing this can impact client side code

/*
 * 	MySQL Access
 */
define('_MYSQL_DB_NAME_', '');
define('_MYSQL_USER_NAME_', '');
define('_MYSQL_PASSWORD_', '');

/*
 * 	Email Variables
 */
define('_EMAIL_HOSTS_', '');
define('_EMAIL_ADDRESS_', '');
define('_EMAIL_PASSWORD_', '');
define('_EMAIL_TEMPLATES_LOGO_', __DIR__ . '/../assets/img/logo.png');
