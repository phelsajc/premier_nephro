<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Premier | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../backend2/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../backend2/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../backend2/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../backend2/plugins/jqvmap/jqvmap.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../backend2/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../backend2/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../backend2/plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="../../pdfjs-dist/web/viewer.css">

</head>
<body class="hold-transition sidebar-mini">
<div id="app">
  
      <router-view></router-view>

</div>


<!-- jQuery -->
<script src="../../backend2/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../backend2/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../js/app.js"></script>
<script src="../../backend2/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../backend2/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../backend2/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../backend2/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../backend2/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<!-- Summernote -->
<!-- overlayScrollbars -->
<script src="../../backend2/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../backend2/dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script type="text/javascript">
  $( document ).ready(function() {
    let token = localStorage.getItem('token');
    if(token) {
        $("#sidebar").css("display","");
        $("#topbar").css("display","");
        $("#footer_div").css("display","");
        //  $("#user_name").text(localStorage.getItem('user_type'))
        if(localStorage.getItem('user_type')!="Administrator")
        {
          $("#usermenu").addClass("d-none")
        }else{
          $("#usermenu").removeClass("d-none")
        }
    }
  });
</script>
</body>
</html>
