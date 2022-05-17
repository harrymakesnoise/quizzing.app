<?
if(!defined('CONFIG_CONSTANTS')) echo('Require Constants');

function getCleanRequestParam($paramName, $default='') {
  if(isset($_REQUEST)) {
    foreach($_REQUEST as $param=>$value) {
      $reqParamName = strtolower($param);
      if($reqParamName == strtolower($paramName)) {
        return htmlspecialchars($value);
      }
    }
  }
  return $default;
}

function sortAdminsAlphabetically($a,$b) {
  if($a['displayname']>$b['displayname']) return 1;
  if($a['displayname']==$b['displayname']) return 0;
  return -1;
}


function sortTeamsAlphabetically($a,$b) {
  if($a['teamname']>$b['teamname']) return 1;
  if($a['teamname']==$b['teamname']) return 0;
  return -1;
}

function sortByTime($a,$b) {
  if ($a['time']==$b['time']) return 0;
  return ($a['time']<$b['time'])?-1:1;
}

function sortByScore($a,$b) {
  if(!isset($a['score'])) {
    return 1;
  }

  if ($a['score']==$b['score']) {
    return strcasecmp($a['teamname'], $b['teamname']);
  }
  return ($a['score']>$b['score'])?-1:1;
}

function replaceTree($search="", $replace="", $array=false, $keys_too=false)
{
	if (!is_array($array)) {
		// Regular replace
		return utf8_encode(str_replace($search, $replace, $array));
	}

	$newArr = array();
	foreach ($array as $k=>$v) {
		// Replace keys as well?
		$add_key = $k;
		if ($keys_too) {
			$add_key = str_replace($search, $replace, $k);
		}

		// Recurse
		$newArr[$add_key] = replaceTree($search, $replace, $v, $keys_too);
	}
	return $newArr;
}

function connectDatabase() {
  $conn = null;
  try {
    $conn    = new PDO("mysql:host=" . SS_DBHOST . ":" . SS_DBPORT . ";dbname=" . SS_DBNAME, SS_DBUSER, SS_DBPASS);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    echo('Error: ' . $e);
    file_put_contents('sql_errors.txt', $e . PHP_EOL, FILE_APPEND);
  }
  return $conn;
}

function doSQL($sql, $returnHandle=false) {
  $con = connectDatabase();
  if($con != null) {
    $stmt = $con->prepare($sql);
    try {
      $stmt->execute();
      if(!$returnHandle) return true;
      $stmt->lastInsertId = $con->lastInsertId();
      return $stmt;
    } catch (PDOException $e) {
      echo('Error: ' . $e);
      file_put_contents('sql_errors.txt', $e . PHP_EOL, FILE_APPEND);
    }
  }
  return false;
}

function getSQLData($sql,$allResults=true) {
  $returnData = new stdClass();
  $stmt = doSQL($sql, true);
  $now = new DateTime();
  if($stmt != false && $stmt->rowCount() > 0) {
    // set the resulting array to associative
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);    
    if($allResults) {
      foreach(new RecursiveArrayIterator($stmt->fetchAll()) as $k=>$v) {
        $returnData->$k = $v;
      }
      foreach($returnData as $row=>$fields) {
        $returnData->$row = new stdClass();
        $returnData->$row->lastDBUpdate = $now;
        foreach($fields as $fieldName=>$value) {
          $returnData->$row->$fieldName = $value;
        }
      }
    } else {
      foreach(new RecursiveArrayIterator($stmt->fetch()) as $k=>$v) {
        $returnData->$k = $v;
        $returnData->lastDBUpdate = $now;
      }
    }
  }
  
  return $returnData;
}

function makeHash($data, $key=ENC_KEY) {
  return \mervick\aesEverywhere\AES256::encrypt($data, $key);
}

function readHash($data, $key=ENC_KEY) {
  return \mervick\aesEverywhere\AES256::decrypt($data, $key);
}

function getURLParam($index=0, $default='') {
  $cleanUp = trim($_SERVER['REQUEST_URI'], '/');
  $parts = explode('/', $cleanUp);
  
  if(strval($index) == 'last') {
    return urldecode($parts[count($parts)-1]);
  } else {
    return (isset($parts[$index]) && (strlen($parts[$index]) > 0) ? urldecode($parts[$index]) : $default);
  }
}


function passwordEncrypt($str) {
  return hash('sha512', $str);
}

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

function getRQTypes() {
  $rqTypeArray = array();
  $rqTypeArray[RQTYPE_STANDARD]    = 'Standard Q/A';
  $rqTypeArray[RQTYPE_PICTURE]     = 'Picture';
  $rqTypeArray[RQTYPE_MULTICHOICE] = 'Multiple Choice';
  
  return $rqTypeArray;
}

function getRQType($type='') {
  $rqTypes = getRQTypes();
  
  if(isset($rqTypes[$type])) {
    return $rqTypes[$type];
  }
  
  return $rqTypes[RQTYPE_STANDARD];
}

function writeToast($type, $msg) {
  $_SESSION['toast'] = array('message' => $msg, 'type' => $type);
}

function renderDataTableHeader($showFilters=true, $prefix='/') {
  global $requestedPage, $filter;

  $colSize = 12;
  echo '<div class="row">';
  
  if($showFilters) {
    $colSize = 6;
    echo '
      <div class="col-6 text-left">
        Filters:
        <a href="/' . $requestedPage . $prefix . '" class="btn btn-primary btn-xs ' . ($filter != 'recover' ? 'btn-active' : '') . '">All</a>
        <a href="/' . $requestedPage . $prefix . 'recover" class="btn btn-danger btn-xs ' . ($filter == 'recover' ? 'btn-active' : '') . '">Deleted</a>
      </div>';
  }

  echo '
    <div class="col-' . $colSize . ' text-right">
      <a href="/' . $requestedPage . $prefix. 'new" class="btn btn-success">Create&nbsp;&nbsp;&nbsp;<span class="fas fa-plus"></span></a>
    </div>
    ' . ($filter == 'recover' ? '<p>Deleted items are kept for 1 month until automatically removed permanently.<br />Items that are removed after this time cannot be recovered.</p>' : '') . '
  </div>';
}
?>