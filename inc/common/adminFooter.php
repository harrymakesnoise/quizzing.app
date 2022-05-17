<?
function renderSiteFooter() {
  global $footerScripts, $requestedPage;
?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; <?=date('Y')?> <a href="https://quizzing.app">Quizzing.APP</a>.</strong>
    All rights reserved. | <small><a href="/terms-conditions" target="_blank">Terms &amp; Conditions</a></small> | <small><a href="/privacy-policy" target="_blank">Privacy Policy</a></small>
    <div class="float-right d-none d-sm-inline-block">
      <span id="mc-ping-out"></span><b>Version</b> 2.0.2
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="dist/js/demo.js"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="plugins/raphael/raphael.min.js"></script>
<script src="plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="plugins/jquery-mapael/maps/usa_states.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- PAGE SCRIPTS -->
<? if($requestedPage == 'dashboard') { ?>
<script src="dist/js/pages/dashboard2.js"></script>
<? } ?>
<?=$footerScripts?>
<!-- NAV BAR -->
<script>
var baseURL = $("base").attr("href");
var clientURL = window.location.href;
var pageURL = clientURL.replace(baseURL, '');
var pageURLParts = pageURL.split("/");
var newPageURL = '';
var pageURLLength = (pageURLParts.length >= 3 ? 3 : pageURLParts.length);
for(var i=0;i<pageURLLength;i++) {
  newPageURL += pageURLParts[i] + "/";
}
newPageURL = newPageURL.slice(0, -1);
var currentLink = $("a[href='" + newPageURL + "']");
if(currentLink.length) {
  currentLink.addClass("active");
  var subParent = currentLink.parent().parent();
  if(subParent.hasClass('nav-treeview')) {
    if(subParent.parent().hasClass('has-treeview')) {
      subParent.parent().addClass('menu-open');
    }
  }
}
</script>
</body>
</html>
<?
}
?>