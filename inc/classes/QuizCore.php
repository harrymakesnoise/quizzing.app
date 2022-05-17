<?
use MongoDB\Driver\Exception\ConnectionTimeoutException;

class QuizCore {
  public $error = '';
  public $success = '';
  public $mongo = null;
  public $mongoCollection = '';
  public $mongoFilter = array();
  public $mongoFind   = array();
  public $mongoOptions= array();
  
  public function getMongoConnection() {
    $mongoConn = new MongoDB\Client(
    );

    try {
      $dbs = $mongoConn->listDatabases();
      $this->mongo = $mongoConn->quiz;
    } 
    catch (MongoDB\Driver\Exception\ConnectionException $e) {
    }
    catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    }

    if($this->mongo == null) {
      include_once(WEBSITE_DOCROOT . '/service-unavailable.php');
      exit;
    }
    
  }

  public function toArray($nest='') {
    $arr = array();
    $data = ($nest == '' ? $this : $this->$nest);
    foreach($data as $key=>$val) {
      $kcomp = strtolower($key);
      if(strpos($kcomp, 'mongo') !== FALSE || strpos($kcomp, 'sitedataloaded') !== FALSE || $kcomp == 'success' || $kcomp == 'error' || substr($kcomp, 0, 1) == "_" || strpos($kcomp, '$') !== FALSE || strpos($kcomp, 'livedata') !== FALSE) {
        continue;
      }
      $arr[$key] = $val;
    }
    return json_decode(json_encode($arr), true);
  }
  
  public function storeToSession($key, $data) {
    $dataExpiry = new DateTime();
    $dataExpiry->add(new DateInterval('PT10M'));
    
    if(is_array($data) || is_object($data)) {
      $data = serialize($data);
    }
    
    $_SESSION[$key] = array('dataexpiry' => $dataExpiry->format('Y-m-d H:i:s'), 'data' => serialize($data));
  }
  
  public function getFromSession($key) {
    $now = new DateTime();
    if(isset($_SESSION[$key])) {
      $rawData = $_SESSION[$key];
      
      if(isset($rawData['dataexpiry'])) {
        $dataExpiry = new DateTime($rawData['dataexpiry']);
        if($dataExpiry <= $now) {
          return null;
        } else {
          $data = @unserialize($rawData['data']);
          if ($rawData['data'] === 'b:0;' || $data !== false) {
            return unserialize($data);
          } else {
            return $rawData['data'];
          }
        }
      }
    }
    return null;
  }
  
  public function save() {
    if($this->mongo == null) {
      $this->getMongoConnection();
    }
  }
  
  public function remove() {
    if($this->mongo == null) {
      $this->getMongoConnection();
    }
    
  }
  
  public function create() {
    if($this->mongo == null) {
      $this->getMongoConnection();
    }
    
  }

  public function dbScheduleDelete() {
    $deleteDate = new DateTime();
    $deleteDate->add(new DateInterval('P1M'));
    $this->getMongoConnection();
    $set = array('$set' => array('deletion_date' => $deleteDate->format('Y-m-d H:i')));
    if($this->mongo->selectCollection($this->mongoCollection)->findOneAndUpdate($this->mongoFind, $set) != null) {
      writeToast('success', 'Record deleted successfully');
      return true;
    }
    writeToast('error', 'Failed to remove record');
    return false;
  }

  public function dbRestore() {
    $this->getMongoConnection();
    $set = array('$unset' => array('deletion_date' => 1));
    if($this->mongo->selectCollection($this->mongoCollection)->findOneAndUpdate($this->mongoFind, $set) != null) {
      writeToast('success', 'Record restored successfully');
      return true;
    }
    writeToast('error', 'Failed to restore record');
    return false;
  }
  
  public function dbGet($recover=false) {
    $outData = [];
    $outData['data'] = [];

    $this->mongoFind["deletion_date"] = array('$exists' => $recover); 
    $this->getMongoConnection();
    $data = $this->mongo->selectCollection($this->mongoCollection)->find(
        $this->mongoFind,
        $this->mongoOptions
    );

    $outData['recordsFiltered'] = $this->mongo->selectCollection($this->mongoCollection)->count($this->mongoFind);
    $outData['draw'] = getCleanRequestParam('draw');
    if(isset($this->mongoCount) && is_array($this->mongoCount)) {
      $outData['recordsTotal'] = $this->mongo->selectCollection($this->mongoCollection)->count($this->mongoCount);
    } else {
      $outData['recordsTotal'] = $outData['recordsFiltered'];
    }

    return array($data, $outData);
  }
  
  public function getStats() {
    $statData = $this->getFromSession('stats');
    $now = new DateTime();
    if($statData == null) {
      $db = new quizDB();
      $rawStats = $db->doSelect('select s.siteid, s.statkey, s.statvalue from stats s order by s.siteid');
      $lastStatId = 0;
      $stats = array();
      $stats['all'] = array();
      foreach($rawStats as $statRow) {
        if($lastStatId != $statRow['siteid']) {
          $lastStatId = $statRow['siteid'];
          $stats[$statRow['siteid']] = array();
        }
        $key = $statRow['statkey'];
        $val = $statRow['statvalue'];
        $stats[$statRow['siteid']][$key] = $val;
        if(isset($stats['all'][$key])) {
          $stats['all'][$key] += $val;
        } else {
          $stats['all'][$key] = $val;
        }
      }
      $this->storeToSession('stats', $stats);
      return $stats;
    } else {
      return $statData;
    }
  }
  
  static function countQuizSites() {
    $quizCore = new QuizCore;
    $data = $quizCore->getStats();
    return (isset($data['all']['sitecount']) ? $data['all']['sitecount'] : 0);
  }
  
  static function countQuizTeams() {
    $quizCore = new QuizCore;
    $data = $quizCore->getStats();
    return (isset($data['all']['teamcount']) ? $data['all']['teamcount'] : 0);
  }
  
  public function loadData($data) {
    foreach($data as $k=>$v) {
      if($k == "_oid") continue;

      $k = strtolower($k);
      $this->$k = $v;
    }
    $this->siteDataLoaded = (isset($this->siteid) && $this->siteid > 0);
  }
  
  public function getLiveData($field, $type='') {
    if(isset($this->liveData) && isset($this->liveData->$field)) {
      $chosen = $this->liveData->$field;
      if($type == '') {
        return $chosen;
      } else if($type == 'array') {
        if(is_array($chosen)) {
          return $chosen;
        } else {
          return array($chosen);
        }
      } else if($type == 'bool') {
        return boolVal($chosen);
      }
    }
    return '';
  }
  
  public function getData($field, $type='') {
    if($field == "debug") {
      return false;
    }
    if(isset($this->$field)) {
      $chosen = $this->$field;
      if($type == '') {
        return $chosen;
      } else if($type == 'array') {
        if(is_array($chosen)) {
          return $chosen;
        } else {
          return array($chosen);
        }
      } else if($type == 'bool') {
        return boolVal($chosen);
      }
    }
    return '';
  }
}
?>