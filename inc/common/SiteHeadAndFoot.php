<?
if(!isset($auth)) {
  $auth = new \Delight\Auth\Auth($db);
}

$filePrefix = 'guest';
if($auth->isLoggedIn()) {
  $filePrefix = 'user';
  if($auth->hasAnyRole(
        \Delight\Auth\Role::MODERATOR,
        \Delight\Auth\Role::SUPER_MODERATOR,
        \Delight\Auth\Role::ADMIN,
        \Delight\Auth\Role::SUPER_ADMIN
    )) {
    $filePrefix = 'admin';
  }
}
$fileFound = 0;
$headerFile = COMMON_PATH . '\\' . $filePrefix . 'Header.php';
$footerFile = COMMON_PATH . '\\' . $filePrefix . 'Footer.php';

if(file_exists($headerFile)) {
  $fileFound++;
}
if(file_exists($footerFile)) {
  $fileFound++;
}

if($fileFound == 2) {
  include_once $headerFile;
  include_once $footerFile;
} else {
  function renderSiteHeader() {}
  function renderSiteFooter() {}
}
?>