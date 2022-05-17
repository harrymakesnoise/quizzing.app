<?
$showForm = true;
$selector = getURLParam(1);
$token    = getURLParam(2);

if($requestedPage != 'forgot-password' || $token == '' || $selector == '') {
  header("Location: /");
  exit;
}

if($quizUser->checkPasswordResetTokens($selector, $token)) {
  $pass  = getCleanRequestParam('password');
  $cpass = getCleanRequestParam('confpassword');
  if(getCleanRequestParam('process') != '') {
    if($pass == '') {
      $quizUser->error = 'Password is required';
    } else if($pass != $cpass) {
      $quizUser->error = 'Confirm password does not match password';
    } else {
      if($quizUser->resetUserPassword($selector, $token, $pass)) {
        $showForm = false;
      }
    }
  }
} else {
  $quizUser->error = 'Invalid password reset link';
  $showForm = false;
}
renderSiteHeader();
?>
<div class="login-box">
  <div class="login-logo">
    <a href=""><?=$quizSite->getData('sitename')?></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <?=renderBoxHeader('Enter your new password below')?>

      <form action="/forgot-password/<?=$selector?>/<?=$token?>" method="post">
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" name="password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Confirm Password" name="confpassword">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <input type="hidden" value="1" name="process">
            <button type="submit" class="btn btn-primary btn-block">Change password</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mt-3 mb-1">
        <a href="login">Login</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
<?
renderSiteFooter();
?>