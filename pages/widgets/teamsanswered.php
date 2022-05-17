<?
function renderTeamsAnswered() {
  return '
    <div class="card">
      <div class="card-body p-0">
        <div class="p-4 border-bottom">
          <div class="row">
            <div class="col-6">Teams Answered:</div>
            <div class="col-6 font-weight-light text-right"><span id="mc-teamsanswered-count">0</span>/<span id="mc-teamsanswered-teamtotal">0</span> (<span id="mc-teamsanswered-remain">0</span> remaining)</span></div>
          </div>
          <div class="row mt-1">
            <div class="col-4 text-left"><span class="badge badge-md badge-secondary">Not Answered</span></div>
            <div class="col-4 text-center"><span class="badge badge-md badge-warning">Incorrect</span></div>
            <div class="col-4 text-right"><span class="badge badge-md badge-success">Correct</span></div>
          </div>
        </div>
        <table class="table table-striped">
          <thead>
            <tr>
              <th colspan="2">Team Name</th>
              <th class="text-right">Status</th>
            </tr>
          </thead>
          <tbody id="mc-teamsanswered">
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->';
}
?>