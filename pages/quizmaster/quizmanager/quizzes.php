<?
$filter = getURLParam(2, 'all');
$quizId = getURLParam(3);

$pageSubTitle = 'All Quizzes';
$dataTablesURL = '/api/lists/quizzes/';

if($filter == 'recover') {
  $pageSubTitle = 'Deleted Quizzes';
  $dataTablesURL .= 'recover/';
  if($quizId != '') {
    $quizzes = new Quizzes();
    $quizzes->recover($quizId);
    header("Location: /quiz-manager/quizzes/recover/");
    exit;
  }
} else if($filter == 'new' || $filter == 'edit') {
  $createEditPage = WEBSITE_DOCROOT . '/pages/quizmaster/create-edit/quiz-manager_quizzes.php';
  if(file_exists($createEditPage)) {
    include_once($createEditPage);
    exit;
  } else {
    header("Location: /quiz-manager/quizzes/");
    exit;
  }
} else if($filter == 'delete') {
  $quizzes = new Quizzes();
  $quizzes->delete($quizId);
  header("Location: /quiz-manager/quizzes/");
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
                <?=renderDataTableHeader(true,'/quizzes/')?>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="ajaxTable" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Qu-ID</th>
                    <th>Quiz Name</th>
                    <th>Rounds</th>
                    <th>Options</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                  <tr>
                    <th>Qu-ID</th>
                    <th>Quiz Name</th>
                    <th>Rounds</th>
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
        { "width": "10%", "targets": 2 },
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