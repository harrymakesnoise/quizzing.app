<?
if(!defined('CONFIG_CONSTANTS')) die('Require Constants');

define('FUNCTIONS_DB', true);
class quizDB {
    private $con = null;

    function __construct()
    {
      $this->con = $this->createConnection();
    }
    
    function createConnection() {
      $con = null;
      try {
        $con = new PDO('mysql:dbname=' . SS_DBNAME . ';host=' . SS_DBHOST . ';charset=utf8mb4', SS_DBUSER, SS_DBPASS);
        // set the PDO error mode to exception
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        echo('Error: ' . $e);
      }
      return $con;
    }

    function doDelete($sql, $placeholderValues=array(), $returnHandle=false){
        return $this->handleQuery("DELETE", $sql, $placeholderValues, $returnHandle);
    }

    function doSelect($sql, $placeholderValues=array(), $returnHandle=true, $fetchType=PDO::FETCH_ASSOC) {
        return $this->handleQuery("SELECT",$sql, $placeholderValues, $returnHandle);
    }

    function doInsert($sql, $placeholderValues, $returnHandle=false){
        return $this->handleQuery("INSERT",$sql, $placeholderValues, $returnHandle);
    }

    function doInsertMultiple($sql, $placeholderValues, $returnHandle=false){
        $posValueStart = strpos($sql, "VALUES(") + strlen("VALUES");
        $posValueLength = strpos($sql, ")", $posValueStart) - $posValueStart;
        $rowString = substr($sql, $posValueStart, $posValueLength);
        $placeholderColumnCount = substr_count($sql, "?", $posValueStart, $posValueLength);

        if(!is_array($placeholderValues)) return "VALUES_SHOULD_BE_ARRAY";

        $values = $this->flattenArray($placeholderValues);
        $rowCount = count($values) / $placeholderColumnCount;

        if($rowCount <= 1) return $this->doInsert($sql, $values, $returnHandle);
        if(count($values) % $placeholderColumnCount != 0) return "INCORRECT_AMOUNT_OF_VALUES";

        $updatedSql = $sql . str_repeat(", ".$rowString, $placeholderColumnCount - 1);

        return $this->doInsert($updatedSql, $values, $returnHandle);
    }

    private function flattenArray(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    function doUpdate($sql, $placeholderValues, $returnHandle=false) {
        return $this->handleQuery("UPDATE",$sql, $placeholderValues, $returnHandle);
    }

    function handleQuery($type, $sql, $placeholderValues, $returnHandle=false, $fetchType=PDO::FETCH_ASSOC, $tries=0){
        //Expects $values to be an array, will add the values in order to replace
        // question mark placeholders
        if(!is_array($placeholderValues)) throw new Exception("Values must be an array");

        $placeholderCount = substr_count($sql, "?");
        $valueCount = count($placeholderValues);

        if($placeholderCount != $valueCount) throw new Exception("Placeholders count (".$placeholderCount.") doesn't match the amount of values (".$valueCount.")");

        if($this->con != null) {
            try {
                $stmt = $this->con->prepare($sql);

                for($i = 1;$i <= $valueCount; $i ++){
                    $stmt->bindValue($i, $placeholderValues[$i-1]);
                }

                $stmt->execute();
                if(!$returnHandle) return true;
                if($type == "INSERT" || $type == "UPDATE"){
                    $stmt->lastInsertId = $this->con->lastInsertId();
                    return $stmt;
                } else if($type == "SELECT"){
                    return $stmt->fetchAll($fetchType);
                } else if($type == "DELETE"){
                    return $stmt->rowCount();
                }
            } catch (PDOException $e) {
              if($stmt->errorCode() == 2006) {
                $this->con = $this->createConnection();
                return $this->handleQuery($type, $sql, $placeholderValues, $returnHandle, $fetchType, $tries++);
              } else {
                //$logDir = 'C:\QuizServer\server\logs\\' . date("Y-m-d") . '\\';
                //if(!is_dir($logDir)) {
                //  mkdir($logDir, 777, true);
                //}
                //file_put_contents($logDir . 'sql_errors.txt', print_r($e, true) . PHP_EOL, FILE_APPEND);
                die($e);
              }
            }
        } else {
          if($tries < 5) {
            $this->con = $this->createConnection();
            return $this->handleQuery($type, $sql, $placeholderValues, $returnHandle, $fetchType, $tries++);
          } else {
            //$logDir = 'C:\QuizServer\server\logs\\' . date("Y-m-d") . '\\';
            //if(!is_dir($logDir)) {
            //  mkdir($logDir, 777, true);
            //}
            //file_put_contents($logDir . 'sql_errors.txt', print_r($e, true) . PHP_EOL, FILE_APPEND);
            die($e);
          }
        }
        return false;
    }
}
?>