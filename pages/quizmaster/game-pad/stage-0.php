<?
$headerScripts .= '<!-- Select2 -->
<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">';
$footerScripts .= '<!-- Select2 -->
<script src="/plugins/select2/js/select2.full.min.js"></script>
<script>
  $(".select2bs4").select2({
    theme: "bootstrap4", 
    ajax : { 
      url : \'/api/schedule/get/\',
      data: function(params) {
        var now = new Date();
        var query = {
          start: now.toISOString(),
          end: new Date(now.getTime() + 30*60000).toISOString(),
        }
        return query;
      },
      processResults: function (data) {
        var output = [];
        for(var i=0;i<data.length;i++) {
          var quizData = data[i];
          output.push({id: quizData.quizId, text: quizData.title});
        }
        return {
          results: output,
        };
      }
    }
  });
  $(".select2bs4").on("change", function() {
    window.location.href = window.location.href + "/" + $(this).val();
  });
</script>';

function renderGamePad() {
  ?>
<div class="row">
  <div class="col-12">
    <!-- interactive chart -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Quiz Selection</h5>
      </div>
      <div class="card-body">
        <div class="form-group">
          <select id="quiz" class="select2bs4 full-width form-control" data-dropdown-css-class="no-search" data-placeholder="Pick from this list">
            <option></option>
          </select>
        </div>
        <div class="form-group">
          <strong>Quiz not listed?</strong><br /><small>Only quizzes starting in the next 30 mins are displayed, visit the <a href="/quiz-manager/scheduler">Scheduler</a> to schedule a quiz.</small>
        </div>
      </div>
      <!-- /.card-body-->
    </div>
    <!-- /.card -->

  </div>
  <!-- /.col -->
</div>
<!-- /.row -->
<?
}