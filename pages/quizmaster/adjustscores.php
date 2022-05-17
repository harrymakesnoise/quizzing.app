<?
$dev = getCleanRequestParam('dev');

if(!$quizUser->isQuizMaster() && !$quizUser->isAdmin()) {
  header("Location: /");
}

$output = '<base href="' . WEBSITE_HOMEURL . '"><script src="/plugins/jquery/jquery.min.js"></script>';

$currentQuiz = new Quiz($requestedAction);
$currentQuiz->loadAllData();

$quizRounds = [];

if(!isset($currentQuiz->roundData)) {
  die('No round data found');
}

foreach($currentQuiz->roundData as $round) {
  $quizRounds[$round->roundid] = ['label' => $round->label, 'questions' => $round->questionData];
}

if(getCleanRequestParam('adjust') == 'true') {
  $localUserId     = intVal(getCleanRequestParam('userid'));
  $localRoundId    = intVal(getCleanRequestParam('roundid'));
  $localQuestionId = intVal(getCleanRequestParam('questionid'));
  $localScore      = intVal(getCleanRequestParam('score'));
  if(isset($currentQuiz->liveData) && isset($currentQuiz->liveData['teamsanswered'])) {
    if(isset($currentQuiz->liveData['teamsanswered'][$localUserId])) {
      if(isset($currentQuiz->liveData['teamsanswered'][$localUserId][$localRoundId])) {
        if(isset($currentQuiz->liveData['teamsanswered'][$localUserId][$localRoundId][$localQuestionId])) {
          $currentQuiz->liveData['teamsanswered'][$localUserId][$localRoundId][$localQuestionId]['awardedscore'] = $localScore;

          $userData = ['userid' => $localUserId, 'roundid' => $localRoundId, 'questionid' => $localQuestionId, 'awardedscore' => $localScore];

          $res = $currentQuiz->updatePlayerScores($userData);
          if($res['result']) {
            die("SUCCESS|" . $localScore);
          } else {
            die('SUCCESS|' . $res['error']);
          }
        }
      }
    }
  }
  die();
}

if(getCleanRequestParam('publish') == "true") {
  $currentQuiz->publishLeaderboard();
}

$groupQuestions = array();

echo '<form action="" method="post">';
echo 'Question: <select name="questionid">';
foreach($quizRounds as $roundId=>$round) {
  echo '<optgroup label="' . $round['label'] . '">';
  foreach($round['questions'] as $question) {
    $selected = (getCleanRequestParam('questionid') == $roundId . '-' . $question->questionid ? ' selected' : '');
    echo '<option value="' . $roundId . '-' . $question->questionid . '"' . $selected . '>' . $question->label . '</option>';
  }
  echo '</optgroup>';
}
echo '</select><br /><br /><input type="submit" value="Get Answers"></form>';

if(getCleanRequestParam('questionid') != '') {
  $questionIdSplit = explode("-", getCleanRequestParam('questionid'));

  if(!is_array($questionIdSplit) || count($questionIdSplit) < 2) {
    die('Invalid question id');
  }

  $roundId = intVal($questionIdSplit[0]);
  $questionId = intVal($questionIdSplit[1]);
  $thisQuestionData = [];
  
  if($roundId <= 0 || $questionId <= 0) {
    die('Invalid question id');
  }
  if(isset($quizRounds[$roundId]) && isset($quizRounds[$roundId]['questions'])) {
    $key = array_search($questionId, array_column($quizRounds[$roundId]['questions'], 'questionid'));

    if($key !== false) {
      $thisQuestionData = $quizRounds[$roundId]['questions'][$key];
    }

    if((is_array($thisQuestionData) || is_object($thisQuestionData))) {
      $correctAnswers = (is_array($thisQuestionData->correctanswers) ? implode(", ", $thisQuestionData->correctanswers) : $thisQuestionData->correctanswers);
      $output .= "<h3>" . $thisQuestionData->label . '</h3><p>Correct answers: ' . $correctAnswers . '</p>';

      $output .= '<table border="0" width="100%" style="text-align:left;"><tr><th>Team</th><th>Answer</th><th>Score</th></tr>';
      $teamOutput = [];
      if(isset($currentQuiz->liveData) && isset($currentQuiz->liveData['teamsanswered'])) {
        foreach($currentQuiz->liveData['teamsanswered'] as $userId=>$rounds) {
          $teamName = $currentQuiz->liveData['allusers'][$userId]['username'];
          foreach($rounds as $userRoundId=>$userQuestionData) {
            if($userRoundId == $roundId) {
              foreach($userQuestionData as $userQuestionId=>$questionData) {
                if($userQuestionId == $questionId) {
                  $answer = implode(", ", array_merge($questionData['incorrectanswers'], $questionData['correctanswers']));
                  $output .= '<tr><td>' . $teamName . '</td><td>' . $answer . '</td><td><span id="' . $userId .'-score">' . $questionData['awardedscore'] . '</span></td><td><input type="number" value="' . $questionData['awardedscore'] . '" id="' . $userId .'-adjust"><button onClick="adjustAnswerScore(' . $userId . ');">Update</button></td></tr>';
                }
              }
            }
          }
        }
      }
    }
  }
}

echo $output;
?>
</table>

<form action="" method="post">
  <input type="hidden" name="publish" value="true">
  <input type="submit" value="Publish Leaderboard">
</form>

<? if(isset($questionIdSplit)) { ?>
<script>
function adjustAnswerScore(userid) {
  var score = parseInt($("#" + userid + "-adjust").val());
  var toSend = '';
  
  $.post( "/adjustscores/<?=$currentQuiz->quizid?>", {'userid': userid, 'roundid': <?=$roundId?>, 'questionid': <?=$questionId?>, 'score': score, 'adjust': true})
  .done(function( data ) {
    console.log(data);
    var dataParts = data.split('|');
    if(dataParts[0] == 'SUCCESS') {
      $("#" + userid + "-score").html(dataParts[1]);
    }
  });
}
</script>
<? } ?>