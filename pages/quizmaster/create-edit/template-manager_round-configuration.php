<?
$filter = getURLParam(2);
$quizID = getURLParam(3);

if(getCleanRequestParam('process') != '') {
  $class = new QuizTemplate();
  if($class->createEdit($quizID, $quizSite->siteid)) {
    header("Location: /template-manager/round-configuration");
    exit;
  }
}

$headerScripts .= '  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link href="/dist/css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
  <script>var chosenRounds = [];</script>
';
$footerScripts .= '
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>$(".select2bs4").select2({theme: "bootstrap4"})</script>
<!-- jQuery Ui -->
<script src="/plugins/jquery-ui/jquery-ui.min.js"></script>';
renderSiteHeader();
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?=($filter == 'new' ? 'Create Round Configuration Template' : 'Edit Round Configuration Template')?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item"><a href="/template-manager/round-configuration">Round Configuration Templates</a></li>
              <li class="breadcrumb-item active"><?=($filter == 'new' ? 'Create' : 'Edit')?></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <form role="form" method="post" action="<?=CURRENT_PAGE_URL?>">
          <div class="row">
            <div class="col-md-6 col-sm-12">
              <div class="card">
                <div class="card-header bg-primary">
                  <h3 class="card-title">Template Information</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="template-name">Template Name</label>
                    <input type="text" name="label" value="" id="template-name" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="template-name">Template Name</label>
                    <input type="text" name="label" value="" id="template-name" class="form-control">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-sm-12">
              <div class="card">
                <div class="card-header bg-warning">
                  <h3 class="card-title">Round Configuration</h3>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-10">
                      <strong>Always accessible</strong><br />
                      <small>Useful for picture rounds / rounds where players can go back to at any point in the quiz</small>
                    </div>
                    <div class="col-2 text-right">
                      <input type="checkbox" id="round-mod-always-available" data-bootstrap-switch data-on-color="success" data-off-color="danger" data-on-text="YES" data-off-text="NO">
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-10">
                      <strong>Auto Scorer</strong><br />
                      <small>Automatically marks each answer as it comes in.<br />This feature can be configured on the &ldquo;Round Scoring&rdquo; pane.</small>
                    </div>
                    <div class="col-2 text-right">
                      <input type="checkbox" id="round-mod-auto-scorer" data-bootstrap-switch data-on-color="success" data-off-color="danger" data-on-text="ON" data-off-text="OFF">
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-6">
                      <strong>End Round After</strong><br />
                      <small>
                        <strong>Manual</strong>: You push the button to end the round.<br />
                        <strong>Automatic</strong>: Game-pad will automatically end the round once all answers for the last question have been submitted.<br />
                        <strong>After x amount...</strong>: Consider it a 'bonus points' round. After a certain amount of points have been given across any of the questions in this round, game-pad will automatically end the round.<br />
                      </small>
                    </div>
                    <div class="col-6">
                      <br />
                      <select class="select2bs4" data-dropdown-css-class="no-search" id="round-mod-end-after">
                        <option value="manual">Manual</option>
                        <option value="auto">Automatic</option>
                        <option value="after">After x amount of points</option>
                      </select>
                      <div id="round-mod-endroundafter-xamount" class="form-group mt-3" style="display:none;">
                        <label for="round-mod-endroundafter-value">Value</label>
                        <div class="input-group">
                          <input class="form-control" type="number" id="round-mod-endroundafter-value" value="0" min="0" step="1">
                          <div class="input-group-append">
                            <span class="input-group-text">pts</span>
                          </div>
                        </div> 
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
<?
$footerScripts .= '<script>
function addScoreRow() {
  var outputElement = $("#round-mod-score-dynamicrows");
  var outputLength = outputElement.children().length;
  var nextNumber = outputLength+1;
  var sup = "th";
  switch(nextNumber.toString().slice(-1)) {
    case "1": sup = "st"; break;
    case "2": sup = "nd"; break;
    case "3": sup = "rd"; break;
  }
  var newLabel = nextNumber + "<sup>" + sup + "</sup>";
  var newId = nextNumber;
  var outputHTML = \'<tr><td class="align-middle"><label for="round-mod-speed-score-\' + newId + \'" style="font-weight:normal;">\' + newLabel + \' team to answer correctly</label></td><td class="align-middle"><div class="input-group"><input class="form-control" type="number" id="round-mod-speed-score-\' + newId + \'" value="0" min="0" step="1"><div class="input-group-append"><span class="input-group-text">pts</span></div></div></td></tr>\';

  $("#round-mod-score-dynamicrows").append(outputHTML); 
  $("#round-mod-score-remove").fadeIn();
}
function removeScoreRow() {
  var outputElement = $("#round-mod-score-dynamicrows");
  outputElement.children().last()[0].remove();
  var outputLength = outputElement.children().length;
  if(outputLength <= 0) {
    $("#round-mod-score-remove").fadeOut();
  }
}
$("#round-mod-end-after").on("change", function() {
  if($(this).val() == "after") {
    $("#round-mod-endroundafter-xamount").fadeIn();
  } else {
    $("#round-mod-endroundafter-xamount").fadeOut();
  }
});
</script>';
$footerScripts .= '<!-- Bootstrap Switch --><script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script><script>    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch();
    });</script>';
renderSiteFooter();
?>