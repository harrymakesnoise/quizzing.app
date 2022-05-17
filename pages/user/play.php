<?
include_once(WEBSITE_DOCROOT . '/pages/widgets/chat.php');
include_once(WEBSITE_DOCROOT . '/pages/widgets/leaderboard.php');

$quizid = getURLParam(1);
$quiz = new Quiz($quizid);

if(intVal($quiz->getData('quizid')) > 0 && $quiz->canPlay()) {
  $pageStage = 1;
} else {
  writeToast('warning', 'Active quiz game not found.');
  header("Location: /home");
  die();
}

$headerScripts .= '
<!-- Toastr -->
<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- Countdown -->
<link rel="stylesheet" href="/dist/css/countdown.css">';

$footerScripts .= '
<!-- Toastr -->
<script src="/plugins/toastr/toastr.min.js"></script>
<!-- Server -->
<script>
var userAuth = "' . makeHash(session_id() . '***' . $auth->getUserId() . '***' . $quiz->quizid) . '";
var gameid = ' . $quiz->quizid . ';
</script>
<script src="/dist/js/countdown' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . $quiz->quizid . '"></script>
<script src="/dist/js/player.quiz' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . $quiz->quizid . '"></script>
<script src="/dist/js/connection' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . time() . '"></script>
<script>$(document).ready(function() { connectToServer(); });</script>';

renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?=$quiz->getData('label')?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/home">Home</a></li>
              <li class="breadcrumb-item"><a href="/home">Quizzes</a></li>
              <li class="breadcrumb-item active"><?=$quiz->getData('label')?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container">
        <div class="row" id="mc-player-waiting">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <p class="text-center" id="mc-player-waiting-timer-desc">This game will be starting in...</p>
                <h3 class="text-center" id="mc-player-waiting-timer"></h3>
              </div>
            </div>
          </div>
        </div>
        <div class="row" id="mc-player-gamearea" style="display:none;">
          <div class="col-lg-4">
            <div class="card">
              <div class="overlay">
                <i class="fas fa-sync fa-spin fa-2x"></i>
              </div>
              <div class="card-body">
                <h5 class="mb-2 text-center">Round Key:</h5>
                <div class="card-title row w-100 p-0">
                  <div class="col-md-4" id="mc-roundkey-any" style="display:none;">
                    <button type="button" class="btn btn-block bg-gradient-success">Play Anytime</button>
                  </div>
                  <div class="col-md-4" id="mc-roundkey-current" style="display:none;">
                    <button type="button" class="btn btn-block bg-gradient-info">Current Round</button>
                  </div>
                  <div class="col-md-4" id="mc-roundkey-na" style="display:none;">
                    <button type="button" class="btn btn-block bg-gradient-secondary" disabled>Not Available</button>
                  </div>
                </div>
                <p class="card-text pt-2" id="mc-roundlist" style="display:none;">
                </p>
              </div>
            </div>
          </div>
          <!-- /.col-md-3 -->
          <div class="col-lg-8">
            <div class="card" id="mc-playarea">
              <div class="overlay">
                <i class="fas fa-sync fa-spin fa-2x"></i>
              </div>
              <div class="card-header">
                <h5>Play Area</h5>
              </div>
              <div class="card-body" id="mc-playarea-home" style="display:none;">
                <? /*
                  # Home screen, welcome to quiz - custom message from site owner?
                  # Game status, perhaps timeline (order of round)
                  # Player stats from previous rounds?
                  # How many answers they scored correctly in previous round
                */ ?>
              </div>
              <div class="card-body" id="mc-playarea-alwaysavailable" style="display:none;">
              <? /* Rendered in JS -> this is a round with questions that can be accessed anytime during the quiz */ ?>
              </div>
              <div class="card-body" id="mc-playarea-currentround" style="display:none;">
              <? /* Rendered in JS -> this is a dynamic box, which will contain round info and the current question */ ?>
                <div id="mc-playarea-currentround-header"></div>
                <div class="row">
                  <div class="col-lg-10" id="mc-playarea-currentround-question"></div>
                  <div class="col-lg-2" id="countdown" style="display:none;">
                    <span id="countdown-number"></span>
                    <svg>
                      <circle r="18" cx="20" cy="20"></circle>
                    </svg>
                  </div>
                </div>
              </div>
              <div class="card-body" id="mc-playarea-information">
                <p>
                  Use the round list on the left (or above on mobile) to select a round to play.<br /><br />
                  <span id="mc-playarea-information-alwaysavailable" style="display:none;">A round coloured <span class="badge badge-md badge-success">GREEN</span> can be played at any time (commonly picture rounds!).<br /></span>
                  A round coloured <span class="badge badge-md badge-info">BLUE</span> is the current LIVE round.<br />
                  A round coloured <span class="badge badge-md badge-secondary">GREY</span> is not available to play right now.
                  <br /><br />
                  We'll let you know when a new question / round arrives in the live game.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6">
            <?=renderChatBox()?>
          </div>
          <div class="col-lg-6">
            <?=renderLeaderboard()?>
          </div>
          <!-- /.col-md-8 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
      <div class="modal fade" id="modal-round-alert">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Live Game Update</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p id="modal-round-alert-body"></p>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onClick="toggleRoundPlay(currentRound.roundid); return false;">Go to Live Screen</button>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<script>
// Set the date we're counting down to
var countDownDate = new Date("<?=$quiz->getData('schedule_datetime')?>").getTime();

// Update the count down every 1 second
var x = setInterval(function() {

  // Get today's date and time
  var now = new Date().getTime();

  // Find the distance between now and the count down date
  var distance = countDownDate - now;

  // Time calculations for minutes and seconds
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);

  // Display the result in the element with id="demo"
  var outHTML = "";
  if(minutes > 0) {
    outHTML += minutes + " minute" + (minutes != 1 ? 's' : '') + " ";
  }
  if(seconds > 0) {
    if(minutes > 0) {
      outHTML += " & ";
    }
    outHTML += seconds + " second" + (seconds != 1 ? 's' : '') + " ";
  }

  $("#mc-player-waiting-timer").html(outHTML);

  // If the count down is finished, write some text
  if (distance < 0) {
    clearInterval(x);
    $("#mc-player-waiting-timer-desc").html("Your game will begin shortly.");
    $("#mc-player-waiting-timer").html("");
  }
}, 1000);
</script>
<?
renderSiteFooter();
?>