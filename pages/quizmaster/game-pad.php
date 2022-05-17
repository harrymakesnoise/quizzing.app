<?
/*
  Page stages:
  0 - No state
  1 - Quiz Selected
*/
$pageStage = 0;
$quizid = getURLParam(1);
$quiz = new Quiz($quizid);

if($quizid != "") {
  if(intVal($quiz->getData('quizid')) > 0 && $quiz->canPlay()) {
    $pageStage = 1;
  } else {
    writeToast('error', 'Invalid game selected.');
    header("Location: /game-pad");
    exit();
  }
}

include_once('game-pad/stage-' . $pageStage . '.php');

renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Game-Pad</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item active">Game-Pad / Stage <?=$pageStage?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <?=renderGamePad()?>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?
renderSiteFooter();
?>