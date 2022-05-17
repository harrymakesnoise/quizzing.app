<?
$showForm = true;
if(getCleanRequestParam('teamname') != '') {
  if($quizUser->register()) {
    $showForm = false;
  }
}
renderSiteHeader();
?>
<div class="register-box">
  <div class="register-logo">
    <a href=""><?=$quizSite->getData('sitename')?></a>
  </div>

  <div class="card">
    <div class="card-body register-card-body">
      <?=renderBoxHeader('Register a new membership')?>
      <? if($showForm) { ?>
      <form action="" method="post">
        <div class="form-group mb-3">
          <div class="input-group">
            <input type="text" class="form-control" name="teamname" placeholder="Team Name" value="<?=getCleanRequestParam('teamname')?>">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" name="email" placeholder="Email" value="<?=getCleanRequestParam('email')?>">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="confpass" placeholder="Retype password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="agreeTerms" name="terms" value="agree">
              <label for="agreeTerms">
               I agree to the <a href="/terms-conditions" target="_blank">Terms &amp; Conditions</a>
              </label>
            </div>
            <div class="icheck-primary">
              <input type="checkbox" id="agreePrivacy" name="privacy" value="agree">
              <label for="agreePrivacy">
               I agree to the <a href="/privacy-policy" target="_blank">Privacy Policy</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
<? /*
      <div class="social-auth-links text-center">
        <p>- OR -</p>
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i>
          Sign up using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i>
          Sign up using Google+
        </a>
      </div>*/?>

      <a href="login" class="text-center">I already have a membership</a>
      <? } ?>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
<!-- /.register-box -->
<?
renderSiteFooter();
?>