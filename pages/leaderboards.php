<?
$leaderboards = Quizzes::getLeaderboards($quizSite->siteid);
$leaderboardOutput = '';
$legacyLeaderboards = '';

function renderLeaderboard($leaderboard,$legacy=false) {
	$date = "";
	try {
		$date = new DateTime($leaderboard['datetime']);
		$date = $date->format('l jS F Y - g:ia');
	} catch (Exception $e) {
	}

	$html = '';

	$html .= '<div class="col-md-6 col-sm-12 p-3"><div class="card"><div class="card-header text-center"><h4>' . $leaderboard['quizname'] . '<br /><small>' . $date . '</small></div><div class="card-body"><table class="table table-striped"><thead><tr><th>#</th><th>Team Name</th><th>Score</th></tr></thead><tbody>';

	foreach($leaderboard['data'] as $row) {
		$html .= '<tr><td>' . $row['pos'] . '</td><td>' . $row['teamname'] . '</td><td>' . $row['score'] . '</td></tr>';
	}

	$html .= '</tbody></table></div></div></div>';

	return $html;
}

if(file_exists(WEBSITE_DOCROOT . '/sites/legacy-leaderboards/' . $quizSite->siteid . '.php')) {
	include_once(WEBSITE_DOCROOT . '/sites/legacy-leaderboards/' . $quizSite->siteid . '.php');
	if(function_exists('getLegacyLeaderboards')) {
		getLegacyLeaderboards();
	}
}

renderSiteHeader(!$auth->isLoggedIn() ? 'layout-top-nav' : '');

if(count($leaderboards) > 0) {
	foreach($leaderboards as $leaderboard) {
		$leaderboardOutput .= renderLeaderboard($leaderboard);
	}
} else {
	$leaderboardOutput .= '<div class="col-12 p-3"><div class="card"><div class="card-header text-center">No leaderboards have been published yet.</div></div></div>';
}

if(!$auth->isLoggedIn()) {
?>
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="/index3.html" class="navbar-brand">
        <img src="/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light"><?=$quizSite->getData('sitename')?></span>
      </a>

      <!-- Right navbar links -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
		<li class="nav-item">
			<a href="/login" class="nav-link">Login</a>
		</li>
		<li class="nav-item">
			<a href="/leaderboards" class="nav-link">Leaderboards</a>
		</li>
      </ul>
    </div>
  </nav>
  <!-- /.navbar -->
<?
}
?>
<div class="content-wrapper">
    <!-- Main content -->
    <div class="content">
    	<div class="container">
			<div class="row">
				<div class="col-12 mt-2"><div class="card-header p-2 text-center"><h3><?=$quizSite->sitename?> Leaderboards</div></div>
			</div>
			<div class="row">
				<?=$leaderboardOutput?>
			</div>
			<? if($legacyLeaderboards != '') { ?>
			<div class="row">
				<div class="col-12"><div class="card-header p-2"><h3>Legacy Leaderboards <span class="float-right"><small>Quizzes played on v1</small></span></h3></div></div>
			</div>
			<div class="row">
				<?=$legacyLeaderboards?>
			</div>
			<? } ?>
		</div>
	</div>
</div>
<?
renderSiteFooter('');
?>