<?
if(!$auth->isLoggedIn()) {
	die(json_encode([
			'data' => [],
			'recordsFiltered' => 0,
			'recordsTotal' => 0,
			'error' => 'Permission denied'
	]));
}

$type         = getURLParam(3, 'all');
$skip         = getCleanRequestParam('start');
$length       = getCleanRequestParam('length');
$order        = (isset($_REQUEST['order']) ? isset($_REQUEST['order'][0]) ? $_REQUEST['order'][0]['dir'] : 'asc' : 'asc');
$search       = (isset($_REQUEST['search']) ? isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '' : '');
$optionPrefix = '/quiz-manager';

if(!$quizUser->isAdmin()) {
	$siteid = $quizSite->siteid;
} else {
	$siteid = getCleanRequestParam('siteid', $quizSite->siteid);
}

function getSiteUsers() {
	global $authDB, $quizUser, $auth, $quizSite, $order, $length, $skip, $siteid;

	$return = array();
	$rowcount = 0;
	$rowtotal = 0;

	$sql = 'SELECT id, m.metavalue, email, roles_mask, status FROM users left outer join users_data m on (m.userid = id and m.metakey = "teamname") where siteid = ' . $siteid . ' ORDER BY id ' . $order . ' LIMIT ' . $skip . ', ' . $length;
	$return['data'] = [];
	try {
		$stmt = $authDB->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$rowcount++;
			$subData = array();
			foreach($row as $key=>$value) {
				$value = $value;
				if($key == 'metavalue') {
					$value = readHash($value);
				} else if($key == 'roles_mask') {
					$value = $quizSite->getRoles($value);
				} else if($key == 'status') {
					$value = $quizSite->getUserStatus($value);
				} else if($key == 'verified') {
					$value = (intVal($value) == 1 ? 'Verified' : 'Not Verified');
				}
				$subData[] = $value;
			}
			$subData[] = '<a href="/user-manager/edit/' . $row['id'] . '" class="btn btn-md btn-success">View <i class="fa fa-eye"></i></a>';

			$return['data'][] = $subData;
		}
	} catch (PDOException $e) {
	}

	$return['draw'] = getCleanRequestParam('draw');
	$return['recordsFiltered'] = $rowcount;
	$return['recordsTotal'] = $rowcount;

	return $return;
}

switch(getURLParam(2)) {
	case 'sites'         : $class = new QuizSites(); break;
	case 'categories'    : $class = new QuizCategories(); $output = $class->get($skip, $length, $order, $search, $type); break;
	case 'quizzes'       : $class = new Quizzes(); break;
	case 'rounds'        : $class = new QuizRounds(); break;
	case 'site-rounds'   : $class = new QuizRounds(); $output = $class->getMinimal($skip, $length, $order, $search, $type, $siteid); break;
	case 'questions'     : $class = new QuizQuestions(); break;
	case 'site-questions': $class = new QuizQuestions(); $output = $class->getMinimal($skip, $length, $order, $search, $type, $siteid); break;
	case 'users'         : $output = getSiteUsers(); break;
	case 'quiz-schedule' : $class = new Quizzes(); $output = $class->getSchedule($type); break;
	case 'templates'     : $type = getURLParam(3) . "|" . getURLParam(4); $class = new QuizTemplateList(); break;
	default              : $output = ['data' => [], 'recordsFiltered' => 0, 'recordsTotal' => 0, 'error' => 'Invalid API reference'];
}

if(!isset($output)) {
	$output = $class->get($skip, $length, $order, $search, $type, $siteid);
}

echo json_encode($output);
?>