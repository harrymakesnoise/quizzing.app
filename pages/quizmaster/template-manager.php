<?
$management = getURLParam(1);
$managementFile = WEBSITE_DOCROOT . '/pages/quizmaster/templatemanager/' . $management . '.php';

if($management == '' || !file_exists($managementFile)) {
	header("Location: /template-manager/round-configuration");
} else if(file_exists($managementFile)) {
	include_once($managementFile);
	exit;
}