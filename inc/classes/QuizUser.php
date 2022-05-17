<?
class QuizUser extends QuizCore {
  public function login() {
    global $auth, $quizSite;
    
    $email    = getCleanRequestParam('email');
    $password = getCleanRequestParam('password');
    try {
      $auth->login($email, $password);
      return true;
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
      $this->error = 'Invalid e-mail / password';
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->error = 'Invalid e-mail / password';
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $this->error = 'E-mail address not verified';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests, please try again later.';
    }
    return false;
  }
  public function register() {
    global $auth;
    
    $email    = getCleanRequestParam('email');
    $name     = getCleanRequestParam('teamname');
    $password = getCleanRequestParam('password');
    $confPass = getCleanRequestParam('confpass');
    $termsAccept = getCleanRequestParam('terms') == 'agree';
    $privAccept = getCleanRequestParam('privacy') == 'agree';
    
    if($name == '') {
      $this->error = 'Name is required';
    } else if($email == '') {
      $this->error = 'E-mail is required';
    } else if($password == '') {
      $this->error = 'Password is required';
    } else if($confPass == '') {
      $this->error = 'Confirm Password is required';
    } else if($password != $confPass) {
      $this->error = 'Password and confirm password do not match.';
    } else if(!$termsAccept) {
      $this->error = 'Please agree to the terms and conditions';
    } else if(!$privAccept) {
      $this->error = 'Please agree to the privacy policy';
    }
    
    if($this->error != '') {
      return false;
    }
    
    try {
      $userId = $auth->register($email, $password, null/*, function ($selector, $token) {
        global $quizSite;
        
        $mail = new QuizEmail();
        $mail->sendWelcomeEmail($selector, $token, getCleanRequestParam('teamname'), getCleanRequestParam('email'));
      }*/);
      
      if($userId > 0) {
        $this->success = 'Your account has been created, please check your e-mail inbox for an activation link. You will need this to login.';
        $this->success = 'Your account has been created, click <a href="/login">here to login</a>.';
        $sql = 'INSERT INTO users_data (userid, metakey, metavalue) VALUES (?, ?, ?);';
        $sqlValues = array($userId, 'teamname', makeHash($name));
        $quizDB = new QuizDB();
        if($quizDB->doInsert($sql, $sqlValues)) {
          return true;
        } else {
          $this->error = 'Failed to store user data.';
        }
      }
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
      $this->error = 'Invalid E-mail Address';
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->error = 'Invalid Password';
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {
      $this->error = 'E-mail already registered';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests, please try again later.';
    }
    return false;
  }
  
  public function activateAccount($selector, $token) {
    global $auth;
    
    try {
      $auth->confirmEmail($selector, $token);
      $this->success = 'Your e-mail has now been verified. You may now login.';
      return true;
    }
    catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
      $this->error = 'Invalid token';
    }
    catch (\Delight\Auth\TokenExpiredException $e) {
      $this->error = 'Token expired';
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {
      $this->error = 'Email address already exists';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests';
    }
    return false;
  }
  
  public function initiateResetPassword() {
    global $auth;
    try {
      $auth->forgotPassword(getCleanRequestParam('email'), function ($selector, $token) {
        $mail = new QuizEmail();
        $mail->sendPasswordResetEmail($selector, $token, getCleanRequestParam('email'));
      });
      $this->success = 'Please check your e-mail account for an e-mail from us, this will contain a password reset link.';
      return true;
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
      $this->error = 'Invalid email address';
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $this->error = 'Email not verified';
    }
    catch (\Delight\Auth\ResetDisabledException $e) {
      $this->error = 'Password reset is disabled';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests';
    }
    return false;
  }
  
  public function checkPasswordResetTokens($selector, $token) {
    global $auth;
    
    try {
      $auth->canResetPasswordOrThrow($selector, $token);
      return true;
    }
    catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
      $this->error = 'Invalid token';
    }
    catch (\Delight\Auth\TokenExpiredException $e) {
      $this->error = 'Token expired';
    }
    catch (\Delight\Auth\ResetDisabledException $e) {
      $this->error = 'Password reset is disabled';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests';
    }
    
    return false;
  }
  
