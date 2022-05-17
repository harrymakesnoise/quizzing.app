<?
$action = getURLParam(2, '');
$dt    = new DateTime();
if(!$quizUser->isAdmin() && !$quizUser->isQuizMaster()) {
	$siteid       = $quizSite->siteid;
	$optionPrefix = '/quiz-manager';
  $endTimeObj   = clone $dt;
  $startTimeObj = $dt;
  $endTimeObj->add(new DateInterval("PT30M"));
  $startTime    = $startTimeObj->format(DateTime::ATOM);
  $endTime      = $endTimeObj->format(DateTime::ATOM);
} else {
  $siteid       = (!$quizUser->isAdmin() ? $quizSite->siteid : getCleanRequestParam('siteid', $quizSite->siteid));
  $optionPrefix = '/quiz-manager';
  $startTime    = getCleanRequestParam('start');
  $startTimeObj = new DateTime($startTime);
  $startTime    = $startTimeObj->format(DateTime::ATOM);
  $endTime      = getCleanRequestParam('end');
  $endTimeObj   = new DateTime($endTime);
  $endTime      = $endTimeObj->format(DateTime::ATOM);
}

switch($action) {
	case 'update'    : $class = new Quiz(getCleanRequestParam('quizid')); $output = $class->updateSchedule(); break;
	case 'remove'    : $class = new Quiz(getCleanRequestParam('quizid')); $output = $class->removeSchedule(); break;
	case 'get'       : $class = new Quizzes(); $output = $class->get(0, 999, 'desc', $startTime . '|' . $endTime . '|notended', 'scheduled', $siteid); break;
	default          : die();
}

if(!isset($output)) {
	$output = array();
}

if($action == 'get') {
	if(count($output) > 0) {
		$newOutput = array();
		foreach($output['data'] as $event) {
	  $backgroundColor = "#28a745";
	  $borderColor     = "#28a745";
	  $textColor       = "#FFFFFF";
	  $blockHTML       = '<div class="external-event bg-success" data-quizid="' . $event[0] . '">' . $event[1] . '</div>';

			$newOutput[] = array('title' => $event[1], 'start' => $event[2], 'blockHTML' => $blockHTML, 'quizId' => $event[0], 'backgroundColor' => $backgroundColor, 'borderColor' => $borderColor, 'textColor' => $textColor);
		}
		$output = $newOutput;
	}
}

echo json_encode($output);
?>