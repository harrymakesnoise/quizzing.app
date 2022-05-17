<?
function renderSiteHeader() {
	global $quizSite, $auth, $thisQuizUser, $quizUserData, $quizUser, $quiz, $class, $selectedQuiz, $selectedQuizQuestion, $selectedQuizRound, $headerScripts, $footerScripts, $requestedPage;

  $quizUser->getData('teamname');

  if($thisQuizUser == null) {
    $thisQuizUser = new QuizUser();
    $quizUserData = $thisQuizUser->getUserById($auth->id());
  }

  $userName = $quizUserData['teamname'];

  if($class == null) {
    if(isset($quiz)) {
      $class = ($class == null ? $quiz : $class);
    }
    if(isset($selectedQuiz)) {
      $class = ($class == null ? $selectedQuiz : $class);
    }
    if(isset($selectedQuizQuestion)) {
      $class = ($class == null ? $selectedQuizQuestion : $class);
    }
    if(isset($selectedQuizRound)) {
      $class = ($class == null ? $selectedQuizRound : $class);
    }
  }

  $pageTitle = ucWords(str_replace('-', ' ', $requestedPage));
  $paramVal1 = getURLParam(1);
  $paramVal2 = getURLParam(2);
  $paramVal3 = getURLParam(3);

  if(intVal($paramVal1) > 0 && $class != null) {
    $pageTitle .= ' | ' . $class->getData('label');
  } else if(intVal($paramVal2) > 0 ) {
    $pageTitle .= ' | ' . ucWords($paramVal1);
    if($class != null) {
      $pageTitle .= ' - ' . $class->getData('label');
    }
  } else if($paramVal1 != '') {
    $pageTitle .= ' | ' . ucWords($paramVal1);

    if($paramVal2 != '') {
      $pageTitle .= ' - ' . ucWords($paramVal2);
    }

    if(intVal($paramVal3) > 0 && $class != null) {
      $pageTitle .= ': ' . $class->getData('label');
    }
  }

  $pageTitle = $quizSite->getData('sitename') . ' | ' . $pageTitle;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title><?=$pageTitle?></title>
  <base href="<?=WEBSITE_HOMEURL?>/">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <?
  if(isset($_SESSION['toast'])) { 
    $footerScripts .= '<!-- Toastr -->
<script src="/plugins/toastr/toastr.min.js"></script><script>$(document).ready(function(){toastr.' . $_SESSION['toast']['type'] . '("' . $_SESSION['toast']['message'] . '");});</script>';
    unset($_SESSION['toast']);
  ?>
  <!-- Toastr -->
  <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
  <? } ?>
  <?=$headerScripts?>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="/index3.html" class="navbar-brand">
        <img src="/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light"><?=$quizSite->getData('sitename')?></span>
      </a>
      
      <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="/home" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="/leaderboards" class="nav-link">Leaderboards</a>
          </li>
        </ul>
      </div>

      <!-- Right navbar links -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle"><?=$userName?>&nbsp;&nbsp;</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="/settings" class="dropdown-item">Account Settings</a></li>
            <li><a href="/logout" class="dropdown-item">Sign Out</a></li>
          </ul>
        </li>
        <?/*<!-- Messages Dropdown Menu -->
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button"><i
              class="fas fa-th-large"></i></a>
        </li>*/?>
      </ul>
    </div>
  </nav>
  <!-- /.navbar -->
<?
}
?>