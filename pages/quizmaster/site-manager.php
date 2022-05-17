<?
$filter = getURLParam(1);
$siteID = $quizSite->siteid;
$dataTablesURL = '/api/lists/users/all/';

if(getCleanRequestParam('process') != '') {
  $class = new QuizSites();
  if($class->createEdit($siteID)) {
    header("Location: /site-manager");
    exit;
  }
}

$setSiteId = null;
$loadSite  = false;
if($siteID != '') {
  $setSiteId = intVal($siteID);
  $loadSite  = true;
}
$selectedQuizSite = new QuizSite($setSiteId, $loadSite);
$siteName         = getCleanRequestParam('sitename', $selectedQuizSite->getData('sitename'));
$siteOwner        = getCleanRequestParam('siteowner', $selectedQuizSite->getData('siteowner'));
$siteURL          = getCleanRequestParam('siteurl', $selectedQuizSite->getData('siteurl'));

if($quizUser->isAdmin()) {
  $canEditQuotas    = '';
  $siteEnabled      = getCleanRequestParam('siteenabled', $selectedQuizSite->getData('siteenabled')) == 'on';
  $maxPlayers       = getCleanRequestParam('maxplayers', $selectedQuizSite->getData('maxplayers'));
  $siteStorage      = getCleanRequestParam('sitefilestorage', $selectedQuizSite->getData('maxfilestorage'));
  $isCapped         = getCleanRequestParam('sitelimitatquota', $selectedQuizSite->getData('limitatquota')) == 'on';
} else {
  $canEditQuotas    = 'disabled';
  $siteEnabled      = $selectedQuizSite->getData('siteenabled') == 'on';
  $maxPlayers       = $selectedQuizSite->getData('maxplayers');
  $siteStorage      = $selectedQuizSite->getData('maxfilestorage');
  $isCapped         = $selectedQuizSite->getData('limitatquota') == 'on';
}

$headerScripts .= '  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">';
renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Site Manager</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item active">Site Manager</li>
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
                  <? if($quizUser->isAdmin()) { ?>
                  <div class="row">
                    <div class="col-6">
                      <label for="fixedQuotaInput">Site Enabled</label>
                    </div>
                    <div class="col-6 text-right">
                      <input type="checkbox" name="siteenabled" id="siteenabled" <?=($siteEnabled ? 'checked' : '')?> data-bootstrap-switch <?=$canEditQuotas?>>
                    </div>
                  </div>
                  <? } ?>
                  
                  <div class="form-group">
                    <label for="sitenameInput">Site Name</label>
                    <input type="text" class="form-control" id="sitenameInput" name="sitename" placeholder="" value="<?=$siteName?>">
                  </div>
                  <div class="form-group">
                    <label for="siteownerInput">Site Owner</label>
                    <input type="text" class="form-control" id="siteownerInput" name="siteowner" placeholder="" value="<?=$siteOwner?>">
                  </div>
                  <div class="form-group">
                    <label for="siteurlInput">Site URL</label>
                    <input type="text" class="form-control" id="siteurlInput" name="siteurl" placeholder="" value="<?=$siteURL?>" <?=$canEditQuotas?>>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
              <!-- Application buttons -->
              <div class="card">
                <div class="card-header bg-primary">
                  <h3 class="card-title">Quotas</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="maxplayersInput">Max Players</label>
                    <input type="number" min="0" step="1" class="form-control" id="maxplayersInput" name="maxplayers" value="<?=$maxPlayers?>" <?=$canEditQuotas?>>
                  </div>
                  <div class="form-group">
                    <label for="sitefilestorageInput">Total Media Space (MB)</label>
                    <input type="number" class="form-control" id="sitefilestorageInput" name="sitefilestorage" value="<?=$siteStorage?>" <?=$canEditQuotas?>>
                  </div>
                  <div class="row">
                    <div class="col-6">
                      <label for="fixedQuotaInput">Cap site at quotas</label>
                    </div>
                    <div class="col-6 text-right">
                      <input type="checkbox" name="sitelimitatquota" <?=($isCapped ? 'checked' : '')?> data-bootstrap-switch  <?=$canEditQuotas?>>
                    </div>
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
    "searching": false,
    "rowReorder": true,
    "columnDefs": [
        { orderable: true, width: "10%", targets: 0 },
        { orderable: false, targets: "_all" },
        { "width": "30%", "targets": 2 },
        { "width": "13%", "targets": 3 },
        { "width": "13%", "targets": 4 },
        { "width": "12%", "targets": 5 },
    ],
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "processing": true,
    "serverSide": true,
    "ajax": "' . $dataTablesURL . '"
  });
</script>';
$footerScripts .= '<!-- Bootstrap Switch --><script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script><script>    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch("state", $(this).prop("checked"));
    });</script>';
renderSiteFooter();
?>