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

$joinDateTimestamp = (isset($quizUserData['registered']) ? $quizUserData['registered'] : 0);
$lastActiveTimestamp = (isset($quizUserData['last_login']) ? $quizUserData['last_login'] : 0);

$joinDateObj = new DateTime("@" . $joinDateTimestamp);
$joinDateObj->setTimeZone(new DateTimeZone(date_default_timezone_get()));
$lastActiveObj = new DateTime("@" . $lastActiveTimestamp);
$lastActiveObj->setTimeZone(new DateTimeZone(date_default_timezone_get()));

$joinDate = $joinDateObj->format('d/m/Y g:ia');
$lastActive = $lastActiveObj->format('d/m/Y g:ia');

$userStatus = 'Unknown';
if($quizUserData['status'] == \Delight\Auth\Status::NORMAL) {
	$userStatus = 'Active';
} else if($quizUserData['status'] == \Delight\Auth\Status::ARCHIVED) {
	$userStatus = 'Archived';
} else if($quizUserData['status'] == \Delight\Auth\Status::BANNED) {
	$userStatus = 'Banned';
} else if($quizUserData['status'] == \Delight\Auth\Status::LOCKED) {
	$userStatus = 'Locked';
} else if($quizUserData['status'] == \Delight\Auth\Status::PENDING_REVIEW) {
	$userStatus = 'Pending Review';
} else if($quizUserData['status'] == \Delight\Auth\Status::SUSPENDED) {
	$userStatus = 'Suspended';
}

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
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle" src="/dist/img/user4-128x128.jpg" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center"><?=(isset($quizUserData['fullname']) ? $quizUserData['fullname'] : '')?></h3>

                <p class="text-muted text-center"><?=(isset($quizUserData['roles_mask']) ? $quizSite->getRoles($quizUserData['roles_mask']) : 'Unknown')?><br /><?=(isset($quizUserData['sitename']) ? $quizUserData['sitename'] : 'Unknown')?></p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Site ID</b> <a class="float-right"><small><?=(isset($quizUserData['siteid']) ? $quizUserData['siteid'] : 'Unknown')?></small></a>
                  </li>
                  <li class="list-group-item">
                    <b>User ID</b> <a class="float-right"><small><?=(isset($quizUserData['id']) ? $quizUserData['id'] : 'Unknown')?></small></a>
                  </li>
                  <li class="list-group-item">
                    <b>Join Date</b> <a class="float-right"><small><?=$joinDate?></small></a>
                  </li>
                  <li class="list-group-item">
                    <b>Last Active</b> <a class="float-right"><small><?=$lastActive?></small></a>
                  </li>
                  <li class="list-group-item border-0">
                    <b>Status</b> <a class="float-right"><small><?=$userStatus?></small></a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                  <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="activity">
                    <!-- Post -->
                    <div class="post">
                      <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user1-128x128.jpg" alt="user image">
                        <span class="username">
                          <a href="#">Jonathan Burke Jr.</a>
                          <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Shared publicly - 7:30 PM today</span>
                      </div>
                      <!-- /.user-block -->
                      <p>
                        Lorem ipsum represents a long-held tradition for designers,
                        typographers and the like. Some people hate it and argue for
                        its demise, but others ignore the hate as they create awesome
                        tools to help create filler text for everyone from bacon lovers
                        to Charlie Sheen fans.
                      </p>

                      <p>
                        <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                        <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                        <span class="float-right">
                          <a href="#" class="link-black text-sm">
                            <i class="far fa-comments mr-1"></i> Comments (5)
                          </a>
                        </span>
                      </p>

                      <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                    </div>
                    <!-- /.post -->

                    <!-- Post -->
                    <div class="post clearfix">
                      <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user7-128x128.jpg" alt="User Image">
                        <span class="username">
                          <a href="#">Sarah Ross</a>
                          <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Sent you a message - 3 days ago</span>
                      </div>
                      <!-- /.user-block -->
                      <p>
                        Lorem ipsum represents a long-held tradition for designers,
                        typographers and the like. Some people hate it and argue for
                        its demise, but others ignore the hate as they create awesome
                        tools to help create filler text for everyone from bacon lovers
                        to Charlie Sheen fans.
                      </p>

                      <form class="form-horizontal">
                        <div class="input-group input-group-sm mb-0">
                          <input class="form-control form-control-sm" placeholder="Response">
                          <div class="input-group-append">
                            <button type="submit" class="btn btn-danger">Send</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- /.post -->

                    <!-- Post -->
                    <div class="post">
                      <div class="user-block">
                        <img class="img-circle img-bordered-sm" src="../../dist/img/user6-128x128.jpg" alt="User Image">
                        <span class="username">
                          <a href="#">Adam Jones</a>
                          <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                        </span>
                        <span class="description">Posted 5 photos - 5 days ago</span>
                      </div>
                      <!-- /.user-block -->
                      <div class="row mb-3">
                        <div class="col-sm-6">
                          <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                          <div class="row">
                            <div class="col-sm-6">
                              <img class="img-fluid mb-3" src="../../dist/img/photo2.png" alt="Photo">
                              <img class="img-fluid" src="../../dist/img/photo3.jpg" alt="Photo">
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-6">
                              <img class="img-fluid mb-3" src="../../dist/img/photo4.jpg" alt="Photo">
                              <img class="img-fluid" src="../../dist/img/photo1.png" alt="Photo">
                            </div>
                            <!-- /.col -->
                          </div>
                          <!-- /.row -->
                        </div>
                        <!-- /.col -->
                      </div>
                      <!-- /.row -->

                      <p>
                        <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                        <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                        <span class="float-right">
                          <a href="#" class="link-black text-sm">
                            <i class="far fa-comments mr-1"></i> Comments (5)
                          </a>
                        </span>
                      </p>

                      <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                    </div>
                    <!-- /.post -->
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="timeline">
                    <!-- The timeline -->
                    <div class="timeline timeline-inverse">
                      <!-- timeline time label -->
                      <div class="time-label">
                        <span class="bg-danger">
                          10 Feb. 2014
                        </span>
                      </div>
                      <!-- /.timeline-label -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-envelope bg-primary"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 12:05</span>

                          <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>

                          <div class="timeline-body">
                            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                            weebly ning heekya handango imeem plugg dopplr jibjab, movity
                            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                            quora plaxo ideeli hulu weebly balihoo...
                          </div>
                          <div class="timeline-footer">
                            <a href="#" class="btn btn-primary btn-sm">Read more</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-user bg-info"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>

                          <h3 class="timeline-header border-0"><a href="#">Sarah Young</a> accepted your friend request
                          </h3>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-comments bg-warning"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>

                          <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>

                          <div class="timeline-body">
                            Take me to your leader!
                            Switzerland is small and neutral!
                            We are more like Germany, ambitious and misunderstood!
                          </div>
                          <div class="timeline-footer">
                            <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <!-- timeline time label -->
                      <div class="time-label">
                        <span class="bg-success">
                          3 Jan. 2014
                        </span>
                      </div>
                      <!-- /.timeline-label -->
                      <!-- timeline item -->
                      <div>
                        <i class="fas fa-camera bg-purple"></i>

                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> 2 days ago</span>

                          <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>

                          <div class="timeline-body">
                            <img src="http://placehold.it/150x100" alt="...">
                            <img src="http://placehold.it/150x100" alt="...">
                            <img src="http://placehold.it/150x100" alt="...">
                            <img src="http://placehold.it/150x100" alt="...">
                          </div>
                        </div>
                      </div>
                      <!-- END timeline item -->
                      <div>
                        <i class="far fa-clock bg-gray"></i>
                      </div>
                    </div>
                  </div>
                  <!-- /.tab-pane -->

                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal" action="/user-manager/edit/<?=$userID?>" method="post">
                    	<input type="hidden" name="process" value="1">
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" name="inputName" id="inputName" placeholder="Name" value="<?=(isset($quizUserData['fullname']) ? $quizUserData['fullname'] : '')?>">
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