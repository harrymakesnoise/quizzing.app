<?
$filter = getURLParam(1);
$userID = getURLParam(2);

if(getCleanRequestParam('process') != '') {
	if($quizSite->updateUser($userID)) {
		header("Location: /user-manager/edit/" . $userID);
		exit;
	}
}

$thisQuizUser = new QuizUser();
$quizUserData = $thisQuizUser->getUserById($userID);

include_once(PAGES_PATH . '/widgets/avatarPane.php');

$headerScripts .= '  <!-- Select2 -->
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">';
$footerScripts .= '
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>$(".select2bs4").select2({theme: "bootstrap4"})</script>';
renderSiteHeader();
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>User Manager</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item active">User Manager</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <?=renderAvatarPane()?>
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                  <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" action="/user-manager/edit/<?=$userID?>" method="post">
                      <input type="hidden" name="process" value="1">
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" name="inputName" id="inputName" placeholder="Name" value="<?=(isset($quizUserData['teamname']) ? $quizUserData['teamname'] : '')?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                          <input type="email" class="form-control" name="inputEmail" id="inputEmail" placeholder="Email" value="<?=(isset($quizUserData['email']) ? $quizUserData['email'] : '')?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputRoles" class="col-sm-2 col-form-label">User Role</label>
                        <div class="col-sm-10">
                          <select class="select2bs4 form-control" id="inputRoles" name="inputRoles">
                            <option></option>
                            <? foreach($quizSite->getRoles() as $roleID=>$roleDesc) { ?>
                              <option value="<?=$roleID?>"<?=(isset($quizUserData['roles_mask']) && $quizUserData['roles_mask'] == $roleID ? ' selected' : '')?>><?=$roleDesc?></option>
                            <? } ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputStatus" class="col-sm-2 col-form-label">User Status</label>
                        <div class="col-sm-10">
                          <select class="select2bs4 form-control" id="inputStatus" name="inputStatus">
                            <option></option>
                            <? foreach($quizSite->getUserStatus() as $statusID=>$statusDesc) { ?>
                              <option value="<?=$statusID?>"<?=(isset($quizUserData['status']) && $quizUserData['status'] == $statusID ? ' selected' : '')?>><?=$statusDesc?></option>
                            <? } ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="col-2">&nbsp;</div>
                        <div class="col-4 text-left">
                          <button type="reset" class="btn btn-danger">Reset&nbsp;&nbsp;<i class="fa fa-times"></i></button>
                        </div>
                        <div class="col-6 text-right">
                          <button type="submit" class="btn btn-success">Save&nbsp;&nbsp;<i class="fa fa-check"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="activity">
                  </div>
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
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
<?
renderSiteFooter();
?>