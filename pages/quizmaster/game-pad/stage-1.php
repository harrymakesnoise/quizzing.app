<?
include_once(WEBSITE_DOCROOT . '/pages/widgets/leaderboard.php');
include_once(WEBSITE_DOCROOT . '/pages/widgets/chat.php');
include_once(WEBSITE_DOCROOT . '/pages/widgets/teamsanswered.php');
$headerScripts .= '<!-- Select2 -->
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<!-- Toastr -->
<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">';
$footerScripts .= '<!-- Select2 -->
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>
  $(".select2bs4").select2({
    theme: "bootstrap4"
  });
</script>
<!-- Toastr -->
<script src="/plugins/toastr/toastr.min.js"></script>
<!-- Server -->
<script>
var userAuth = "' . makeHash(session_id() . '***' . $auth->getUserId() . '***' . $quiz->quizid) . '";
var gameid = ' . $quiz->quizid . ';
</script>
<script src="/dist/js/admin.quiz' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=123' . $quiz->quizid . '"></script>
<script src="/dist/js/connection' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . time() . '"></script>
<script>$(document).ready(function() { connectToServer(); });</script>';
$headerScripts .= '<!-- Bootstrap Toggle --><link rel="stylesheet" href="/plugins/bootstrap-toggle/bootstrap-toggle.min.css">';
$footerScripts .= '<!-- Bootstrap Toggle --><script src="/plugins/bootstrap-toggle/bootstrap-toggle.min.js"></script>';

$footerScripts .= '
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
  var questionAnswersTable = $("#mc-questionanswers").DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "rowReorder": true,
    "columnDefs": [
        { "orderable": true, "targets": "_all" },
        { "width": "5%", "targets": 0 },
        { "width": "30%", "targets": 1 },
        { "width": "30%", "targets": 2 },
        { "width": "18%", "targets": 3, "orderable": false },
    ],
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "createdRow": function ( row, data, index ) {
        $(row).attr("data-teamid", parseInt(data[0]));
        $($(row).find("td")[1]).attr("data-fieldname", "username");
      },
  });
