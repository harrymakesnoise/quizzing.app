<?
$headerScripts .= '<link href="/dist/css/cropper.min.css" rel="stylesheet"><style>.img-overlay{display:none;justify-content:center;align-items:center;position:absolute;top:0;left:0;height:100%;width:100%;background-color:rgba(255,255,255,0.7);}.profile-img-container:hover .img-overlay{display:flex;}</style>';

function renderAvatarPane() {
	global $footerScripts, $thisQuizUser, $quizUserData, $quizSite;

	$joinDateTimestamp = (isset($quizUserData['registered']) ? $quizUserData['registered'] : 0);
	$lastActiveTimestamp = (isset($quizUserData['last_login']) ? $quizUserData['last_login'] : 0);

	$joinDate = DateTime::createFromFormat("U", $joinDateTimestamp)->format('d/m/Y g:ia');
	$lastActive = DateTime::createFromFormat("U", $lastActiveTimestamp)->format('d/m/Y g:ia');

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

	$avatarURL = (isset($quizUserData['avatar']) ? $quizUserData['avatar'] : DEFAULT_AVATAR);
	$avatarEditSize = " " . ($avatarURL == DEFAULT_AVATAR ? 'col-12' : 'col-6') . " ";

	$footerScripts .= '<script src="/dist/js/cropper.min.js"></script>';
	$footerScripts .= '<script>var thisUserId=' . $quizUserData['id'] . ';var renderSrc = "' . ($avatarURL != DEFAULT_AVATAR ? $avatarURL : '') . '";</script>';
	$footerScripts .= '<script src="/dist/js/avatar' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js"></script>';

	echo '
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center position-relative profile-img-container">
                  <img class="profile-user-img img-fluid img-circle" src="/siteassets/user-images/' . $avatarURL . '" alt="' . $quizUserData['teamname'] . '">
                  <div class="img-overlay">
                    <span>
                      <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#modal-image-edit"><i class="far fa-edit"></i></button>
                      <button class="btn btn-xs btn-danger"' . ($avatarURL == DEFAULT_AVATAR ? ' style="display:none;"' : '') . ' data-toggle="modal" data-target="#modal-image-delete"><i class="fas fa-trash-alt"></i></button>
                    </span>
                  </div>
                </div>

                <h3 class="profile-username text-center">' . $quizUserData['teamname'] . '</h3>

                <p class="text-muted text-center">' . (isset($quizUserData['roles_mask']) ? $quizSite->getRoles($quizUserData['roles_mask']) : 'Unknown') . '<br />' . $quizSite->getData('sitename') . '</p>

                <ul class="list-group list-group-unbordered mb-3">
                ';
                /*  <li class="list-group-item">
                    <b>Site ID</b> <a class="float-right"><small>' . (isset($quizUserData['siteid']) ? $quizUserData['siteid'] : 'Unknown') . '</small></a>
                  </li>
                  */
                echo '
                  <li class="list-group-item">
                    <b>User ID</b> <a class="float-right"><small>' . (isset($quizUserData['id']) ? $quizUserData['id'] : 'Unknown') . '</small></a>
                  </li>
                  <li class="list-group-item">
                    <b>Join Date</b> <a class="float-right"><small>' . $joinDate . '</small></a>
                  </li>
                  <li class="list-group-item border-0">
                    <b>Last Login</b> <a class="float-right"><small>' . $lastActive . '</small></a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
      <div class="modal fade" id="modal-image-edit">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Edit Profile Image</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <ul class="nav nav-tabs row" id="edit-image-tab" role="tablist">
                <li class="nav-item col-' . ($avatarURL != DEFAULT_AVATAR ? '6' : '12') . ' text-center" onClick="$(\'#edit-image-mode\').val(\'upload\');">
                  <a class="nav-link active" id="edit-image-upload-tab" data-toggle="pill" href="#edit-image-upload" role="tab" aria-controls="edit-image-upload" aria-selected="true">Upload New Image</a>
                </li>
                <li class="nav-item col-6 text-center" onClick="$(\'#edit-image-mode\').val(\'update\');" ' . ($avatarURL == DEFAULT_AVATAR ? 'style="display:none;"' : '') . '>
                  <a class="nav-link" id="edit-image-imageeditor-tab" data-toggle="pill" href="#edit-image-imageeditor" role="tab" aria-controls="edit-image-imageeditor" aria-selected="false">Edit Current Photo</a>
                </li>
              </ul>
              <input type="hidden" id="edit-image-mode" value="upload">
              <div class="tab-content p-2" id="custom-tabs-three-tabContent">
                <div class="tab-pane fade show active" id="edit-image-upload" role="tabpanel" aria-labelledby="edit-image-upload-tab">
                  <div class="form-group">
                    <label for="profilePictureInput">Upload Profile Picture</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="profilePictureInput" onChange="previewImage()">
                        <label class="custom-file-label" for="profilePictureInput">Choose Picture</label>
                      </div>
                    </div>
                  </div>
                  <img class="image-preview" src="/siteassets/user-images/' . $avatarURL . '" style="display:block;margin:0 auto;max-height:250px;max-width:100%;height:auto;width:auto;">
                </div>
                <div class="tab-pane fade" id="edit-image-imageeditor" role="tabpanel" aria-labelledby="edit-image-imageeditor-tab">
                  <div class="w-100">
                    <img class="image-preview" id="image-edit-image" src="/siteassets/user-images/' . $avatarURL . '" style="display:block;margin:0 auto;max-width:100%;height:auto;width:auto;">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div class="row w-100">
                <div class="col-6">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                <div class="col-6 text-right">
                  <button type="button" class="btn btn-primary" id="image-edit-save-btn">Save changes</button>
                </div>
              </div>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->      
      <div class="modal fade" id="modal-image-delete">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Remove Profile Image</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              Are you sure you want to remove your profile picture?
            </div>
            <div class="modal-footer">
              <div class="row w-100">
                <div class="col-6">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                <div class="col-6 text-right">
                  <button type="button" class="btn btn-primary" id="image-delete-btn">Confirm</button>
                </div>
              </div>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->';
}