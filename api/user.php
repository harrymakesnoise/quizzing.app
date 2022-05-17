<?
if((!$quizUser->isAdmin() && !$quizUser->isQuizMaster()) || getCleanRequestParam('userid') == $quizUser->getData('userid')) {
	$thisQuizUser = $quizUser;
	$quizUserData = ["userid" => $quizUser->getData('userid')];
} else {
	$thisQuizUser = new QuizUser();
	$quizUserData = $thisQuizUser->getUserById(getCleanRequestParam('userid'));
	$quizUserData["userid"] = $quizUserData['id'];
}

$output = ['type' => 'error', 'message' => 'Invalid action specified.'];
switch(getURLParam(2)) {
	case 'upload': $output = $thisQuizUser->uploadProfilePicture($quizSite->siteid, $quizUserData['userid']); break;
	case 'update': $output = $thisQuizUser->updateProfilePicture($quizSite->siteid, $quizUserData['userid']); break;
	case 'delete': $output = $thisQuizUser->removeProfilePicture($quizSite->siteid, $quizUserData['userid']); break;
}

echo json_encode($output);