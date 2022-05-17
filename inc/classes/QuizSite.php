<?
class QuizSites extends QuizCore {

  public function getRoles($roleMask='') {
    $arr = array();
    $arr[0] = 'Quiz Player';
    //$arr[\Delight\Auth\Role::SUPER_ADMIN] = 'Quizzing.APP Admin';
    //$arr[\Delight\Auth\Role::ADMIN]       = 'Quizzing.APP Staff';
    $arr[\Delight\Auth\Role::SUPER_MODERATOR] = 'Site Administrator';
    $arr[\Delight\Auth\Role::MODERATOR]       = 'Site Moderator';
    if($roleMask == '') {
      return $arr;
    }
    return (isset($arr[$roleMask]) ? $arr[$roleMask] : 'Unknown');
  }

  public function updateOwnUser() {
    global $quizUser, $authDB, $auth, $quizSite;

    $currentPassword = getCleanRequestParam('inputCheckPassword');
    try {
      $currentPasswordAccepted = $auth->reconfirmPassword($currentPassword);
    }
    catch (\Delight\Auth\NotLoggedInException $e) {
      writeToast('error', 'Not logged in.');
      return false;
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      writeToast('error', 'Too many requests, try again later.');
      return false;
    }

    if(!$currentPasswordAccepted) {
      writeToast('error', 'Current password is incorrect.');
      return false; 
    }

    $userid = $auth->id();

    $teamname = makeHash(getCleanRequestParam('inputName'));
    $email    = getCleanRequestParam('inputEmail');
    $newPassword = getCleanRequestParam('inputPassword');
    $confirmNewPassword = getCleanRequestParam('inputConfirmPassword');

    if($newPassword != '') {
      if($newPassword != $confirmNewPassword) {
        writeToast('error', 'New passwords do not match.');
        return false; 
      }

      try {
        $auth->changePassword($currentPassword, $newPassword);
      }
      catch (\Delight\Auth\NotLoggedInException $e) {
        writeToast('error', 'Not logged in.');
        return false;
      }
      catch (\Delight\Auth\InvalidPasswordException $e) {
        writeToast('error', 'Current password is incorrect.');
        return false; 
      }
      catch (\Delight\Auth\TooManyRequestsException $e) {
        writeToast('error', 'Too many requests, try again later.');
        return false;
      }
    }

    $checkEmailMsg = '';
    if($email != $auth->getEmail()) {
      try {
        $auth->changeEmail($_POST['newEmail'], function ($selector, $token) {
          global $quizSite;
          
          $mail = new QuizEmail();
          $mail->sendWelcomeEmail($selector, $token, getCleanRequestParam('teamname'), getCleanRequestParam('email'));
        });
        $checkEmailMsg = ' Your e-mail address will be updated when you click the link we\'ve just sent to your new e-mail address.';
      }
      catch (\Delight\Auth\InvalidEmailException $e) {
        writeToast('error', 'Invalid e-mail address.');
        return false;
      }
      catch (\Delight\Auth\UserAlreadyExistsException $e) {
        writeToast('error', 'E-mail is already registered.');
        return false;
      }
      catch (\Delight\Auth\EmailNotVerifiedException $e) {
        writeToast('error', 'Account not verified.');
        return false;
      }
      catch (\Delight\Auth\NotLoggedInException $e) {
        writeToast('error', 'Not logged in.');
        return false;
      }
      catch (\Delight\Auth\TooManyRequestsException $e) {
        writeToast('error', 'Too many requests, try again later.');
        return false;
      }
    }
    
    $sqlTwo = 'replace into users_data (userid, metakey, metavalue) values (:userid, :metakey, :metavalue)';

    try {
      $stmu = $authDB->prepare($sqlTwo);
      $stmu->bindValue(':userid', $userid, PDO::PARAM_INT);
      $stmu->bindValue(':metakey', "teamname", PDO::PARAM_STR);
      $stmu->bindValue(':metavalue', $teamname, PDO::PARAM_STR);
      if($stmu->execute()) {
        $this->getMongoConnection();
        $collection = $this->mongo->selectCollection('user_sessions');
        $collection->updateMany(array('siteid' => ['$eq' => $quizSite->siteid], 'userid' => ['$eq' => $auth->id()]), array('$set' => ['teamname' => $teamname]));
        writeToast('success', 'User updated successfully.' . $checkEmailMsg);
        return true;
      }
    } catch (PDOException $e) {

    }
    writeToast('error', 'Failed to update user');
    return false;
  }

