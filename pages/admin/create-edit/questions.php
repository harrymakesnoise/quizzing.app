<?
$filter     = getURLParam(1);
$questionID = getURLParam(2);

if(getCleanRequestParam('process') != '') {
  $class = new QuizQuestion();
  if($class->createEdit($questionID, $quizSite->siteid)) {
    header("Location: /questions");
    exit;
  }
}

$setQuestionId = null;
$loadQuestion  = false;

if($questionID != '') {
  $setQuestionId = intVal($questionID);
  $loadQuestion  = true;
}

$selectedQuizQuestion = new QuizQuestion($setQuestionId, $loadQuestion);
$questionLabel        = getCleanRequestParam('question', $selectedQuizQuestion->getData('label'));
$questionAnswers      = (isset($_REQUEST['answers']) ? $_REQUEST['answers'] : $selectedQuizQuestion->getData('answers', 'array'));
if(!is_countable($questionAnswers) || count($questionAnswers) == 0) {
  $questionAnswers    = array('');
}
$questionType         = getCleanRequestParam('type', $selectedQuizQuestion->getData('type'));
$questionCategory     = getCleanRequestParam('questioncategory', $selectedQuizQuestion->getData('category'));
$correctAnswers       = (isset($_REQUEST['questioncorrect']) ? $_REQUEST['questioncorrect'] : $selectedQuizQuestion->getData('correctanswers', 'array'));
$answerImages         = (isset($_REQUEST['answer-image']) ? $_REQUEST['answer-image'] : $selectedQuizQuestion->getData('answerimages', 'array'));

$quizCategories = new QuizCategories();
$quizCategoryData = $quizCategories->get(0, 999999, 'asc', '', '', true);
$allQuizCategories = $quizCategoryData['data'];

if(!is_countable($correctAnswers) || count($correctAnswers) == 0) {
  $correctAnswers    = array('');
}
$footerScripts .= '
  <script src="/dist/js/jquery.easing.js" type="text/javascript"></script>
  <script src="/dist/js/jqueryFileTree.js" type="text/javascript"></script>';
