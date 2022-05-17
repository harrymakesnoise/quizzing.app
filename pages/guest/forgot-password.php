<?
if(getURLParam(1) != '') {
  include_once('reset-password.php');
  exit;
}
if(getCleanRequestParam('email') != '') {
  if($quizUser->initiateResetPassword()) {
    $showForm = false;
  }
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
      <?=renderBoxHeader('Enter your e-mail address to reset your password.')?>
      <form action="/forgot-password" method="post">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" name="email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Reset My Password</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mt-3 mb-1">
        <a href="login">Login</a>
      </p>
      <p class="mb-0">
        <a href="register" class="text-center">Register a new membership</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
<?
renderSiteFooter();
?>