  public function updateUser($userid) {
    global $quizUser, $authDB;

    $teamname = makeHash(getCleanRequestParam('inputName'));
    $email    = getCleanRequestParam('inputEmail');
    $role     = getCleanRequestParam('inputRoles');
    $status   = getCleanRequestParam('inputStatus');

    $sql = 'update users set email = :email, roles_mask = :rolemask, status = :status where id = :userid';

    if(!$quizUser->isAdmin()) {
      $sql .= ' and siteid = :siteid';
    }
    
    $sqlTwo = 'replace into users_data (userid, metakey, metavalue) values (:userid, :metakey, :metavalue)';

    try {
      $stmt = $authDB->prepare($sql);
      $stmt->bindValue(':email', $email, PDO::PARAM_STR);
      $stmt->bindValue(':rolemask', $role, PDO::PARAM_INT);
      $stmt->bindValue(':status', $status, PDO::PARAM_INT);
      $stmt->bindValue(':userid', $userid, PDO::PARAM_INT);

      if(!$quizUser->isAdmin()) {
        $stmt->bindValue(':siteid', $this->siteid, PDO::PARAM_INT);
      }
      
      if($stmt->execute()) {
        $stmu = $authDB->prepare($sqlTwo);
        $stmu->bindValue(':userid', $userid, PDO::PARAM_INT);
        $stmu->bindValue(':metakey', "teamname", PDO::PARAM_STR);
        $stmu->bindValue(':metavalue', $teamname, PDO::PARAM_STR);
        if($stmu->execute()) {
          $this->getMongoConnection();
          $collection = $this->mongo->selectCollection('user_sessions');
          $updateFilter = array('userid' => ['$eq' => $userid]);
          if(!$quizUser->isAdmin()) {
            $updateFilter['siteid'] = ['$eq' => $this->siteid];
          }
          $collection->updateMany($updateFilter, array('$set' => ['teamname' => $teamname]));
          writeToast('success', 'User updated successfully');
          return true;
        }
      }
    } catch (PDOException $e) {

    }
    writeToast('error', 'Failed to update user');
    return false;
  }

