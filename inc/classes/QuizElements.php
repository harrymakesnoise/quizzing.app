<?
class Quizzes extends QuizCore {
  public function __construct() {
    $this->mongoCollection = 'quizzes';
  }

  public static function getLeaderboards($siteid) {
    $quizzes = new Quizzes();
    $quizzes->mongoCollection = 'leaderboards';
    $leaderboards = [];
    $quizzes->mongoFind = array(
      'siteid' => array('$eq' => intVal($siteid))
    );
    $quizzes->mongoOptions = array(
      'projection' => array('quizid' => 1, 'quizname' => 1, 'pos' => 1, 'datetime' => 1, 'data' => 1), 
      'skip' => intVal(0),
      'sort' => array('datetime' => -1)
    );
    $fromDB  = $quizzes->dbGet();
    $quizzes->mongoCollection = 'quizzes';
    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());

    foreach($data as $row) {
      $leaderboards[] = $row;
    }

    return $leaderboards;
  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    global $optionPrefix;

    $outData = array();
    if($type == 'unscheduled') {
			$this->mongoFind = array(
		    'siteid' => array('$eq' => $siteid), 
		    'schedule_datetime' => array('$exists' => false)
			);
    } else if($type == 'scheduled') {
    	$searchRange = explode("|", $search);
    	$startDate = new DateTime($searchRange[0]);
    	$endDate = new DateTime($searchRange[1]);
      $notEndedOnly = isset($searchRange[2]) && $searchRange[2] == "notended";

			$this->mongoFind = [
        'siteid' => ['$eq' => intval($siteid)],
        '$or' => [
          ['schedule_datetime' => ['$gte' => $startDate->format(DateTime::ATOM), '$lte' => $endDate->format(DateTime::ATOM)]], 
          ['$and' => [
            ['schedule_datetime' => ['$lte' => $startDate->format(DateTime::ATOM)]], 
            ['ended' => ['$exists' => false]],
          ]],
        ],
			];
    } else if($search == '') {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array(
          'siteid' => array('$eq' => $siteid), 
          '$and' => array(
            array('label' => $regex),
          )
      );
    }
    if($type == "scheduled") {
	    $this->mongoOptions = array(
          'projection' => array('quizid' => 1, 'label' => 1, 'schedule_datetime' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('schedule_datetime' => 1) : array('schedule_datetime' => -1)));
    } else {
    	$this->mongoOptions = array(
          'projection' => array('quizid' => 1, 'label' => 1, 'rounds' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('quizid' => 1) : array('quizid' => -1)));
  	}