</script>';
function renderGamePad() {
  global $quiz;
  ?>
<div class="row">
  <div class="col-12">
    <div class="card" id="mainControlWrapper">
      <div class="sendtosite-control-overlay overlay dark">
        <i class="fas fa-2x fa-sync fa-spin"></i>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12">
            <ul class="nav nav-tabs" id="vert-tabs-tab" role="tablist">
              <li class="nav-item"><a class="nav-link active" id="vert-tabs-mc-tab" data-toggle="pill" href="#vert-tabs-mc" role="tab" aria-controls="vert-tabs-mc" aria-selected="true">Main Control</a></li>
              <li class="nav-item"><a class="nav-link" id="vert-tabs-sc-tab" data-toggle="pill" href="#vert-tabs-sc" role="tab" aria-controls="vert-tabs-sc" aria-selected="false">Override Scoring</a></li>
              <li class="nav-item"><a class="nav-link" id="vert-tabs-qd-tab" data-toggle="pill" href="#vert-tabs-qd" role="tab" aria-controls="vert-tabs-qd" aria-selected="false">Question Info</a></li>
              <li class="nav-item"><a class="nav-link" id="vert-tabs-tl-tab" data-toggle="pill" href="#vert-tabs-tl" role="tab" aria-controls="vert-tabs-tl" aria-selected="false">Quiz Timeline</a></li>
            </ul>
            <?/*
            <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link active" id="vert-tabs-mc-tab" data-toggle="pill" href="#vert-tabs-mc" role="tab" aria-controls="vert-tabs-mc" aria-selected="true">Main Control</a>
              <a class="nav-link" id="vert-tabs-sc-tab" data-toggle="pill" href="#vert-tabs-sc" role="tab" aria-controls="vert-tabs-sc" aria-selected="false">Override Scoring</a>
              <a class="nav-link" id="vert-tabs-qd-tab" data-toggle="pill" href="#vert-tabs-qd" role="tab" aria-controls="vert-tabs-qd" aria-selected="false">Question Info</a>
              <a class="nav-link" id="vert-tabs-tl-tab" data-toggle="pill" href="#vert-tabs-tl" role="tab" aria-controls="vert-tabs-tl" aria-selected="false">Quiz Timeline</a>
            </div>*/?>
            <div class="tab-content p-3" id="vert-tabs-tabContent">
              <div class="tab-pane text-left fade show active" id="vert-tabs-mc" role="tabpanel" aria-labelledby="vert-tabs-mc-tab">
                <div class="row mb-2">
                  <div class="col-12 text-right">
                    <div class="form-group clearfix">
                      <div class="icheck-danger d-inline">
                        <input type="checkbox" id="mc-auto-confirm-commands">
                        <label for="mc-auto-confirm-commands">
                          Auto-Confirm Commands
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-sm-12 col-md-2 align-middle">
                    <label>Game Control</label>
                  </div>
                  <div class="col-sm-12 col-md-7 align-middle">
                    <?=$quiz->getData('label')?>
                  </div>
                  <div class="col-sm-12 col-md-3 align-middle text-right">
                    <div class="btn-group w-100">
                      <button id="mc-gametoggle-start" data-controls="sendtosite-quiz" data-disable='{"started": true, "ended": true, "round": true, "question": true, "timerrunning": true}' data-label="Start Game" class="btn btn-success" disabled>Start</button>
                      <button id="mc-gametoggle-stop" data-controls="sendtosite-quiz" data-disable='{"started": false, "ended": true, "round": true, "question": true, "timerrunning": true}' data-label="End Game" class="btn btn-danger" disabled>Stop</button>
                    </div>
                  </div>
                </div>
                <div class="row mb-2 align-middle">
                  <div class="col-sm-12 col-md-2 align-middle">
                    <label>Round Control</label>
                  </div>
                  <div class="col-sm-12 col-md-7 align-middle">
                    <select id="mc-roundlist" class="select2bs4 full-width form-control" data-disable='{"started": false, "ended": true, "round": true, "timerrunning": true}' data-dropdown-css-class="no-search" data-placeholder="Round Selector">
                      <option></option>
                    </select>
                  </div>
                  <div class="col-sm-12 col-md-3 align-middle text-right">
                    <div class="btn-group w-100">
                      <button id="mc-roundtoggle-start" data-extradata="mc-roundlist" data-disable='{"started": false, "ended": true, "round": true, "timerrunning": true}' data-controls="sendtosite-round" data-label="Start Round" class="btn btn-success" disabled>Start</button>
                      <button id="mc-roundtoggle-stop" data-extradata="mc-roundlist" data-disable='{"started": false, "ended": true, "round": false, "timerrunning": true}' data-controls="sendtosite-round" data-label="End Round" class="btn btn-danger" disabled>Stop</button>
                    </div>
                  </div>
                </div>
                <div class="row mb-2 align-middle">
                  <div class="col-sm-12 col-md-2 align-middle">
                    <label>Question Control</label>
                  </div>
                  <div class="col-sm-12 col-md-7 align-middle">
                    <select id="mc-questionlist" class="select2bs4 full-width form-control" data-disable='{"started": false, "ended": true, "round": false, "question": true, "timerrunning": true}' data-dropdown-css-class="no-search" data-placeholder="Question Selector">
                      <option></option>
                    </select>
                  </div>
                  <div class="col-sm-12 col-md-3 align-middle text-right">
                    <div class="btn-group w-100">
                      <button id="mc-questiontoggle-send" data-extradata="mc-questionlist" data-disable='{"started": false, "ended": true, "round": false, "question": true, "timerrunning": true}' data-controls="sendtosite-question" data-label="Send Question" class="btn btn-primary" disabled>Send</button>
                      <button id="mc-questiontoggle-clear" data-controls="sendtosite-question" data-disable='{"started": false, "ended": true, "round": false, "question": false, "timerrunning": true}' data-label="Clear Question" class="btn btn-secondary" disabled>Clear</button>
                    </div>
                  </div>
                </div>
                <div class="row mb-2 align-middle">
                  <div class="col-sm-12 col-md-2 align-middle">
                    <label>Question Timer</label>
                  </div>
                  <div class="col-sm-12 col-md-7 align-middle">
                    <div class="input-group input-group-xs mb-3">
                      <input type="number" id="mc-questiontimer" min="0" max="120" placeholder="Seconds" class="form-control" data-disable='{"started": false, "ended": true, "round": false, "question": false, "timerrunning": true}'>
                      <div class="input-group-append">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-disable='{"started": false, "ended": true, "round": false, "question": false, "timerrunning": true}'>
                          End
                        </button>
                        <ul class="dropdown-menu">
                          <li class="dropdown-item active" id="mc-questiontimer-opt1"><a href="" class="text-white" onClick="changeQuestionTimer(1);return false;">Clear Current Question</a></li>
                          <li class="dropdown-item" id="mc-questiontimer-opt2"><a href="" onClick="changeQuestionTimer(2);return false;">Send Next Question</a></li>
                          <li class="dropdown-item" id="mc-questiontimer-opt3"><a href="" onClick="changeQuestionTimer(3);return false;">End Current Round</a></li>
                        </ul>
                      </div>
                      <!-- /btn-group w-100 -->
                    </div>
                    <!-- /input-group -->
                  </div>
                  <input type="hidden" id="mc-questiontimer-summary">
                  <div class="col-sm-12 col-md-3 align-middle text-right">
                    <button id="mc-questiontimer-send" data-extradata="mc-questiontimer-summary" data-disable='{"started": false, "ended": true, "round": false, "question": false, "timerrunning": true}' data-controls="sendtosite-questiontimer" data-label="Start Timer" class="btn btn-warning" style="width:100%" disabled>&nbsp;Start&nbsp;</button><br /><br />
                    <?/*<a href="" onClick="resetControlButtons(seenGameData);return false;" class="btn btn-info" style="width:100%">&nbsp;Reset Buttons&nbsp;</a>
                    <button id="mc-questiontimer-clear" data-controls="sendtosite-questiontimer" data-disable='{"started": false, 
                      </div>"ended": true, "round": false, "question": false, "timerrunning": false}' data-label="Cancel Timer" class="btn btn-danger" disabled>&nbsp;Stop&nbsp;</button>*/?>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="vert-tabs-sc" role="tabpanel" aria-labelledby="vert-tabs-sc-tab">
                <div class="row">
                  <div class="alert m-auto w-100">
                    <div class="alert alert-danger text-center" id="mc-score-config-alert">
                      Score configuration can only be adjusted after a round has begun AND between questions.
                    </div>
                  </div>
                  <div class="card m-auto" id="roundModifierContainer">
                    <div class="card-header bg-info">
                      <h3 class="card-title">Override Scoring</h3>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-8">
                          <strong>Auto Scorer</strong><br />
                          <small>Automatically marks each answer as it comes in.<br />Configuration for this feature is below.<br />The system will only grant these scores automatically IF auto scorer is turned on.</small>
                        </div>
                        <div class="col-4 text-right">
                          <input type="checkbox" id="round-mod-auto-scorer" data-width="75" data-height="32" data-toggle="toggle" data-on="ON" data-off="OFF" data-onstyle="success" data-offstyle="danger">
                        </div>
                      </div>
                    </div>
                    <div class="card-body p-0">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>General Scoring</th> 
                            <th>Score</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td class="align-middle">
                              <label for="round-mod-score-correct" style="font-weight:normal;">Correct answers</label>
                            </td>
                            <td class="align-middle">
                              <div class="input-group">
                                <input class="form-control" type="number" id="round-mod-score-correct" value="0" min="0" step="1">
                                <div class="input-group-append">
                                  <span class="input-group-text">pts</span>
                                </div>
                              </div> 
                            </td>
                          </tr>
                          <tr>
                            <td class="align-middle">
                              <label for="round-mod-score-incorrect" style="font-weight:normal;">Incorrect answers</label>
                            </td>
                            <td class="align-middle">
                              <div class="input-group">
                                <input class="form-control" type="number" id="round-mod-score-incorrect" value="0" max="0" step="1">
                                <div class="input-group-append">
                                  <span class="input-group-text">pts</span>
                                </div>
                              </div> 
                            </td>
                          </tr>
                        </tbody>
                        <thead>
                          <tr></tr>
                          <tr>
                            <th>Speed Scoring</th> 
                            <th>Score</th>
                          </tr>
                        </thead>
                        <tbody id="round-mod-score-dynamicrows">
                        </tbody>
                        <thead>
                          <tr></tr>
                          <tr>
                            <td colspan="2" class="text-center" id="round-mod-speedscore-buttons">
                              <button class="btn btn-danger" onClick="removeScoreRow();return false;" style="display:none;" id="round-mod-score-remove"><i class="fa fa-times"></i></button>
                              <button class="btn btn-primary" onClick="addScoreRow();return false;"><i class="fa fa-plus"></i></button>
                            </td>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="card no-border shadow-none m-2">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-6">
                        <button class="btn btn-warning" id="mc-scoreoverride-reset" style="display:none;" data-controls="sendtosite-score" data-label="Reset Score Template">Reset to Round Default</button>
                      </div>
                      <div class="col-6 text-right">
                        <button class="btn btn-success" id="mc-scoreoverride-send" style="display:none;" data-controls="sendtosite-score" data-label="Update Score Template">Send Override</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="vert-tabs-qd" role="tabpanel" aria-labelledby="vert-tabs-qd-tab">
                <div class="form-group">
                  <label for="mc-questioninfo-selector">Select Question</label>
                  <select id="mc-questioninfo-selector"class="select2bs4 full-width form-control"></select> 
                </div>
                <hr />
                 <div id="mc-questioninfo"></div>
                  <table id="mc-questionanswers" class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                      <th>Team ID</th>
                      <th>Team Name</th>
                      <th>Answer</th>
                      <th>Awarded Score</th>
                      <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                      <th>Team ID</th>
                      <th>Team Name</th>
                      <th>Answer</th>
                      <th>Awarded Score</th>
                      <th>Options</th>
                    </tr>
                    </tfoot>
                  </table>
              </div>
              <div class="tab-pane fade" id="vert-tabs-tl" role="tabpanel" aria-labelledby="vert-tabs-tl-tab">
                 <div id="mc-quiztimeline"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card" id="sendtosite-control" style="display:none;">
      <div class="sendtosite-control-overlay overlay dark" style="display:none;">
      </div>
      <div class="card-body">
        <table class="table full-width">
          <thead>
            <tr><th colspan="2" class="text-center">Game control overview</th></tr>
            <tr>
              <th style="width:150px">Info</th>
              <th>Values</th>
            </tr>
          </thead>
          <tbody>
            <tr id="sendtosite-quiz" style="display:none;">
              <th>Game Control</th>
              <td>-</td>
            </tr>
            <tr id="sendtosite-round" style="display:none;">
              <th>Round Control</th>
              <td>-</td>
            </tr>
            <tr id="sendtosite-question" style="display:none;">
              <th>Question Control</th>
              <td>-</td>
            </tr>
            <tr id="sendtosite-questiontimer" style="display:none;">
              <th>Question Timer</th>
              <td>-</td>
            </tr>
            <tr id="sendtosite-score" style="display:none;">
              <th>Score Override</th>
              <td>-</td>
            </tr>
          </tbody>
          <tfoot>
            <td>
              <button class="btn btn-md btn-danger" id="game-control-clearpackets">Cancel</button>
            </td>
            <td class="text-right">
              <button class="btn btn-md btn-success" id="game-control-sendtosite">Send</button>
            </td>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  <!-- /.col-md-6 -->

</div>
<!-- /.row -->
<div class="row">
  <div class="col-12">
    <?=renderChatBox()?>
  </div>
</div>
<div class="row">
  <div class="col-sm-12 col-md-6 col-lg-6">
    <?=renderTeamsAnswered()?>
  </div>
  <div class="col-sm-12 col-md-6 col-lg-6">
    <?=renderLeaderBoard()?>
  </div>
</div>
<?
}