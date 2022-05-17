<?
$filter = getURLParam(2);
$quizID = getURLParam(3);

if(getCleanRequestParam('process') != '') {
  $class = new Quiz();
  if($class->createEdit($quizID, $quizSite->siteid)) {
    header("Location: /quiz-manager/quizzes");
    exit;
  }
}

$setQuizId = null;
$loadQuiz  = false;

if($quizID != '') {
  $setQuizId = intVal($quizID);
  $loadQuiz  = true;
}

$allQuestions = array();

$selectedQuiz  = new Quiz($setQuizId, $loadQuiz);
$quizLabel     = getCleanRequestParam('label', $selectedQuiz->getData('label'));
$rounds        = (isset($_REQUEST['rounds']) ? explode(",", $_REQUEST['rounds']) : $selectedQuiz->getData('rounds', 'array'));
$roundMods     = htmlspecialchars_decode(getCleanRequestParam('roundmods', $selectedQuiz->getData('roundmodifiers')));
$quizRounds    = new QuizRounds();
$roundOutput   = $quizRounds->getMany($rounds);

$roundModifiers = json_decode($roundMods, true);
if($roundModifiers === null || $roundModifiers === false) {
  $roundModifiers = [];
}
$roundModifiers["default"] = [];
$roundModifiers["default"]["round-mod-always-available"] = false;
$roundModifiers["default"]["round-mod-auto-scorer"] = true;
$roundModifiers["default"]["round-mod-end-after"] = "auto";
$roundModifiers["default"]["round-mod-endroundafter-value"] = "0";
$roundModifiers["default"]["round-mod-score-correct"] = "1";
$roundModifiers["default"]["round-mod-score-incorrect"] = "0";

$roundMods = json_encode($roundModifiers);

$dataTablesURL = '/api/lists/site-rounds/';

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
            <h1><?=($filter == 'new' ? 'Create Quiz' : 'Edit Quiz')?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item"><a href="/quiz-manager/rounds">Rounds</a></li>
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
            <div class="col-md-6">
              <!-- Block buttons -->
              <div class="card">
                <div class="card-header bg-success">
                  <h3 class="card-title">Basic Info</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="roundInput">Quiz Name</label>
                    <input type="text" class="form-control" id="roundInput" name="label" placeholder="" value="<?=$quizLabel?>">
                  </div>
                  <div class="form-group">
                    <label>Rounds&nbsp;&nbsp;&nbsp;</label>
                    <span class="badge badge-xs badge-success" id="round-selected-counter">0</span><br />
                    <small>Re-order how the rounds will be played in the quiz</small>
                  </div>
                  <div class="sortable" id="sortable">
                    <?
                    foreach($roundOutput as $round) {
                      ?>
                      <div class="card" data-roundid="<?=$round->getData('roundid')?>">
                        <div class="card-header row align-items-center">
                          <div class="col-8">
                            <span>[<?=$round->getData('roundid')?>] <?=$round->getData('label')?></span>
                          </div>
                          <div class="col-4 text-right">
                            <button class="btn btn-info roundSettingsBtn" data-roundid="<?=$round->getData('roundid')?>" onClick="return false;"><i class="fa fa-cog"></i></button>
                            <button class="btn btn-danger roundToggleBtn" data-roundid="<?=$round->getData('roundid')?>" onClick="return false;"><i class="fa fa-times"></i></button>
                          </div>
                        </div>
                      </div>
                      <script>chosenRounds.push(<?=$round->getData('roundid')?>);</script>
                      <?
                    }
                    ?>
                  </div>
                </div>
                <input type="hidden" name="rounds" id="roundsInput">
                <div class="card-footer">
                  <div class="row">
                    <div class="col-6">
                      <? if($filter != 'new') { ?>
                      <a href="/delete" class="btn btn-danger">Delete&nbsp;&nbsp;&nbsp;<i class="fas fa-times"></i></a>
                      <? } ?>
                    </div>
                    <div class="col-6 text-right">
                      <button class="btn btn-success">Save&nbsp;&nbsp;&nbsp;<i class="fas fa-check"></i></button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
              <!-- Block buttons -->
              <div class="card">
                <div class="card-header bg-primary">
                  <h3 class="card-title">Round Picker</h3>
                </div>
                <div class="card-body">
	                <table id="site-rounds" class="table table-bordered table-hover">
	                  <thead>
	                  <tr>
	                  	<th></th>
	                    <th>Q-ID</th>
	                    <th>Question</th>
	                    <th>Category</th>
	                  </tr>
	                  </thead>
	                  <tbody>
	                  </tbody>
	                  <tfoot>
	                  <tr>
	                  	<th></th>
	                    <th>Q-ID</th>
	                    <th>Question</th>
	                    <th>Category</th>
	                  </tr>
	                  </tfoot>
	                </table>
			          </div>
			        </div>
		          <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <div class="row" id="roundModifierContainer" style="display:none;">
            <div class="container-fluid">
              <hr />
              <div class="row mb-2">
                <div class="col-sm-6">
                  <h3>Round Settings: <span id="roundModifierTitle"></span></h3>
                </div>
                <div class="col-sm-6 text-right">
                  <button class="btn btn-warning" onClick="resetRoundModifier(); return false;">Reset&nbsp;&nbsp;<i class="fa fa-history"></i></button>
                  <button class="btn btn-success" onClick="saveRoundModifier(); return false;">Save&nbsp;&nbsp;<i class="fa fa-check"></i></button>
                </div>
              </div>
            </div>
            <div class="col-md-6">
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
            <div class="col-md-6">
              <div class="card">
                <div class="card-header bg-info">
                  <h3 class="card-title">Round Scoring</h3>
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
                        <td colspan="2" class="text-center">
                          <button class="btn btn-danger" onClick="removeScoreRow();return false;" style="display:none;" id="round-mod-score-remove"><i class="fa fa-times"></i></button>
                          <button class="btn btn-primary" onClick="addScoreRow();return false;"><i class="fa fa-plus"></i></button>
                        </td>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            &nbsp;
          </div>
          <input type="hidden" name="process" value="1">
          <input type="hidden" name="roundmods" id="roundDataInput" value="<?=$roundMods?>">
        </form>
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <div class="modal fade" id="modal-save">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Unsaved Changes</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Please either save or reset your changes to round configurations to continue.</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" onClick="resetRoundModifier();return false;">Reset</button>
          <button type="button" class="btn btn-primary" onClick="saveRoundModifier(); return false;">Save</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
