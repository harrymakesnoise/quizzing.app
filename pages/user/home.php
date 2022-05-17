<?
$footerScripts .= '<script src="/dist/js/player.gameselection' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js" onLoad="loadGames()"></script>';
renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Choose Your Game</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/home">Home</a></li>
              <li class="breadcrumb-item active">Game Selection</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container">
        <div class="row" id="gameList">
          <div class="col-lg-6">
            <div class="card">
              <div class="overlay dark">
                <i class="fas fa-circle-notch fa-5x fa-spin"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"></h5>

                <p class="card-text">
                  <br /><br /><br /><br />
                </p>

                <a href="#" class="card-link"></a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="overlay dark">
                <i class="fas fa-circle-notch fa-5x fa-spin"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"></h5>

                <p class="card-text">
                  <br /><br /><br /><br />
                </p>

                <a href="#" class="card-link"></a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="overlay dark">
                <i class="fas fa-circle-notch fa-5x fa-spin"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"></h5>

                <p class="card-text">
                  <br /><br /><br /><br />
                </p>

                <a href="#" class="card-link"></a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="overlay dark">
                <i class="fas fa-circle-notch fa-5x fa-spin"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"></h5>

                <p class="card-text">
                  <br /><br /><br /><br />
                </p>

                <a href="#" class="card-link"></a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?
renderSiteFooter();
?>