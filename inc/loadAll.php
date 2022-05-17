<?
$hostname = WEBSITE_HOSTNAME;
define('ROOT_PATH', 'W:\\quizzing.app');
define('INCLUDES_PATH', ROOT_PATH . '\\inc');
define('CLASSES_PATH', INCLUDES_PATH . '\\classes');
define('FUNCTIONS_PATH', INCLUDES_PATH . '\\functions');
define('COMMON_PATH', INCLUDES_PATH . '\\common');

if(strtolower($hostname) == 'quizzing.app') {
	define('PAGES_PATH', ROOT_PATH . '\\corporate\\pages');
} else {
	define('PAGES_PATH', ROOT_PATH . '\\pages');
}

if(session_status() == PHP_SESSION_NONE) {
  session_start();
}

include_once(INCLUDES_PATH . '\\config_constants.php');
include_once(INCLUDES_PATH . '\\AES256.php');
include_once(FUNCTIONS_PATH . '\\functions_db.php');
include_once(FUNCTIONS_PATH . '\\functions.php');
include_once(ROOT_PATH . '\\vendor\\autoload.php');
include_once(CLASSES_PATH . '\\QuizCore.php');
include_once(CLASSES_PATH . '\\QuizEmail.php');
include_once(CLASSES_PATH . '\\QuizSite.php');
include_once(CLASSES_PATH . '\\QuizTemplate.php');

if(strtolower($hostname) == 'quizzing.app') {
  include_once(ROOT_PATH . '/corporate/index.php');
  exit;
}

$quizSite = new QuizSite();

$authDB = new \PDO('mysql:dbname=' . SS_DBNAME . ';host=' . SS_DBHOST . ';charset=utf8mb4', SS_DBUSER, SS_DBPASS);
$auth   = new \Delight\Auth\Auth($authDB, $quizSite->siteid);

include_once(CLASSES_PATH . '\\QuizUser.php');
include_once(CLASSES_PATH . '\\QuizElements.php');
//include_once(CLASSES_PATH . '\\class.player.php');
//include_once(CLASSES_PATH . '\\class.quiz.php');
include_once(COMMON_PATH . '\\SiteHeadAndFoot.php');