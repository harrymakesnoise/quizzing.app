<?
if(getCleanRequestParam('process') != '') {
  if($quizSite->updateOwnUser()) {
    unset($_SESSION['userdata']);
    $quizUser->getData('fullname'); //Refresh user data
    header("Location: /settings");
    exit;
  }
}

include_once(PAGES_PATH . '/widgets/avatarPane.php');

renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?=$quizUserData['teamname']?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/home">Home</a></li>
              <li class="breadcrumb-item active">Account Settings</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container">
        <div class="row">
          <div class="col-md-3">
            <?=renderAvatarPane()?>
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <?/*
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                  <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                </ul>
              </div><!-- /.card-header -->*/?>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" action="/settings" method="post">
                      <input type="hidden" name="process" value="1">
                      <p>To change any details, please enter your password.</p>
                      <div class="form-group row">
                        <label for="inputCheckPassword" class="col-sm-3 col-form-label">Current Password</label>
                        <div class="col-sm-9">
                          <input type="password" class="form-control" name="inputCheckPassword" id="inputCheckPassword" placeholder="Current Password" value="">
                        </div>
                      </div>
                      <hr />
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-3 col-form-label">Team Name</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control" name="inputName" id="inputName" placeholder="Team Name" value="<?=$quizUserData['teamname']?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                          <input type="email" class="form-control" name="inputEmail" id="inputEmail" placeholder="Email" value="<?=$quizUserData['email']?>">
                        </div>
                      </div>
                      <hr />
                      <p>To change your password, please enter a new one in the boxes below.</p>
                      <div class="form-group row">
                        <label for="inputPassword" class="col-sm-3 col-form-label">New Password</label>
                        <div class="col-sm-9">
                          <input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="New Password" value="">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputConfirmPassword" class="col-sm-3 col-form-label">Confirm New Password</label>
                        <div class="col-sm-9">
                          <input type="password" class="form-control" name="inputConfirmPassword" id="inputConfirmPassword" placeholder="New Password" value="">
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="col-4 text-left">
                          <button type="reset" class="btn btn-danger">Reset&nbsp;&nbsp;<i class="fa fa-times"></i></button>
                        </div>
                        <div class="col-8 text-right">
                          <button type="submit" class="btn btn-success">Save&nbsp;&nbsp;<i class="fa fa-check"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                  <?/*<div class="tab-pane" id="activity">
                  </div>*/?>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.nav-tabs-custom -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?
renderSiteFooter();
?>