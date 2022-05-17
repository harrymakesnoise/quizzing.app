<?
$management = getURLParam(1);
$managementFile = WEBSITE_DOCROOT . '/pages/quizmaster/quizmanager/' . $management . '.php';

if($management == '' || !file_exists($managementFile)) {
	header("Location: /quiz-manager/quizzes");
} else if(file_exists($managementFile)) {
	include_once($managementFile);
	exit;
}