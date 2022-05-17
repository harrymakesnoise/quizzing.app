<?
$headerScripts .= '<!-- Leaderboard -->
<link rel="stylesheet" href="/dist/css/leaderboard.min.css">';

function renderLeaderboard() {
  global $footerScripts, $quizSite;
$footerScripts .= '<!-- Leaderboard -->
<script src="/dist/js/leaderboard' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . time() . '"></script>';
  
  echo '
    <div class="card">
      <div class="card-header">
        <h5>Leaderboard <label class="badge badge-sm badge-danger float-right">LIVE <svg version="1.1" baseProfile="full" width="20" height="20" xmlns="http://www.w3.org/2000/svg" class="button" expanded="true" height="20px" width="20px"><circle cx="50%" cy="50%" r="7px"></circle><circle class="pulse" cx="50%" cy="50%" r="10px"></circle></svg></h5>
      </div>
      <div class="card-body p-2">
        <div class="row border-bottom" style="padding:0rem .5rem;">
          <div class="col-3 pos p-2 text-center text-bold">#</div>
          <div class="col-6 teamname p-2 text-left text-bold">Team Name</div>
          <div class="col-3 score p-2 text-center text-bold">Score</div>
        </div>
        <div id="live-leaderboard" class="position-relative p-2">
        </div>
      </div>
      <!-- /.card-body -->
    </div>
  <template id="ranking-item" style="display:none;">
    <div class="row ranking-item" data-teamid="{{userId}}">
      <div class="col-3 pos p-2 text-center ranking-item-order">{{order}}</div>
      <div class="col-6 teamname p-2 text-left ranking-item-userName" data-fieldname="username">{{userName}}</div>
      <div class="col-3 score p-2 text-center text-bold ranking-item-score">{{score}}</div>
    </div>
  </template>';
}
?>