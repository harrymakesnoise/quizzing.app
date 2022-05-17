<?
$filter     = getURLParam(2);
$roundID    = getURLParam(3);

if(getCleanRequestParam('process') != '') {
  $class = new QuizRound();
  if($class->createEdit($roundID, $quizSite->siteid)) {
    header("Location: /quiz-manager/rounds");
    exit;
  }
}

$setRoundId    = null;
$loadRound  = false;

if($roundID != '') {
  $setRoundId = intVal($roundID);
  $loadRound  = true;
}

$allQuestions = array();

$selectedQuizRound = new QuizRound($setRoundId, $loadRound);
$roundLabel        = getCleanRequestParam('label', $selectedQuizRound->getData('label'));
$questions         = (isset($_REQUEST['questions']) ? explode(",", $_REQUEST['questions']) : $selectedQuizRound->getData('questions', 'array'));
$quizQuestions     = new QuizQuestions();
$questionOutput    = $quizQuestions->getMany($questions);

$dataTablesURL = '/api/lists/site-questions/';

$headerScripts .= '  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link href="/dist/css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
  <script>var chosenQuestions = [];</script>
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
            <h1><?=($filter == 'new' ? 'Create Round' : 'Edit Round')?></h1>
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
                    <label for="roundInput">Round Name</label>
                    <input type="text" class="form-control" id="roundInput" name="label" placeholder="" value="<?=$roundLabel?>">
                  </div>
                  <div class="form-group">
                    <label>Questions&nbsp;&nbsp;&nbsp;</label>
                    <span class="badge badge-xs badge-success" id="question-selected-counter">0</span><br />
                    <small>Re-order how the questions will appear by clicking and dragging them below</small>
                  </div>
                  <div class="sortable" id="sortable">
                    <?
                    foreach($questionOutput as $question) {
                      ?>
                      <div class="card" data-questionid="<?=$question->getData('questionid')?>">
                        <div class="card-header">
                          <span style="margin-left:10px;cursor:pointer;" class="badge badge-danger float-right" onClick="toggleQuestionInRound(<?=$question->getData('questionid')?>)">
                            <i class="fa fa-times"></i>
                          </span>
                          <span>[<?=$question->getData('questionid')?>] <?=$question->getData('label')?></span>
                        </div>
                      </div>
                      <script>chosenQuestions.push(<?=$question->getData('questionid')?>);</script>
                      <?
                    }
                    ?>
                  </div>
                </div>
                <input type="hidden" name="questions" id="questionsInput">
                <div class="card-footer">
                  <div class="row">
                    <div class="col-6">
                      <? if($filter != 'new') { ?>
                      <a href="/" class="btn btn-danger">Delete&nbsp;&nbsp;&nbsp;<i class="fas fa-times"></i></a>
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
                  <h3 class="card-title">Question Picker</h3>
                </div>
                <div class="card-body">
			            <ul class="nav nav-tabs" role="tablist">
			              <li class="nav-item col-6 text-center">
			                <a class="nav-link active" id="site-repo-tab" data-toggle="pill" href="#site-repo" role="tab" aria-controls="site-repo" aria-selected="true">Your Questions</a>
			              </li>
			              <li class="nav-item col-6 text-center">
			                <a class="nav-link" id="public-repo-tab" data-toggle="pill" href="#public-repo" role="tab" aria-controls="public-repo" aria-selected="false">Public Repository</a>
			              </li>
			            </ul>
			            <div class="tab-content" id="custom-content-above-tabContent">
			              <div class="tab-pane fade p-2 show active" id="site-repo" role="tabpanel" aria-labelledby="site-repo-tab">
			                <table id="site-questions" class="table table-bordered table-hover">
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
			              <div class="tab-pane fade p-2" id="public-repo" role="tabpanel" aria-labelledby="public-repo-tab">
			              	<div class="form-group">
			              		<label for="site-repo-select">Choose a category:</label>
				                <select class="select2bs4 form-control" id="site-repo-select">
				                	<option></option>
				                </select>
			              	</div>
			              	<div class="form-group">
			              		<label for="site-repo-qty">How many questions?</label>
				                <input class="form-control" type="number" max="100" min="0" step="1" id="site-repo-qty">
			              	</div>
			              </div>
			            </div>
			          </div>
			        </div>
		          <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <div class="row">
            &nbsp;
          </div>
          <input type="hidden" name="process" value="1">
        </form>
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
<?
$footerScripts .= '
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
	function toggleQuestionInRound(id, label) {
		if(typeof id === "undefined") { return false; }
		var index = chosenQuestions.indexOf(id);
		if(index > -1) {
			chosenQuestions.splice(index, 1);
      $("#sortable div[data-questionid=\'" + id + "\']").remove();
			$("#checkbox-question-" + id).prop("checked", false);
		} else {
			chosenQuestions.push(id);
      $("#sortable").append(\'<div class="card" data-questionid="\' + id + \'"><div class="card-header"><span style="margin-left:10px;" class="badge badge-danger float-right" onClick="toggleQuestionInRound(\' + id + \')"><i class="fa fa-times"></i></span><span>[\' + id + \'] \' + label + \'</span></div></div>\');
			$("#checkbox-question-" + id).prop("checked", true);
		}
    writeQuestionInput();
    $("#question-selected-counter").html(chosenQuestions.length);
	}
	function checkSelectedQuestions(rows) {
		for(var i=0;i<rows.length;i++) {
			if(rows[i] != null) {
				var row = rows[i];
				if(chosenQuestions.indexOf(row[1]) > -1) {
					$("#checkbox-question-" + row[1]).prop("checked", true);
				}
			}
		}
	}
  function writeQuestionInput() {
    var ele = $("#sortable .card");
    var inputData = new Array();

    ele.each(function() {
      inputData.push($(this).data("questionid"));
    });

    $("#questionsInput").val(inputData.toString());
  }
  var table = $("#site-questions").DataTable({
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
    	checkSelectedQuestions(api.rows( {page:"current"} ).data());
    }
  });
  $(document).ready(function() {
	  $("#site-questions tbody").on("click", "tr", function () {
      var data = table.row( this ).data();
      toggleQuestionInRound(data[1], data[2]);
	  });
    $( "#sortable" ).sortable({
      update: function( event, ui ) {
        writeQuestionInput();
      }
    });
    writeQuestionInput();
    $("#question-selected-counter").html(chosenQuestions.length);
    $( "#sortable" ).disableSelection();
  });
</script>';
renderSiteFooter();
?>