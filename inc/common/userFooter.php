<?
function renderSiteFooter() {
  global $footerScripts;
  /*
?>
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->
<?
*/
?>
  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      <span id="mc-ping-out"></span><b>Version</b> 2.0.2
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?=date('Y')?> <a href="https://quizzing.app">Quizzing.APP</a>.</strong> All rights reserved. | <small><a href="/terms-conditions" target="_blank">Terms &amp; Conditions</a></small> | <small><a href="/privacy-policy" target="_blank">Privacy Policy</a></small>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="/dist/js/adminlte.min.js"></script>
<?=$footerScripts?>
</body>
</html>
<?
}
?>