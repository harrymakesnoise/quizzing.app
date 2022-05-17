<?
$filter = getURLParam(1, 'all');
$questionID = getURLParam(2);

$pageSubTitle = 'All Questions';
$dataTablesURL = '/api/lists/questions/';

if($filter == 'recover') {
  $pageSubTitle = 'Deleted Questions';
  $dataTablesURL .= 'recover/';
  if($questionID != '') {
    $quizQuestion = new QuizQuestions();
    $quizQuestion->recover($questionID);
    header("Location: /questions/recover/");
    exit;
  }
}

if($filter == 'delete') {
  $quizQuestion = new QuizQuestions();
  $quizQuestion->delete($questionID);
  header("Location: /questions/");
  exit;
} 
renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1><?=$pageSubTitle?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <? if($filter == 'all') { ?>
                <li class="breadcrumb-item active">All Sites</li>
              <? } else { ?>
                <li class="breadcrumb-item"><a href="/sites">All Sites</a></li>
                <li class="breadcrumb-item active"><?=$pageSubTitle?></li>
              <? } ?>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <?=renderDataTableHeader()?>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="ajaxTable" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Q-ID</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Options</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                  <tr>
                    <th>Q-ID</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Options</th>
                  </tr>
                  </tfoot>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
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
  $("#ajaxTable").DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "rowReorder": true,
    "columnDefs": [
        { orderable: true, width: "10%", targets: 0 },
        { orderable: false, targets: "_all" },
        { "width": "30%", "targets": 2 },
        { "width": "18%", "targets": 3 },
    ],
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "processing": true,
    "serverSide": true,
    "ajax": "' . $dataTablesURL . '"
  });
</script>';
renderSiteFooter();
?>