  public function resetUserPassword($selector, $token, $password) {
    global $auth;
    try {
      $auth->resetPassword($selector, $token, $password);
      $this->success = 'Password has been reset successfully. You may now login.';
      return true;
    }
    catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
      $this->error = 'Invalid token';
    }
    catch (\Delight\Auth\TokenExpiredException $e) {
      $this->error = 'Token expired';
    }
    catch (\Delight\Auth\ResetDisabledException $e) {
      $this->error = 'Password reset is disabled';
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->error = 'Invalid password';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->error = 'Too many requests';
    }
    return false;
  }
  
  public function isQuizMaster() {
    global $auth;
    return $auth->hasAnyRole(
        \Delight\Auth\Role::MODERATOR,
        \Delight\Auth\Role::SUPER_MODERATOR
    );
  }
  
  public function isAdmin() {
    global $auth;
    return $auth->hasAnyRole(
        \Delight\Auth\Role::ADMIN,
        \Delight\Auth\Role::SUPER_ADMIN
    );
  }

  public function getUserById($id=0, $siteid='') {
    global $quizSite, $authDB, $auth;

    $siteIDSQL = '';
    if($siteid == '') {
      if(!$this->isAdmin()) {
        $siteIDSQL = ' u.siteid = ' . $quizSite->siteid . ' and';
      }
    }

    $sql = 'SELECT u.*, s.metakey as sitemetakey, s.metavalue as sitemetavalue, m.metakey, m.metavalue FROM users u left outer join sites_data s on (s.siteid = u.siteid) left outer join users_data m on (m.userid = id) where' . $siteIDSQL . ' id = ' . $id;
    $return = array();
    try {
      $stmt = $authDB->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $first = true;
      foreach($result as $row) {
        $metaKey = $row['metakey'];
        $row[$metaKey] = readHash($row['metavalue']);
        unset($row['metavalue']);
        unset($row['metakey']);

        $metaKey = $row['sitemetakey'];
        $row[$metaKey] = $row['sitemetavalue'];
        unset($row['sitemetavalue']);
        unset($row['sitemetakey']);

        if($first) {
          $return = $row;
          $first = false;
        } else {
          $return[$metaKey] = readHash($row[$metaKey]);
        }
        
        if($this->isQuizMaster() || $this->isAdmin()) {
          $return['isquizmaster'] = true;
        }
        if($this->isAdmin()) {
          $return['isadmin'] = true;
        }

        $this->getMongoConnection();
        $collection = $this->mongo->selectCollection('user_sessions');
        $data = $collection->find(
          array('siteid' => array('$eq' => intVal($return['siteid'])), 'userid' => array('$eq' => intVal($return['id'])))
        );
        if($data != null) {
          foreach($data as $row) {
            if(isset($row['teamname']) && $row['teamname'] != "") {
              $return['teamname'] = readHash($row['teamname']);
            }
            if(isset($row['avatar']) && $row['avatar'] != null) {
              $return['avatar'] = readHash($row['avatar']);
            }
          }
        }
      }
    } catch (PDOException $e) {

    }
    return $return;
  }
  
  public function getData($field='', $type='', $tried=false) {
    global $auth, $quizSite;
    
    if(isset($_SESSION['userdata'])) {
      if(isset($_SESSION['userdata'][$field])) {
        return readHash($_SESSION['userdata'][$field]);
      } else {
        if($field == 'avatar') {
          return DEFAULT_AVATAR;
        }
      }
    } else {
      if($auth->isLoggedIn()) {
        $db = new quizDB();
        $result = $db->doSelect('select metakey, metavalue from users_data where userid = ?', array($auth->getUserId()));
        $userData = array();
        if(count($result) > 0) {
          foreach($result as $row) {
            $userData[$row['metakey']] = $row['metavalue'];
          }
        }
        $userData['userid'] = $auth->getUserId();
        $userData['siteid'] = $quizSite->siteid;
        $userData['sessionid'] = session_id();
        
        if($this->isQuizMaster() || $this->isAdmin()) {
          $userData['isquizmaster'] = true;
        }
        if($this->isAdmin()) {
          $userData['isadmin'] = true;
        }

        $this->getMongoConnection();
        $collection = $this->mongo->selectCollection('user_sessions');
        $data = $collection->find(
          array('siteid' => array('$eq' => intVal($userData['siteid'])), 'userid' => array('$eq' => intVal($userData['userid'])))
        );
        if($data != null) {
          foreach($data as $row) {
            if(isset($row['teamname']) && $row['teamname'] != "") {
              $userData['teamname'] = $row['teamname'];
            }
            if(isset($row['avatar']) && $row['avatar'] != null) {
              $userData['avatar'] = $row['avatar'];
            }
          }
        }
        $collection->replaceOne(array('siteid' => array('$eq' => intVal($userData['siteid'])), 'sessionid' => array('$eq' => intVal($userData['sessionid']))), $userData, array('upsert' => true));

        $_SESSION['userdata'] = $userData;
      }
    }
    if($field == '') {
      return $_SESSION['userdata'];
    } else if(!$tried) {
      return $this->getData($field, $type, true);
    } else {
      return '';
    }
  }

  public function updateProfilePicture($siteid=null, $userid=null) {
    global $auth, $quizSite, $authDB;

    $oldAvatar = $this->getData('avatar');

    if($siteid == null) {
      $siteid = $quizSite->siteid;
    }
    if($userid == null) {
      $userid = $auth->id();
    }

    $siteid = intval($siteid);
    $userid = intval($userid);

    $image_parts = explode(";base64,", $_POST['image']);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);

    $avatarDir = WEBSITE_DOCROOT . "/sites/" . $siteid . "/user-images/";

    $targetFileName = $this->getData('avatar');
    if($targetFileName == "" || $targetFileName == DEFAULT_AVATAR) {
      $targetFileName = $siteid.date('YmdHi').$userid . "." . $image_type;
    } else {
      @unlink($avatarDir . $targetFileName);
    }
    $avatarFile = $avatarDir . $targetFileName;

    if(file_put_contents($avatarFile, $image_base64)) {
      try {
        $dbFileName = makeHash($targetFileName);
        $stmu = $authDB->prepare('replace into users_data (userid, metakey, metavalue) values (:userid, :metakey, :metavalue)');
        $stmu->bindValue(':userid', $userid, PDO::PARAM_INT);
        $stmu->bindValue(':metakey', "avatar", PDO::PARAM_STR);
        $stmu->bindValue(':metavalue', $dbFileName, PDO::PARAM_STR);
        if($stmu->execute()) {
          $this->getMongoConnection();
          $collection = $this->mongo->selectCollection('user_sessions');
          $collection->updateMany(array('siteid' => ['$eq' => $siteid], 'userid' => ['$eq' => $userid]), array('$set' => ['avatar' => $dbFileName]));
          if($userid == $auth->id()) {
            unset($_SESSION['userdata']);
          }
        }
        return ['type' => 'success', 'url' => '/siteassets/user-images/' . $targetFileName];
      } catch (Exception $e) {
        return ['type' => 'error', 'message' => 'Unable to store updated image.'];
      }
    } else {
      return ['type' => 'error', 'message' => 'Unable to update image.'];
    }
  }

  public function uploadProfilePicture($siteid=null, $userid=null) {
    global $auth, $quizSite, $authDB;

    $oldAvatar = $this->getData('avatar');

    if($siteid == null) {
      $siteid = $quizSite->siteid;
    }
    if($userid == null) {
      $userid = $auth->id();
    }

    $siteid = intval($siteid);
    $userid = intval($userid);

    $target_dir = WEBSITE_DOCROOT . "/sites/" . $siteid . "/user-images/";
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo(basename($_FILES["image"]["name"]),PATHINFO_EXTENSION));
    $targetFileName = $siteid.date('YmdHi').$userid . "." . $imageFileType;
    $target_file = $target_dir . $targetFileName;

    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
      $check = getimagesize($_FILES["image"]["tmp_name"]);
      if($check !== false) {
        $uploadOk = 1;
      } else {
        return ['type' => 'error', 'message' => 'Uploaded file is not an image.'];
        $uploadOk = 0;
      }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
      try {
        unlink($target_file);
      }
      catch (Exception $e){}
    }

    // Check file size
    if ($_FILES["image"]["size"] > 1000000) {
      return ['type' => 'error', 'message' => 'File too large. Maximum file size is 1MB'];
      $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
      return ['type' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are accepted.'];
      $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
      return ['type' => 'error', 'message' => 'Unknown error occurred.'];
    // if everything is ok, try to upload file
    } else {
      if (!is_dir($target_dir)) {
        mkdir($target_dir);
      }
      if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        try {
          $dbFileName = makeHash($targetFileName);
          $stmu = $authDB->prepare('replace into users_data (userid, metakey, metavalue) values (:userid, :metakey, :metavalue)');
          $stmu->bindValue(':userid', $userid, PDO::PARAM_INT);
          $stmu->bindValue(':metakey', "avatar", PDO::PARAM_STR);
          $stmu->bindValue(':metavalue', $dbFileName, PDO::PARAM_STR);
          if($stmu->execute()) {
            $this->getMongoConnection();
            $collection = $this->mongo->selectCollection('user_sessions');
            $collection->updateMany(array('siteid' => ['$eq' => $siteid], 'userid' => ['$eq' => $userid]), array('$set' => ['avatar' => $dbFileName]));
            if($userid == $auth->id()) {
              unset($_SESSION['userdata']);
            }

            if($oldAvatar != "" && $oldAvatar != DEFAULT_AVATAR) {
              @unlink($target_dir . '/' . $oldAvatar);
            }

            return ['type' => 'success', 'url' => '/siteassets/user-images/' . $targetFileName];
          }
        } catch (PDOException $e) {
          return ['type' => 'error', 'message' => 'Unable to save uploaded file in system.'];
        }
      } else {
        return ['type' => 'error', 'message' => 'Unable to save uploaded file.'];
      }
    }
  }

  public function removeProfilePicture($siteid=null, $userid=null) {
    global $auth, $quizSite, $authDB;

    $oldAvatar = $this->getData('avatar');
    
    if($oldAvatar == "" || $oldAvatar == DEFAULT_AVATAR) {
      return ['type' => 'success', 'url' => '/siteassets/user-images/' . DEFAULT_AVATAR];
    }

    if($siteid == null) {
      $siteid = $quizSite->siteid;
    }
    if($userid == null) {
      $userid = $auth->id();
    }

    $siteid = intval($siteid);
    $userid = intval($userid);

    $target_dir = WEBSITE_DOCROOT . "/sites/" . $siteid . "/user-images/";

    try {
      $stmu = $authDB->prepare('delete from users_data where metakey = :metakey and userid = :userid');
      $stmu->bindValue(':userid', $userid, PDO::PARAM_INT);
      $stmu->bindValue(':metakey', "avatar", PDO::PARAM_STR);
      if($stmu->execute()) {
        $this->getMongoConnection();
        $collection = $this->mongo->selectCollection('user_sessions');
        $collection->updateMany(array('siteid' => ['$eq' => $siteid], 'userid' => ['$eq' => $userid]), array('$unset' => ['avatar' => ""]));
        if($userid == $auth->id()) {
          unset($_SESSION['userdata']);
        }

        if($oldAvatar != "") {
          try {
            unlink($target_dir . '/' . $oldAvatar);
          }
          catch (Exception $e) {}
        }

        return ['type' => 'success', 'url' => '/siteassets/user-images/' . DEFAULT_AVATAR];
      }
    } catch (PDOException $e) {
      return ['type' => 'error', 'message' => 'Unable to remove file in system.'];
    }
  }
}
?>