<?
$footerScripts .= '
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
	function toggleRoundInQuiz(id, label) {
		if(typeof id === "undefined") { return false; }
		var index = chosenRounds.indexOf(id);
		if(index > -1) {
			chosenRounds.splice(index, 1);
      $("#sortable div[data-roundid=\'" + id + "\']").remove();
			$("#checkbox-round-" + id).prop("checked", false);
		} else {
			chosenRounds.push(id);
      $("#sortable").append(\'<div class="card" data-roundid="\' + id + \'"><div class="card-header row align-items-center"><div class="col-8"><span>[\' + id + \'] \' + label + \'</span></div><div class="col-4 text-right"><button class="btn btn-info" onClick="toggleRoundSettings(\' + id + \');return false;"><i class="fa fa-cog"></i></button> <button class="btn btn-danger" onClick="toggleRoundInQuiz(\' + id + \');return false;"><i class="fa fa-times"></i></button></div></div></div>\');
			$("#checkbox-round-" + id).prop("checked", true);
		}
    writeRoundInput();
    $("#round-selected-counter").html(chosenRounds.length);
	}
	function checkSelectedRounds(rows) {
		for(var i=0;i<rows.length;i++) {
			if(rows[i] != null) {
				var row = rows[i];
				if(chosenRounds.indexOf(row[1]) > -1) {
					$("#checkbox-round-" + row[1]).prop("checked", true);
				}
			}
		}
	}
  var currentEditRoundId = 0;
  var currentEditChangesSaved = true;
  var currentEditLabel = "";
  var currentEditRoundElement = null;
  var roundSettings = ' . $roundMods . ';
  var nextEditRoundId = 0;

  function toggleRoundSettings(id) {
    if(typeof id === "undefined") { return false; }
    id = parseInt(id);
    if(id == currentEditRoundId) { return false; }
    if(currentEditRoundId > 0) {
      nextEditRoundId = id;
      $("#modal-save").modal("show");
      return false;
    }

    nextEditRoundId = 0;
    currentEditRoundId = id;
    currentEditRoundElement = $("#sortable div[data-roundid=\'" + currentEditRoundId + "\'");
    var fullLabel = currentEditRoundElement.text();
    currentEditLabel = fullLabel.substring(fullLabel.indexOf("]")+1).trim();

    var thisRoundSettings = new Object();
    if(roundSettings[currentEditRoundId] != null) {
      thisRoundSettings = roundSettings[currentEditRoundId];
    } else {
      thisRoundSettings = roundSettings["default"];
    }
    var inputs = $("#roundModifierContainer input,#roundModifierContainer select");
    inputs.each(function() {
      var val = thisRoundSettings[$(this).attr("id")];
      if($(this).attr("type") == "checkbox") {
        $(this).prop("checked",val);
        console.log($(this));
      } else if($(this).hasClass("select2bs4")) {
        $(this).val(val).trigger("change");
      } else {
        $(this).val(val);
      }
    });
    setTimeout(function() {
      inputs.each(function() {
        $(this).trigger("change");
      });
    }, 100);

    $("#roundModifierTitle").html(currentEditLabel);
    $("#roundModifierContainer").slideDown();
  }

  function saveRoundModifier() {
    var inputs = $("#roundModifierContainer input,#roundModifierContainer select");
    var data = {};
    inputs.each(function() {
      var val = $(this).val();
      if($(this).attr("type") == "checkbox") {
        val = $(this).prop("checked");
      }
      data[$(this).attr("id")] = val;
    });
    roundSettings[currentEditRoundId] = data;
    $("#roundDataInput").val(JSON.stringify(roundSettings));
    $("#modal-save").modal("hide");
    $("#roundModifierContainer").slideUp().promise().done(function() {
      currentEditRoundId = 0;
      if(nextEditRoundId > 0) {
        toggleRoundSettings(nextEditRoundId);
      }
    });
  }

  function resetRoundModifier() {
    var inputs = $("#roundModifierContainer input,#roundModifierContainer select");
    inputs.each(function() {
      if($(this).attr("type") == "checkbox") {
        $(this).prop("checked", this.defaultChecked);
        $(this).bootstrapSwitch("destroy");
        $(this).bootstrapSwitch();
      } else if($(this).hasClass("select2bs4")) {
        $(this).val(this.defaultSelected).trigger("change");
      } else {
        $(this).val(function() {
          return this.defaultValue;
        });
      }
    });
    delete roundSettings[currentEditRoundId];
    $("#roundDataInput").val(JSON.stringify(roundSettings));
    $("#modal-save").modal("hide");
    $("#roundModifierContainer").slideUp().promise().done(function() {
      currentEditRoundId = 0;
      if(nextEditRoundId > 0) {
        toggleRoundSettings(nextEditRoundId);
      }
    });
  }

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
  
  function writeRoundInput() {
    var ele = $("#sortable .card");
    var inputData = new Array();

    ele.each(function() {
      inputData.push($(this).data("roundid"));
    });

    $("#roundsInput").val(inputData.toString());
  }
  var table = $("#site-rounds").DataTable({
    "paging": true,
    "lengthChange": false,
    "pageLength": 5,
    "searching": true,
    "rowReorder": true,
    "columnDefs": [
        { "width": "5%", "targets": 0 },
        { orderable: true, width: "10%", targets: 1 },
        { orderable: false, targets: "_all" },
        { "width": "30%", "targets": 2 },
        { "width": "18%", "targets": 3 },
    ],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "processing": true,
    "serverSide": true,
    "ajax": "' . $dataTablesURL . '",
    "drawCallback": function( settings ) {
    	var api = this.api();
    	checkSelectedRounds(api.rows( {page:"current"} ).data());
    }
  });
  $(document).ready(function() {
	  $("#site-rounds tbody").on("click", "tr", function () {
      var data = table.row( this ).data();
      toggleRoundInQuiz(data[1], data[2]);
	  });
    $( "#sortable" ).sortable({
      update: function( event, ui ) {
        writeRoundInput();
      }
    });
    writeRoundInput();
    $("#round-selected-counter").html(chosenRounds.length);
    $("#sortable").disableSelection();
    $("#round-mod-end-after").on("change", function() {
      if($(this).val() == "after") {
        $("#round-mod-endroundafter-xamount").fadeIn();
      } else {
        $("#round-mod-endroundafter-xamount").fadeOut();
      }
    });
    $(".roundSettingsBtn").click(function() {
      var roundid = $(this).data("roundid");
      toggleRoundSettings(roundid);
    });
    $(".roundToggleBtn").click(function() {
      var roundid = $(this).data("roundid");
      toggleRoundInQuiz(roundid);
    });
    $("#roundDataInput").val(JSON.stringify(roundSettings));
  });
</script>';
$footerScripts .= '<!-- Bootstrap Switch --><script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script><script>    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch();
    });</script>';
renderSiteFooter();
?>