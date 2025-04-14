<?php

require_once ROOTPATH . '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv	=	Dotenv::createImmutable(ROOTPATH);
$dotenv->load();

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . '../vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_LOW instead.
 */
define('EVENT_PRIORITY_LOW', 200);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_NORMAL instead.
 */
define('EVENT_PRIORITY_NORMAL', 100);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_HIGH instead.
 */
define('EVENT_PRIORITY_HIGH', 10);
$url			=	!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "-";
$domain			=	explode(".", $url);
$subdomain		=	$domain[0];
$productionURL	=	$subdomain == "wa" ? true : false;

$arrHour   =	array(
    array("ID"=>"00", "VALUE"=>"00"),
    array("ID"=>"01", "VALUE"=>"01"),
    array("ID"=>"02", "VALUE"=>"02"),
    array("ID"=>"03", "VALUE"=>"03"),
    array("ID"=>"04", "VALUE"=>"04"),
    array("ID"=>"05", "VALUE"=>"05"),
    array("ID"=>"06", "VALUE"=>"06"),
    array("ID"=>"07", "VALUE"=>"07"),
    array("ID"=>"08", "VALUE"=>"08"),
    array("ID"=>"09", "VALUE"=>"09"),
    array("ID"=>"10", "VALUE"=>"10"),
    array("ID"=>"11", "VALUE"=>"11"),
    array("ID"=>"12", "VALUE"=>"12"),
    array("ID"=>"13", "VALUE"=>"13"),
    array("ID"=>"14", "VALUE"=>"14"),
    array("ID"=>"15", "VALUE"=>"15"),
    array("ID"=>"16", "VALUE"=>"16"),
    array("ID"=>"17", "VALUE"=>"17"),
    array("ID"=>"18", "VALUE"=>"18"),
    array("ID"=>"19", "VALUE"=>"19"),
    array("ID"=>"20", "VALUE"=>"20"),
    array("ID"=>"21", "VALUE"=>"21"),
    array("ID"=>"22", "VALUE"=>"22"),
    array("ID"=>"23", "VALUE"=>"23")
);
$strArrHour =   implode(',', array_column($arrHour, 'VALUE'));

$arrMinuteInterval   =	array(
    array("ID"=>"00", "VALUE"=>"00"),
    array("ID"=>"15", "VALUE"=>"15"),
    array("ID"=>"30", "VALUE"=>"30"),
    array("ID"=>"45", "VALUE"=>"45"),
);
$strArrMinuteInterval=  implode(',', array_column($arrMinuteInterval, 'VALUE'));

$arrMonth   =	array(
    array("ID"=>"01", "VALUE"=>"January"),
    array("ID"=>"02", "VALUE"=>"February"),
    array("ID"=>"03", "VALUE"=>"March"),
    array("ID"=>"04", "VALUE"=>"April"),
    array("ID"=>"05", "VALUE"=>"May"),
    array("ID"=>"06", "VALUE"=>"June"),
    array("ID"=>"07", "VALUE"=>"July"),
    array("ID"=>"08", "VALUE"=>"August"),
    array("ID"=>"09", "VALUE"=>"September"),
    array("ID"=>"10", "VALUE"=>"October"),
    array("ID"=>"11", "VALUE"=>"November"),
    array("ID"=>"12", "VALUE"=>"December")
);

$thisYear   =	date('Y');
$lastYear   =	date("Y", strtotime("-1 year"));
$nextYear   =	date("Y", strtotime("+1 year"));
$arrYear    =	array(
    array("ID"=>$nextYear, "VALUE"=>$nextYear),
    array("ID"=>$thisYear, "VALUE"=>$thisYear),
    array("ID"=>$lastYear, "VALUE"=>$lastYear)
);

defined('APP_NAME')                                     || define('APP_NAME', $_ENV['APP_NAME'] ?: 'WhatsApp');
defined('APP_NAME_FORMAL')                              || define('APP_NAME_FORMAL', $_ENV['APP_NAME_FORMAL'] ?: 'WhatsApp');
defined('APP_TIMEZONE')                                 || define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?: 'Asia/Jakarta');
defined('APP_MAIN_DATABASE_NAME')                       || define('APP_MAIN_DATABASE_NAME', $_ENV['APP_MAIN_DATABASE_NAME'] ?: 'db_default');
defined('MAX_INACTIVE_SESSION_MINUTES')                 || define('MAX_INACTIVE_SESSION_MINUTES', $_ENV['MAX_INACTIVE_SESSION_MINUTES'] ?: 60);
defined('LOG_WEBHOOK_MESSAGE')                          || define('LOG_WEBHOOK_MESSAGE', $_ENV['LOG_WEBHOOK_MESSAGE'] ?: false);

