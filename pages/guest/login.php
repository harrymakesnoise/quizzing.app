<?
$email    = getCleanRequestParam('email');
$remember = (getCleanRequestParam('remember') == '1');

$activateAccount = (getURLParam(1) != '' && getURLParam(2) != '');
if($email != '') {
  if($quizUser->login()) {
    header("Location: /");
  }
}

if($activateAccount) {
  $quizUser->activateAccount(getURLParam(1), getURLParam(2));
}

$headerScripts .= '<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : "3533926933324198",
      cookie     : true,
      xfbml      : true,
      version    : "v8.0"
    });
      
    FB.AppEvents.logPageView();
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, "script", "facebook-jssdk"));

  function facebookLogin() {
    FB.login(function(response) {
      statusChangeCallback(response)
    }, {scope: "public_profile,email"});
  }

  function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
    console.log("statusChangeCallback");
    console.log(response);                   // The current login status of the person.
    if (response.status === "connected") {   // Logged into your webpage and Facebook.
      FB.api("/me", function(response) {
        console.log("Successful login for: " + response.name);
        document.getElementById("status").innerHTML =
          "Thanks for logging in, " + response.name + "!";
      });
    } else {                                 // Not logged into your webpage or we are unable to tell.
      alert("Not logged in");
    }
  }

</script>';
renderSiteHeader();
?>
<div class="login-box">
  <div class="login-logo">
    <a href=""><?=$quizSite->getData('sitename')?></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <?=renderBoxHeader('Login to start your session')?>
      <form action="/login/" method="post">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email" value="<?=$email?>">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" name="remember" value="1"<?=($remember == '1' ? ' checked' : '')?>>
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
<? /*
      <div class="social-auth-links text-center mb-3">
        <p>- OR -</p>
        <a href="" class="btn btn-block btn-primary" onClick="facebookLogin();return false;">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
      <!-- /.social-auth-links -->
      */?>
      <p class="mb-1">
        <a href="forgot-password">I forgot my password</a>
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