$headerScripts .= '  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link href="/dist/css/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
  <script>
  function addAnswer() {
    var eleArray = $("#dynamic-image-fields").find(\'div.form-group\');
    var nextAnswerId = parseInt(eleArray.length)+1;
    $("#questionanswers").append(\'<input type="text" class="form-control" value="" placeholder="Enter an answer" name="questionanswer[]" onKeyUp="updateCorrectAnswers()">\');
    $("#dynamic-image-fields").append("<div class=\'form-group\'><label for=\'answer-image-" + nextAnswerId + "\'>Answer #" + nextAnswerId + " Image</label><div class=\'input-group mb-3\'><input type=\'text\' class=\'form-control\' id=\'answer-image-" + nextAnswerId + "\' name=\'answer-image[]\' onFocus=\'this.value=\"\";triggerFilePicker(\"answer-image-" + nextAnswerId + "\");this.blur();\'><div class=\'input-group-append\'><button type=\'button\' class=\'btn btn-primary\' onClick=\'triggerFilePicker(\"answer-image-" + nextAnswerId + "\");\'>Select</button><button type=\'button\' style=\'display:none;\' class=\'btn btn-danger\' id=\'answer-image-" + nextAnswerId + "-remove\' onClick=\'$(\"#answer-image-" + nextAnswerId + "\").val(\"\");$(this).hide();return false;\'>Clear</button></div></div></div>");
    
    $("#removeanswerbutton").css("display", "inline-block");
  }
  function removeAnswer() {
    var eleArray = $("#questionanswers").find(\'input\');
    if(eleArray.length > 1) {
      if(eleArray.length == 2) {
        $("#removeanswerbutton").css("display", "none");
      }
      eleArray.last().remove();
      updateCorrectAnswers();
    }
    var eleArray = $("#dynamic-image-fields").find(\'div.form-group\');
    if(eleArray.length > 1) {
      eleArray.last().remove();
    }
  }
  function updateCorrectAnswers() {
    var outData = {};
    var selectData = {}
    
    var correctAnswers = $("#questioncorrect").select2("data");

    for(var k in correctAnswers) {
      var selected = correctAnswers[k].selected ? "selected" : "";
      selectData[k] = {"label": correctAnswers[k].text, "value": k, "selected": selected};
    }

    var count = 0;
    $(\'input[name="questionanswer[]"]\').each(function() {
      var selected = "";
      if(selectData[count]) {
        selected = selectData[count].selected;
      }
      if(selected == null) {
        selected = "";
      }
      outData[count] = {"label": $(this).val(), "value": count, "selected": selected};
      count++;
    });

    $("#questioncorrect").html("");
    var outHTML = "";
    for(var k in outData) {
      outHTML = outHTML + "<option value=\'" + outData[k].value + "\' " + outData[k].selected + ">" + outData[k].label + "</option>";
    }
    $("#questioncorrect").html(outHTML).trigger("change");
  }
  </script>';
$footerScripts .= '
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>$(".select2bs4").select2({theme: "bootstrap4"})</script>';
renderSiteHeader();
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?=($filter == 'new' ? 'Create Question' : 'Edit Question')?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item"><a href="/questions">Questions</a></li>
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
                    <label for="questionInput">Question</label>
                    <input type="text" class="form-control" id="questionInput" name="question" placeholder="" value="<?=$questionLabel?>">
                  </div>
                  <div class="form-group">
                    <label for="questioncategory">Question Category</label>
                    <select name="questioncategory" id="questioncategory" class="select2bs4" style="width:100%;" data-placeholder="Select a Category">
                      <option></option>
                    <? foreach($allQuizCategories as $label) { ?>
                      <option value="<?=$label?>" <?=($label == $questionCategory ? 'selected' : '')?>><?=$label?></option>
                    <? } ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="questionanswers">Question Answers</label>
                    <div id="questionanswers">
                    <? foreach($questionAnswers as $answer) { ?>
                      <input type="text" class="form-control" value="<?=$answer?>" placeholder="Enter an answer" name="questionanswer[]" onKeyUp="updateCorrectAnswers()">
                    <? } ?>
                    </div>
                    <div class="col-12 text-right pr-0">
                      <a href="" onClick="removeAnswer();return false;" id="removeanswerbutton" class="btn btn-danger btn-xs" style="display:<?=(count($questionAnswers) > 1 ? 'inline-block' : 'none')?>"><i class="fa fa-minus"></i></a>&nbsp;&nbsp;
                      <a href="" onClick="addAnswer();return false;" class="btn btn-success btn-xs"><i class="fa fa-plus"></i></a>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="questioncorrect">Correct Answer</label>
                    <select name="questioncorrect" id="questioncorrect" class="select2bs4" multiple="multiple" style="width:100%;" data-placeholder="Select at least one correct answer">
                      <option></option>
                      <? 
                      foreach($questionAnswers as $answer) {
                        $selected = (in_array($answer, $correctAnswers) ? ' selected' : '');
                        ?>
                        <option value="<?=$answer?>"<?=$selected?>><?=$answer?></option>
                      <?
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
              <!-- Application buttons -->
              <div class="card">
                <div class="card-header bg-primary" id="questionmedia">
                  <h3 class="card-title">Media</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="question-image">Question Image</label>
                    <div class="input-group mb-3">
                      <input type="text" class="form-control" id="question-image" onFocus="this.value='';triggerFilePicker('question-image');this.blur();" name="question-image">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onClick="triggerFilePicker('question-image');">Select</button>
                        <button type="button" style="display:none;" class="btn btn-danger" id="question-image-remove" onClick="$('#question-image').val('');$(this).hide();return false;">Clear</button>
                      </div>
                      <!-- /btn-group -->
                    </div>
                  </div>
                  <div id="dynamic-image-fields">
                  <? 
                  $aCount = 0; 
                  foreach($questionAnswers as $answer) {
                    $value         = (isset($answerImages[$aCount]) ? $answerImages[$aCount] : '');
                    $removeDisplay = ($value != '' ? 'inline-block' : 'none');
                    ?>
                    <div class="form-group">
                      <label for="question-image">Answer #<?=$aCount+1?> Image</label>
                      <div class="input-group mb-3">
                        <input type="text" class="form-control" id="answer-image-<?=$aCount+1?>" name="answer-image[]" value="<?=$value?>" onFocus="this.value='';triggerFilePicker('answer-image-<?=$aCount+1?>');this.blur();">
                        <div class="input-group-append">
                          <button type="button" class="btn btn-primary" onClick="triggerFilePicker('answer-image-<?=$aCount+1?>');">Select</button>
                          <button type="button" style="display:<?=$removeDisplay?>;" class="btn btn-danger" id="answer-image-<?=$aCount+1?>-remove" onClick="$('#answer-image-<?=$aCount+1?>').val('');$(this).hide();return false;">Clear</button>
                        </div>
                        <!-- /btn-group -->
                      </div>
                    </div>
                  <? $aCount++; } ?>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <div class="row">
            &nbsp;
          </div>
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
          <input type="hidden" name="process" value="1">
        </form>
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <div class="modal fade" id="modal-default">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">File Picker</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="mediaPicker">
          
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
<?
$footerScripts .= '
<script>
  var globalFieldID = null;
  $("#mediaPicker").fileTree(
    {
      root: "", 
      script: "/filemanager/filepicker.php", 
      folderEvent: "click", 
      expandSpeed: 750, 
      collapseSpeed: 750, 
      multiFolder: false
    },
    function(file) { 
      $("#modal-default").modal("hide");
      $("#" + globalFieldID).val(file);
      $("#" + globalFieldID + "-remove").show();
		}
  );
  function triggerFilePicker(fieldid) {
    if(typeof fieldid === "undefined") { return false; }
    globalFieldID = fieldid;
    $("#modal-default").modal("show");
  }
</script><!-- Bootstrap Switch --><script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script><script>    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch("state", $(this).prop("checked"));
    });</script>';
renderSiteFooter();
?>