  public function getUserStatus($statusID='') {
    $arr = array();
    $arr[\Delight\Auth\Status::NORMAL] = 'Active';
    $arr[\Delight\Auth\Status::ARCHIVED] = 'Archived';
    $arr[\Delight\Auth\Status::BANNED] = 'Banned';
    $arr[\Delight\Auth\Status::LOCKED] = 'Locked';
    $arr[\Delight\Auth\Status::PENDING_REVIEW] = 'Pending Review';
    $arr[\Delight\Auth\Status::SUSPENDED] = 'Suspended';
    if($statusID == '') {
      return $arr;
    }
    return (isset($arr[$statusID]) ? $arr[$statusID] : 'Unknown');
  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='') {
    if($search == '') {
      $this->mongoFind = array('siteid' => array('$gt' => 0));
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array(
          'siteid' => array('$gt' => 0), 
          '$or' => array(
            array('siteid' => intVal($search)),
            array('siteName' => $regex), 
            array('siteURL' => $regex)
          )
      );
    }
    if($type == 'active') {
      $this->mongoFind['siteEnabled'] = 'on';
    } else if($type == 'expired') {
      $this->mongoFind['siteEnabled'] = array('$ne' => 'on');
    }

    $this->mongoOptions = array(
          'projection' => array('siteid' => 1, 'siteName' => 1, 'siteURL' => 1, 'siteEnabled' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('siteid' => 1) : array('siteid' => -1))
        );

    $this->mongoCollection = 'sites';
    $this->mongoCount = array('siteid' => array('$gt' => 0));

    $fromDB  = $this->dbGet();
    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array();
      $class = "";
      $label = "";
      foreach($row as $k=>$v) {
        if($k == 'siteEnabled') {
          $class = ($v == 'on' ? 'success' : 'danger');
          $label = ($v == 'on' ? 'Active' : 'Disabled');
        } else {
          $subData[] = $v;
        }
      }
      $subData[] = '<label class="badge badge-' . $class . '">' . $label . '</label>';
      $subData[] = '<a href="' . WEBSITE_HOMEURL . '/site-manager/edit/' . $row['siteid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . '/site-manager/delete/' . $row['siteid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }
  
  public function createEdit($siteID) {
    global $quizUser;

    $site                 = new QuizSite(null,false);
    $site->siteid         = intVal($siteID);
    $site->siteName       = getCleanRequestParam('sitename');
    $site->siteOwner      = getCleanRequestParam('siteowner');
    $site->siteURL        = getCleanRequestParam('siteurl');

    if($quizUser->isAdmin()) {
      $site->siteEnabled      = getCleanRequestParam('siteenabled') == 'on';
      $site->maxPlayers       = getCleanRequestParam('maxplayers');
      $site->maxFileStorage   = getCleanRequestParam('sitefilestorage');
      $site->limitAtQuota     = getCleanRequestParam('sitelimitatquota') == 'on';
    } else {
      $site->siteEnabled      = $selectedQuizSite->getData('siteenabled') == 'on';
      $site->maxPlayers       = $selectedQuizSite->getData('maxplayers');
      $site->maxFileStorage   = $selectedQuizSite->getData('maxfilestorage');
      $site->limitAtQuota     = $selectedQuizSite->getData('limitatquota') == 'on';
    }

    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('sites');
    if($site->siteid == '') {
      $lastSite = $collection->findOne(array(), ['sort' => ['siteid' => -1]]);
      if($lastSite != null) {
        $lastSiteId = intVal($lastSite['siteid']);
      } else {
        $lastSiteId = 0;
      }
      $site->siteid = $lastSiteId+1;
    }

    if($collection) {
      $result = $collection->replaceOne(array('siteid' => $site->siteid), $site->toArray(), array('upsert' => true));
      if($result->getModifiedCount() == 1) {
        writeToast('success', 'Site updated successfully');
        return true; 
      } else if($result->getUpsertedId() != null) {
        writeToast('success', 'Site created successfully');
        return true;
      } else {
        writeToast('error', 'Operation failed');
        return false;
      }
    } else {
      writeToast('error', 'Site collection doesnt exist');
      return false;
    }
  }
}

class QuizSite extends QuizSites {
  public $siteid         = 0;
  public $siteEnabled    = 'off';
  public $siteDataLoaded = false;
  
  public function __construct($siteid=null, $loadSite=true) {
    if($siteid == null && $loadSite) {
      $this->getByHostname();
    } else if($siteid != null && $loadSite) {
      $this->getById($siteid);
    }
  }
  
  private function getByHostname() {
    $siteData = $this->getFromSession('sitedata');
    if($siteData == null) {
      $this->getMongoConnection();
      $lastSite = $this->mongo->selectCollection('sites')->findOne(array('siteURL' => WEBSITE_HOSTNAME));
      if($lastSite == null) {
        $siteData = array();
      } else {
        $siteData = json_decode(json_encode($lastSite), true);
      }
      if(count($siteData) > 0) {
        $this->storeToSession('sitedata', $siteData);
      }
    }
    
    $this->loadData($siteData);
  }
  
  private function getById($id) {
    $this->getMongoConnection();
    $lastSite = $this->mongo->selectCollection('sites')->findOne(array('siteid' => $id));
    if($lastSite == null) {
      $siteData = array();
    } else {
      $siteData = json_decode(json_encode($lastSite), true);
    }
    
    $this->loadData($siteData);
  }
  
  public function loadDataRow($row) {
    $this->siteid      = intval($row['siteid']);
    $this->siteEnabled = intval($row['active']);
    
    $key = $row['metakey'];
    $val = $row['metavalue'];
    
    if($key != '') {
      $this->$key = $val;
    }
  }
}
?>