    $fromDB  = $this->dbGet(($type == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array();
      foreach($row as $k=>$v) {
        if($k == 'rounds') {
          $subData[] = count($v);
          continue;
        }
        $subData[] = $v;
      }
      if($type != 'recover') {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/quizzes/edit/' . $row['quizid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/quizzes/delete/' . $row['quizid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
      } else {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/quizzes/recover/' . $row['quizid'] . '" class="btn btn-success btn-sm">Restore <i class="fas fa-check"></i></a>';
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }

  public function getSchedule($siteid, $next30 = false) {
    $outData = array();

    if(!$next30) {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
    	$nowDT    = new DateTime();
    	$thirtyDT = new DateTime();
    	$thirtyDT->add(new DateInterval('PT30M'));

      $this->mongoFind = array(
        'siteid' => array('$eq' => $siteid), 
        'schedule_datetime' => array('$lte' => $endDate->format(DateTime::ATOM), '$gte' => $startDate->format(DateTime::ATOM)),
        'ended' => array('$exists' => false),
      );
    }
    $this->mongoOptions = array(
          'projection' => array('quizid' => 1, 'label' => 1, 'schedule_datetime' => 1), 
          'skip' => intVal(0)
        );

    $fromDB  = $this->dbGet();

  	return $outData;
  }

  public function delete($id) {
    $this->mongoFind = array('quizid' => intVal($id));
    $this->dbScheduleDelete();
  }

  public function recover($id) {
    $this->mongoFind = array('quizid' => intVal($id));
    $this->dbRestore();
  }
}

class Quiz extends Quizzes {
  public function __construct($quizid=null, $loadQuiz=true) {
    if($quizid != null && $loadQuiz) {
      $this->getById($quizid);
    }
  }
  
  public function canPlay() {
    global $quizUser, $quizSite;
    
    if(!isset($this->schedule_datetime)) {
      return false;
    }
    
    $nowDt = new DateTime();
    $schDt = new DateTime($this->schedule_datetime);
    $schDt->sub(new DateInterval('PT30M'));

    if($nowDt >= $schDt) {
      if($this->getLiveData('ended', "bool")) {
        if(!$quizUser->isQuizMaster()) {
          return false;
        }
      }
      if(intVal($this->getData('siteid')) != intVal($quizSite->getData('siteid'))) {
        return false;
      }
      return true;
    }
    return false;
  }
  
  public function createEdit($quizID, $siteID) {
    $quiz                 = new Quiz(null,false);
    $quiz->quizid         = intVal($quizID);
    $quiz->siteid         = intVal($siteID);
    $quiz->label          = getCleanRequestParam('label');
    $quiz->rounds         = (getCleanRequestParam('rounds') != '' ? explode(",", getCleanRequestParam('rounds')) : array());
    $quiz->roundmodifiers = htmlspecialchars_decode(getCleanRequestParam('roundmods'));

    if($quiz->label == '') {
      writeToast('error', 'Quiz name is required');
      return false;
    }

    $roundModifiers = json_decode($quiz->roundmodifiers, true);
    if($roundModifiers === null || $roundModifiers === false) {
      writeToast('error', 'Invalid round modifiers, please try again.');
      return false;
    }
    $roundModifiers["default"] = [];
    $roundModifiers["default"]["round-mod-always-available"] = false;
    $roundModifiers["default"]["round-mod-auto-scorer"] = false;
    $roundModifiers["default"]["round-mod-end-after"] = "auto";
    $roundModifiers["default"]["round-mod-endroundafter-value"] = "0";
    $roundModifiers["default"]["round-mod-score-correct"] = "1";
    $roundModifiers["default"]["round-mod-score-incorrect"] = "0";

    $this->roundmodifiers = json_encode($roundModifiers);
    
    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('quizzes');
    if($quiz->quizid == 0) {
      $lastQuiz = $collection->findOne(array(), ['sort' => ['quizid' => -1]]);
      if($lastQuiz != null) {
        $lastQuizId = intVal($lastQuiz['quizid']);
      } else {
        $lastQuizId = 0;
      }
      $quiz->quizid = $lastQuizId+1;
    }

    if($collection) {
      $result = $collection->replaceOne(array('quizid' => $quiz->quizid, 'siteid' => $quiz->siteid), $quiz->toArray(), array('upsert' => true));
      if($result->getModifiedCount() == 1) {
        writeToast('success', 'Quiz updated successfully');
        return true; 
      } else if($result->getUpsertedId() != null) {
        writeToast('success', 'Quiz created successfully');
        return true;
      } else {
        writeToast('error', 'Operation failed');
        return false;
      }
    } else {
      writeToast('error', 'Quiz collection doesnt exist');
      return false;
    }
  }

  public function updateSchedule() {
  	$this->schedule_datetime = getCleanRequestParam('schedule_datetime');

    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('quizzes');

    $dbResult = false;
    $dbError = '';

    if($collection) {
      $result = $collection->replaceOne(array('quizid' => intval($this->quizid)), $this->toArray(), array('upsert' => true));
      if($result->getModifiedCount() == 1) {
        $dbResult = true; 
      } else if($result->getUpsertedId() != null) {
        $dbResult = true;
      } else {
      	$dbError = 'Failed to update schedule';
      }
    } else {
    	$dbError = 'Collection does not exist';
    }
    return array('result' => $dbResult, 'error' => $dbError);
  }

  public function updatePlayerScores($userData) {
    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('current_game_data');
    $dbResult = false;
    $dbError = '';

    if($collection) {
      $newData = ['$set' => ['teamsanswered.' . $userData['userid'] . '.' . $userData['roundid'] . '.' . $userData['questionid'] . '.awardedscore' => intVal($userData['awardedscore'])]];

      $result = $collection->updateOne(['gameid' => $this->quizid, 'siteid' => $this->siteid], $newData);

      if($result->getModifiedCount() == 1) {
        $dbResult = true; 
      } else {
        $dbError = 'Failed to update';
      }
    } else {
      $dbError = 'Collection does not exist';
    }
    return array('result' => $dbResult, 'error' => $dbError);
  }

  public function publishLeaderboard() {
    if(!isset($this->liveData)) {
      return false;
    }
    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('leaderboards');
    $dbResult = false;
    $dbError = '';

    if($collection) {
      $leaderboard = ['gameid' => $this->quizid, 'siteid' => $this->siteid, 'quizname' => $this->label, 'datetime' => $this->schedule_datetime, 'data' => []];
      foreach($this->liveData['teamsanswered'] as $teamId=>$roundData) {
        $team = ['id' => $teamId, 'teamname' => $this->liveData['allusers'][$teamId]['username'], 'score' => 0];
        foreach($roundData as $roundQuestions) {
          foreach($roundQuestions as $questionId=>$scoreData) {
            $team['score'] += intVal($scoreData['awardedscore']);
          }
        }
        $leaderboard['data'][] = $team;
      }
      usort($leaderboard['data'], function($a, $b) {
        if($a['score'] > $b['score'])
          return -1;

        if($a['score'] < $b['score'])
          return 1;

        if($a['score'] == $b['score']) {
          return strcmp(strtolower($a['teamname']), strtolower($b['teamname']));
        }
      });
      $lastPos = 0;
      $lastScore = 0;
      foreach($leaderboard['data'] as &$row) {
        if($lastScore != $row['score']) {
          $lastScore = $row['score'];
          $lastPos++;
          $row['pos'] = $lastPos;
        } else {
          $row['pos'] = "";
        }
      }

      $result = $collection->replaceOne(['gameid' => $this->quizid, 'siteid' => $this->siteid], $leaderboard, ['upsert' => true]);

      if($result->getModifiedCount() == 1) {
        $dbResult = true; 
      } else {
        $dbError = 'Failed to update';
      }
    } else {
      $dbError = 'Collection does not exist';
    }
    return array('result' => $dbResult, 'error' => $dbError);
  }

  public function removeSchedule() {
    $dbResult = false;
    $dbError = 'Event already unscheduled';

  	if(isset($this->schedule_datetime)) {
  		unset($this->schedule_datetime);
	    $this->getMongoConnection();
	    $collection = $this->mongo->selectCollection('quizzes');

	    if($collection) {
	      $result = $collection->replaceOne(array('quizid' => $this->quizid), $this->toArray(), array('upsert' => true));
	      if($result->getModifiedCount() == 1) {
	        $dbResult = true; 
	      } else if($result->getUpsertedId() != null) {
	        $dbResult = true;
	      } else {
	      	$dbError = 'Failed to update schedule';
	      }
	    } else {
	    	$dbError = 'Collection does not exist';
	    }
  	}

    return array('result' => $dbResult, 'error' => $dbError);
  }

  private function getById($id) {
    $this->getMongoConnection();
    $quizId = $this->mongo->selectCollection('quizzes')->findOne(array('quizid' => intVal($id)));
    if($quizId == null) {
      $quizData = array();
    } else {
      $quizData = json_decode(json_encode($quizId), true);
    }

    $liveId = $this->mongo->selectCollection('current_game_data')->findOne(array('gameid' => intVal($id)));
    if($liveId == null) {
      $liveData = array();
    } else {
      $liveData = json_decode(json_encode($liveId), true);
    }

    $outData = $quizData;
    $this->liveData = $liveData;

    $this->loadData($outData);
  }

  public function loadAllData() {
    if(!(isset($this->quizid) && $this->quizid > 0)) {
      return false;
    }

    $roundData = new QuizRounds();
    $questionData = new QuizQuestions();
    $this->roundData = $roundData->getMany($this->rounds);

    if($this->roundData != null) {
      foreach($this->roundData as $id=>$round) {
        $this->roundData[$id]->questionData = $questionData->getMany($round->questions);
      }
    }
  }
}

class QuizRounds extends QuizCore {
  public function __construct() {
    $this->mongoCollection = 'rounds';
  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    global $optionPrefix;

    $outData = array();
    if($search == '') {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array(
          'siteid' => array('$eq' => $siteid), 
          '$or' => array(
            array('label' => $regex),
          )
      );
    }
    $this->mongoOptions = array(
          'projection' => array('roundid' => 1, 'label' => 1, 'questions' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('roundid' => 1) : array('roundid' => -1)));

    $fromDB  = $this->dbGet(($type == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array();
      foreach($row as $k=>$v) {
        if($k == 'questions') {
          $subData[] = count($v);
          continue;
        }
        $subData[] = $v;
      }
      if($type != 'recover') {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/rounds/edit/' . $row['roundid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/rounds/delete/' . $row['roundid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
      } else {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/rounds/recover/' . $row['roundid'] . '" class="btn btn-success btn-sm">Restore <i class="fas fa-check"></i></a>';
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }  

  public function getMinimal($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    $outData = array();
    if($search == '') {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
      $strRegex = new \MongoDB\BSON\Regex($search, 'i');
      $intRegex = new \MongoDB\BSON\Regex(intVal($search), 'i');
      $this->mongoFind = array(
          'siteid' => array('$eq' => $siteid), 
          '$or' => array(
            array('roundid' => intVal($search)),
            array('label' => $strRegex),
          )
      );
    }
    $this->mongoOptions = array(
          'projection' => array('roundid' => 1, 'label' => 1, 'questions' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('roundid' => 1) : array('roundid' => -1)));

    $fromDB  = $this->dbGet(($type == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array('<input type="checkbox" class="icheck" id="checkbox-round-' . $row['roundid'] . '" readonly>');
      foreach($row as $k=>$v) {
        if($k == 'questions') {
          $v = count($v);
        }
        $subData[] = $v;
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }

  public function getMany($ids) {
    $this->getMongoConnection();
    $output = array();
    $search = array();
    if(!is_array($ids)) {
      $ids = array($ids);
    }
    foreach($ids as $id) {
      $search[] = array('roundid' => intVal($id));
    }
    if(count($search) > 0) {
      $rounds = $this->mongo->selectCollection('rounds')->find(array('$or' => $search));
      if($rounds != null) {
        foreach($rounds as $roundDoc) {
          $r = new QuizRound();
          $r->loadData($roundDoc);
          $id = array_search($r->roundid, $ids);
          $output[$id] = $r;
        }
      }
    }
    ksort($output);
    return $output;
  }

  public function delete($id) {
    $this->mongoFind = array('roundid' => intVal($id));
    $this->dbScheduleDelete();
  }

  public function recover($id) {
    $this->mongoFind = array('roundid' => intVal($id));
    $this->dbRestore();
  }
}

class QuizRound extends QuizRounds {
  public function __construct($roundid=null, $loadRound=true) {
    if($roundid != null && $loadRound) {
      $this->getById($roundid);
    }
  }
  
  public function createEdit($roundID, $siteID) {
    $round                 = new QuizRound(null,false);
    $round->roundid        = intVal($roundID);
    $round->siteid         = intVal($siteID);
    $round->label          = getCleanRequestParam('label');
    $round->questions      = (getCleanRequestParam('questions') != '' ? explode(",", getCleanRequestParam('questions')) : array());

    if($round->label == '') {
      writeToast('error', 'Round name is required');
      return false;
    }
    
    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('rounds');
    if($round->roundid == '') {
      $lastRound = $collection->findOne(array(), ['sort' => ['roundid' => -1]]);
      if($lastRound != null) {
        $lastRoundId = intVal($lastRound['roundid']);
      } else {
        $lastRoundId = 0;
      }
      $round->roundid = $lastRoundId+1;
    }

    if($collection) {
      $result = $collection->replaceOne(array('roundid' => $round->roundid), $round->toArray(), array('upsert' => true));
      if($result->getModifiedCount() == 1) {
        writeToast('success', 'Round updated successfully');
        return true; 
      } else if($result->getUpsertedId() != null) {
        writeToast('success', 'Round created successfully');
        return true;
      } else {
        writeToast('error', 'Operation failed');
        return false;
      }
    } else {
      writeToast('error', 'Round collection doesnt exist');
      return false;
    }
  }

  public function loadRoundData() {
    $this->getById($this->roundid);
  }

  private function getById($id) {
    $this->getMongoConnection();
    $roundId = $this->mongo->selectCollection('rounds')->findOne(array('roundid' => intVal($id)));
    if($roundId == null) {
      $roundData = array();
    } else {
      $roundData = json_decode(json_encode($roundId), true);
    }
    
    $this->loadData($roundData);
  }
}

class QuizQuestions extends QuizCore {
  public function __construct() {
    $this->mongoCollection = 'questions';
  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    global $optionPrefix;

    $outData = array();

    if($search == '') {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array(
          'siteid' => array('$eq' => $siteid), 
          '$or' => array(
            array('label' => $regex),
          )
      );
    }
    $this->mongoOptions = array(
          'projection' => array('questionid' => 1, 'label' => 1, 'category' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('questionid' => 1) : array('questionid' => -1)));

    $fromDB  = $this->dbGet(($type == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array();
      foreach($row as $k=>$v) {
        $subData[] = $v;
      }
      if($type != 'recover') {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/questions/edit/' . $row['questionid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/questions/delete/' . $row['questionid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
      } else {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/questions/recover/' . $row['questionid'] . '" class="btn btn-success btn-sm">Restore <i class="fas fa-check"></i></a>';
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }

  public function getMinimal($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    $outData = array();
    if($search == '') {
      $this->mongoFind = array('siteid' => array('$eq' => $siteid));
    } else {
      $strRegex = new \MongoDB\BSON\Regex($search, 'i');
      $intRegex = new \MongoDB\BSON\Regex(intVal($search), 'i');
      $this->mongoFind = array(
          'siteid' => array('$eq' => $siteid), 
          '$or' => array(
            array('questionid' => intVal($search)),
            array('label' => $strRegex),
            array('category' => $strRegex),
          )
      );
    }
    $this->mongoOptions = array(
          'projection' => array('questionid' => 1, 'label' => 1, 'category' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('questionid' => 1) : array('questionid' => -1)));

    $fromDB  = $this->dbGet(($type == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array('<input type="checkbox" class="icheck" id="checkbox-question-' . $row['questionid'] . '" readonly>');
      foreach($row as $k=>$v) {
        $subData[] = $v;
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }

  public function getMany($ids) {
    $this->getMongoConnection();
    $output = array();
    $search = array();
    if(!is_array($ids)) {
      $ids = array($ids);
    }
    foreach($ids as $id) {
      $search[] = array('questionid' => intVal($id));
    }
    if(count($search) > 0) {
      $questions = $this->mongo->selectCollection('questions')->find(array('$or' => $search));
      if($questions != null) {
        foreach($questions as $questionDoc) {
          $q = new QuizQuestion();
          $q->loadData($questionDoc);
          $id = array_search($q->questionid, $ids);
          $output[$id] = $q;
        }
      }
    }
    ksort($output);
    return $output;
  }

  public function delete($id) {
    $this->mongoFind = array('questionid' => intVal($id));
    $this->dbScheduleDelete();
  }

  public function recover($id) {
    $this->mongoFind = array('questionid' => intVal($id));
    $this->dbRestore();
  }
}

class QuizCategories extends QuizCore {
  public function __construct() {
    $this->mongoCollection = 'question_categories';
  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='', $forSelectList=false) {
    global $optionPrefix;

    if($search == '') {
      $this->mongoFind = array();
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array(
          '$or' => array(
            array('categorylabel' => $regex),
          )
      );
    }
    $this->mongoOptions = array(
          'projection' => array('categoryid' => 1, 'categorylabel' => 1), 
          'skip' => intVal($skip), 
          'limit' => intVal($length),
          'sort' => ($order == 'asc' ? array('categoryid' => 1) : array('categoryid' => -1)));

    $fromDB  = $this->dbGet(($type == 'recover'));
    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      if($forSelectList) {
        $outData['data'][$row['categoryid']] = $row['categorylabel'];
        continue;
      }
      $subData = array();
      foreach($row as $k=>$v) {
        $subData[] = $v;
      }
      if(!$forSelectList) {
       if($type != 'recover') {
          $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/categories/edit/' . $row['categoryid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/categories/delete/' . $row['categoryid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
        } else {
          $subData[] = '<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/categories/recover/' . $row['categoryid'] . '" class="btn btn-success btn-sm">Restore <i class="fas fa-check"></i></a>';
        }
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }

  public function delete($id) {
    $this->mongoFind = array('categoryid' => intVal($id));
    $this->dbScheduleDelete();
  }

  public function recover($id) {
    $this->mongoFind = array('categoryid' => intVal($id));
    $this->dbRestore();
  }
}

class QuizCategory extends QuizCategories {
  public function __construct($categoryId=null, $loadCategory=true) {
    if($categoryId != null && $loadCategory) {
      $this->getById($categoryId);
    }
  }

  private function getById($id) {
    $this->getMongoConnection();
    $categoryId = $this->mongo->selectCollection('question_categories')->findOne(array('categoryid' => intVal($id)));
    if($categoryId == null) {
      $categoryData = array();
    } else {
      $categoryData = json_decode(json_encode($categoryId), true);
    }
    
    $this->loadData($categoryData);
  }

}

class QuizQuestion extends QuizQuestions {
  public $questionid = 0;

  public function __construct($questionid=null, $loadQuestion=true) {
    if($questionid != null && $loadQuestion) {
      $this->getById($questionid);
    }
  }
  
  public function createEdit($questionID, $siteID) {
    $question                 = new QuizQuestion(null,false);
    $question->questionid     = intVal($questionID);
    $question->siteid         = intVal($siteID);
    $question->label          = getCleanRequestParam('question');
    $question->category       = getCleanRequestParam('questioncategory');
    $question->answers        = (isset($_REQUEST['questionanswer']) ? $_REQUEST['questionanswer'] : array());
    $question->correctanswers = (isset($_REQUEST['questioncorrect']) ? $_REQUEST['questioncorrect'] : array());
    $question->questionimage  = getCleanRequestParam('question-image');
    $question->answerimages   = (isset($_REQUEST['answer-image']) ? $_REQUEST['answer-image'] : array());
    

    if(($question->label == "")) {
      writeToast('error', 'Questions require a label.');
      return false;
    }

    if(($question->answers == "") || (is_array($question->answers) && (count($question->answers) == 0 || $question->answers[0] == ""))) {
      writeToast('error', 'Questions require answers.');
      return false;
    }
    
    if(($question->correctanswers == "") || (is_array($question->correctanswers) && (count($question->correctanswers) == 0 || $question->correctanswers[0] == ""))) {
      writeToast('error', 'Questions require at least one correct answer.');
      return false;
    }

    $this->getMongoConnection();
    $collection = $this->mongo->selectCollection('questions');
    if($question->questionid == '') {
      $lastQuestion = $collection->findOne(array(), ['sort' => ['questionid' => -1]]);
      if($lastQuestion != null) {
        $lastQuestionId = intVal($lastQuestion['questionid']);
      } else {
        $lastQuestionId = 0;
      }
      $question->questionid = $lastQuestionId+1;
    }

    if($collection) {
      $result = $collection->replaceOne(array('questionid' => $question->questionid), $question->toArray(), array('upsert' => true));
      if($result->getModifiedCount() == 1) {
        writeToast('success', 'Question updated successfully');
        return true; 
      } else if($result->getUpsertedId() != null) {
        writeToast('success', 'Question created successfully');
        return true;
      } else {
        writeToast('error', 'Operation failed');
        return false;
      }
    } else {
      writeToast('error', 'Question collection doesnt exist');
      return false;
    }
  }

  public function loadQuestionData() {
    $this->getById($this->questionid);
  }

  private function getById($id) {
    $this->getMongoConnection();
    $questionId = $this->mongo->selectCollection('questions')->findOne(array('questionid' => intVal($id)));
    if($questionId == null) {
      $questionData = array();
    } else {
      $questionData = json_decode(json_encode($questionId), true);
    }
    
    $this->loadData($questionData);
  }
}
?>