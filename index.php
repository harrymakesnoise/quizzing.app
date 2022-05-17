<?
include_once("inc/loadAll.php");
$quizUser = new QuizUser();

if(!$quizSite->getData('siteEnabled', 'bool')) {
  include_once(PAGES_PATH . '/disabled.php');
  exit;
}
if(!$quizSite->siteDataLoaded) {
  include_once(PAGES_PATH . '/sitenotfound.php');
  exit;
}

$defaultPages = [];
$defaultPages['admin']      = 'dashboard';
$defaultPages['quizmaster'] = 'dashboard';
$defaultPages['user']       = 'home';
$defaultPages['guest']      = 'login';

function getUserFilePrefix() {
  global $auth, $quizUser, $quizSite;
  
  if(!$auth->isLoggedIn()) {
    return 'guest';
  } else if($quizUser->isQuizMaster()) {
    return 'quizmaster';
  } else if($quizUser->isAdmin()) {
    if($quizSite->siteid != 0) {
      return 'quizmaster';
    }
    return 'admin';
  } else {
    return 'user';
  }
}

if(getURLParam(0) == 'logout') {
  $auth->logout();
  $auth->destroySession();
}

if(getURLParam(0) == 'api') {
  header("Content-type: application/json");
  if(file_exists(ROOT_PATH . '\\api\\' . getURLParam(1) . '.php')) {
    include_once(ROOT_PATH . '\\api\\' . getURLParam(1) . '.php');
    exit;
  }
}

$siteAssetsURLTag = 'siteassets';

if(strtolower(getURLParam(0)) == $siteAssetsURLTag) {
  $svrRequest = $_SERVER['REQUEST_URI'];
  $siteAssetsOffset = strlen($siteAssetsURLTag)+2; // "/siteassets/"
  $questionMarkPos = strpos(substr($svrRequest, $siteAssetsOffset), '?');
  if($questionMarkPos !== FALSE) {
    $requestURL = substr($svrRequest, $siteAssetsOffset, $questionMarkPos);
  } else {
    $requestURL = substr($svrRequest, $siteAssetsOffset);
  }
  
  if(!$requestURL == "") {
    
    $absolutePath = WEBSITE_DOCROOT . '/sites/' . $quizSite->siteid . '/' . $requestURL;
    $sharedPath   = WEBSITE_DOCROOT . '/sites/GLOBAL/' . $requestURL;
    $filePath     = (file_exists($absolutePath) ? $absolutePath : $sharedPath);

    if(file_exists($filePath) && !is_dir($filePath)) {
      header('Content-Type: ' . mime_content_type($filePath));
      header('Content-Length: ' . filesize($filePath));
      readfile($filePath);
      exit;
    } else {
      if(strpos($filePath, 'user-images') !== FALSE) {
        header("Location: /" . $siteAssetsURLTag . "/user-images/" . DEFAULT_AVATAR);
        exit;
      }
    }
  }
  header("Location: /404");
  exit;
}

$pagePrefix  = getUserFilePrefix();
$defaultPage = $defaultPages[$pagePrefix];

$requestedAction = getURLParam(1, 'view');
if($requestedAction == 'new' || $requestedAction == 'edit') {
  $pagePrefix .= '\\create-edit';
}
define('PAGE_ACTION', $requestedAction);
$requestedPage   = getURLParam(0, '');

if($requestedPage == '') {
  header("Location: " . WEBSITE_HOMEURL . '/' . $defaultPage);
  exit;
}
$requestedFile   = PAGES_PATH . "\\" . $pagePrefix . '\\' . $requestedPage . '.php';
$rootRequestedFile   = PAGES_PATH . '\\' . $requestedPage . '.php';

$headerScripts = '';
$footerScripts = '';

if(file_exists($requestedFile)) {
  include_once($requestedFile);
} else if(file_exists($rootRequestedFile)) {
  include_once($rootRequestedFile);
} else {
  if($requestedPage == $defaultPage) {
    die();
  } else {
    header("Location: /");
  }
}
?>