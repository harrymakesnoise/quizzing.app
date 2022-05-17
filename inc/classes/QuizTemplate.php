<?
class QuizTemplateList extends QuizCore {
  public $mongoCollection = "templates";

  public static function getTemplate($type, $id) {
    
  }

  public static function saveTemplate($type, $id) {

  }

  public function get($skip=0, $length=10, $order='asc', $search='', $type='', $siteid='') {
    global $optionPrefix;


    //TODO: Check all of this is available before assuming that we have this data
    $typeSplit = explode("|", $type);
    //Typesplit 0 = Type of Template, 1 = rest of params OR recover
    $outData = array();
    if($search == '') {
      $this->mongoFind = array('$and' => array(array('siteid' => array('$eq' => $siteid)), array('type' => array('$eq' => $typeSplit[0]))));
    } else {
      $regex = new \MongoDB\BSON\Regex($search, 'i');
      $this->mongoFind = array( 
          '$and' => array(
            array('label' => $regex),
            array('siteid' => array('$eq' => $siteid)),
            array('type' => array('$eq' => $typeSplit[0])),
          )
      );
    }
    $this->mongoOptions = array(
        'projection' => array('templateid' => 1, 'label' => 1), 
        'skip' => intVal($skip), 
        'limit' => intVal($length),
        'sort' => ($order == 'asc' ? array('templateid' => 1) : array('templateid' => -1)));

    $fromDB  = $this->dbGet(($typeSplit[1] == 'recover'));

    $data    = (isset($fromDB[0]) ? $fromDB[0] : array());
    $outData = (isset($fromDB[1]) ? $fromDB[1] : array());

    foreach($data as $row) {
      unset($row['_id']);
      $subData = array();
      foreach($row as $k=>$v) {
        $subData[] = $v;
      }
      if($type != 'recover') {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . '/template-manager/edit/' . $row['templateid'] . '" class="btn btn-warning btn-sm">Settings <i class="fas fa-cog"></i></a>&nbsp;&nbsp;<a href="' . WEBSITE_HOMEURL . $optionPrefix . '/quizzes/delete/' . $row['templateid'] . '" class="btn btn-danger btn-sm">Delete <i class="fas fa-times"></i></a>';
      } else {
        $subData[] = '<a href="' . WEBSITE_HOMEURL . '/templates-manager/recover/' . $row['templateid'] . '" class="btn btn-success btn-sm">Restore <i class="fas fa-check"></i></a>';
      }
      $outData['data'][] = json_decode(json_encode($subData), true);
    }

    return $outData;
  }
}