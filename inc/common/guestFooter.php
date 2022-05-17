<?
function renderSiteFooter($class='login-box') {
?>
<div class="<?=$class?>">
	<div class="card mb-0">
	    <div class="card-body login-card-body w-100">
	    	<div class="row">
	    		<p class="text-center w-100">
	      			<a href="/leaderboards"><small>Leaderboards</small></a><br />
	      			<a href="/terms-conditions"><small>Terms & Conditions</small></a><br />
	      			<a href="/privacy-policy"><small>Privacy Policy</small></a>
	      		</p>
	      	</div>
	      	<p class="card-text text-center"><small>Copyright &copy; <?=date('Y')?> <a href="https://quizzing.app" target="_blank">Quizing.APP</a>. All rights reserved.</small></p>
	    </div>
		<!-- /.login-card-body -->
	</div>
</div>
<!-- /.login-box -->
<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="/dist/js/adminlte.min.js"></script>

</body>
</html>
<?
}
?>