defined('PRODUCTION_URL')						        || define('PRODUCTION_URL', $productionURL);
defined('BASE_URL')                                     || define('BASE_URL', $_ENV['BASE_URL'] ?: 'https://example.com/');
defined('BASE_URL_ADMIN_APPS')                          || define('BASE_URL_ADMIN_APPS', $_ENV['BASE_URL_ADMIN_APPS'] ?: 'https://example.com/');
defined('BASE_URL_MOBILE_APPS')                         || define('BASE_URL_MOBILE_APPS', $_ENV['BASE_URL_MOBILE_APPS'] ?: 'https://example.com/');
defined('BASE_URL_ASSETS')                              || define('BASE_URL_ASSETS', str_replace(array("http:", "https:"), "", $_ENV['BASE_URL_ASSETS'] ?: 'https://example.com/'));
defined('BASE_URL_ASSETS_FULL_PATH')                    || define('BASE_URL_ASSETS_FULL_PATH', BASE_URL_ASSETS.$_ENV['BASE_URL_ASSETS_PATH'] ?: 'example.com/');
defined('BASE_URL_ASSETS_IMG')                          || define('BASE_URL_ASSETS_IMG', BASE_URL_ASSETS_FULL_PATH.$_ENV['BASE_URL_ASSETS_IMG_PATH'] ?: 'img/');
defined('BASE_URL_ASSETS_CSS')                          || define('BASE_URL_ASSETS_CSS', BASE_URL_ASSETS_FULL_PATH.$_ENV['BASE_URL_ASSETS_CSS_PATH'] ?: 'css/');
defined('BASE_URL_ASSETS_JS')                           || define('BASE_URL_ASSETS_JS', BASE_URL_ASSETS_FULL_PATH.$_ENV['BASE_URL_ASSETS_JS_PATH'] ?: 'js/');
defined('BASE_URL_ASSETS_FONT')                         || define('BASE_URL_ASSETS_FONT', BASE_URL_ASSETS_FULL_PATH.$_ENV['BASE_URL_ASSETS_FONT_PATH'] ?: 'font/');
defined('BASE_URL_ASSETS_SOUND')                        || define('BASE_URL_ASSETS_SOUND', BASE_URL_ASSETS_FULL_PATH.$_ENV['BASE_URL_ASSETS_SOUND_PATH'] ?: 'sound/');

defined('OPTION_HOUR')						            || define('OPTION_HOUR', $arrHour);
defined('OPTION_HOUR_STRARR')                           || define('OPTION_HOUR_STRARR', $strArrHour);
defined('OPTION_MINUTEINTERVAL')                        || define('OPTION_MINUTEINTERVAL', $arrMinuteInterval);
defined('OPTION_MINUTEINTERVAL_STRARR')                 || define('OPTION_MINUTEINTERVAL_STRARR', $strArrMinuteInterval);
defined('OPTION_MONTH')						            || define('OPTION_MONTH', $arrMonth);
defined('OPTION_YEAR')						            || define('OPTION_YEAR', $arrYear);

defined('PATH_STORAGE')						            || define('PATH_STORAGE', $_ENV['PATH_STORAGE'] ?: 'storage/');

defined('ONEMSGIO_TOKEN')                               || define('ONEMSGIO_TOKEN', $_ENV['ONEMSGIO_TOKEN'] ?: 'default');
defined('ONEMSGIO_NAMESPACE')                           || define('ONEMSGIO_NAMESPACE', $_ENV['ONEMSGIO_NAMESPACE'] ?: 'default');
defined('ONEMSGIO_CHANNEL_URL')                         || define('ONEMSGIO_CHANNEL_URL', $_ENV['ONEMSGIO_CHANNEL_URL'] ?: 'https://example.com/');
defined('ONEMSGIO_DEFAULT_CHATTEMPLATE_LANGUAGECODE')   || define('ONEMSGIO_DEFAULT_CHATTEMPLATE_LANGUAGECODE', $_ENV['ONEMSGIO_DEFAULT_CHATTEMPLATE_LANGUAGECODE'] ?: 'en_US');

defined('FIREBASE_PRIVATE_KEY_PATH')		            || define('FIREBASE_PRIVATE_KEY_PATH', APPPATH . $_ENV['FIREBASE_PRIVATE_KEY_PATH'] ?: 'default.json');
defined('FIREBASE_RTDB_URI')                            || define('FIREBASE_RTDB_URI', $_ENV['FIREBASE_RTDB_URI'] ?: 'https://example.com');
defined('FIREBASE_RTDB_PROJECT_ID')                     || define('FIREBASE_RTDB_PROJECT_ID', $_ENV['FIREBASE_RTDB_PROJECT_ID'] ?: 'default');
defined('FIREBASE_RTDB_MAINREF_NAME')                   || define('FIREBASE_RTDB_MAINREF_NAME', $_ENV['FIREBASE_RTDB_MAINREF_NAME'] ?: 'default/');