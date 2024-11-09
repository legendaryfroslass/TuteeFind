<!-- jQuery 3 -->
<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="../plugins/iCheck/icheck.min.js"></script>
<!-- Moment JS -->
<script src="../bower_components/moment/moment.js"></script>
<!-- DataTables -->
<!-- <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script> 
<script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script> -->
<!-- ChartJS -->
<script src="../bower_components/chart.js/Chart.js"></script>
<!-- ChartJS Horizontal Bar -->
<script src="../bower_components/chart.js/Chart.HorizontalBar.js"></script>
<!-- daterangepicker -->
<script src="../bower_components/moment/min/moment.min.js"></script>
<script src="../bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="../plugins/timepicker/bootstrap-timepicker.min.js"></script>
<!-- Slimscroll -->
<script src="../bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="../bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- Include Bootstrap Datepicker CSS and JS files -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>


<!-- Active Script -->
<script>
$(function(){
	/** add active class and stay opened when selected */
	var url = window.location;

	// for sidebar menu entirely but not cover treeview
	$('ul.sidebar-menu a').filter(function() {
	    return this.href == url;
	}).parent().addClass('active');

	// for treeview
	$('ul.treeview-menu a').filter(function() {
	    return this.href == url;
	}).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');

});
</script>

<!-- Data Table Initialize -->
<script>
  $(function () {
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>

<!-- Date and Timepicker -->
<script>
$(document).ready(function(){
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd', // Set the date format as per your preference
        autoclose: true,
        startView: 'decade', // Show decades first for faster year selection
    });
    // Restrict input to only integers for age input
    $('#edit_age').on('input', function() {
      var age = $(this).val().replace(/\D/g,''); // Remove non-numeric characters
      $(this).val(age);
    });
  // JavaScript to format the input as XX-XXXX upon saving
  document.getElementById('edit_faculty_id').addEventListener('change', function() {
    var inputValue = this.value;
    if (inputValue.length === 6) {
      var formattedValue = inputValue.slice(0, 2) + '-' + inputValue.slice(2);
      this.value = formattedValue;
    }
  });
   // JavaScript to accept only letters in the input fields
   document.getElementById('edit_firstname').addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z]/g, '');
  });
  
  document.getElementById('edit_lastname').addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z]/g, '');
  });
  
  document.getElementById('edit_middlename').addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z]/g, '');
  });
});


</script>
