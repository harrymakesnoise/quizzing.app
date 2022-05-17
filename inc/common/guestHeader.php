<?
function renderSiteHeader($bodyClass = 'login-page') {
  global $quizSite, $requestedPage, $headerScripts;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?=$quizSite->getData('sitename')?> | <?=ucWords($requestedPage)?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <base href="<?=WEBSITE_HOMEURL?>">
  <?=$headerScripts?>
</head>
<body class="hold-transition <?=$bodyClass?>">
<?
}

function renderBoxHeader($defaultText='') {
  global $quizUser;
  
  if($quizUser->success != '') {
    echo '<div class="alert alert-success">' . $quizUser->success . '</div>';
  } else if($quizUser->error == '') {
    echo '<p class="login-box-msg">' . $defaultText . '</p>';
  } else {
    echo '<div class="alert alert-danger">' . $quizUser->error . '</div>';
  }
}
?>