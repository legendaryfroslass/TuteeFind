<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tutee Page</title>
  <!-- Include jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <!-- Other meta tags, stylesheets, and scripts -->
</head>
<body>
  <!-- Your HTML content -->
</body>
</html>

<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
      Tutees' Logs
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Tutees' Logs</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
            <a href="#deleteAllTutee" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete All</a>
            <a href="#restoreAllTutee" data-toggle="modal" class="btn btn-warning btn-sm restoreAllTutee btn-flat"><i class="fa fa-refresh"></i> Restore All</a>
            <a href="archive_tutee_pdf.php" class="btn btn-primary btn-sm btn-flat" target="_blank"><i class="fa fa-file-pdf-o"></i> Export to PDF</a>
            </div>
            <div class="box-body">

              <table id="example1" class="table table-bordered">
                <thead>
                <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Barangay</th>
                  <th>Contact No.</th>
                  <th>Age</th>
                  <th>Birthday</th>
                  <th>School</th>
                  <th>Grade</th>
                  <th>Tools</th>
                </thead>
                <tbody>
                <?php
$sql = "SELECT id, firstname, lastname, barangay, number, age, tutee_birthday, school, grade FROM archive_tutee";
$query = $conn->query($sql);
while ($row = $query->fetch_assoc()) {
    echo "
        <tr>
            <td>".$row['firstname']."</td>
            <td>".$row['lastname']."</td>
            <td>".$row['barangay']."</td>
            <td>".$row['number']."</td>
            <td>".$row['age']."</td>
            <td>".$row['tutee_birthday']."</td>
            <td>".$row['school']."</td>
            <td>".$row['grade']."</td>
            <td>
                <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
                <button class='btn btn-warning btn-sm restoreTutee btn-flat' data-id='".$row['id']."'><i class='fa fa-refresh'></i> Restore</button>
            </td>
            </tr>";
                }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>   
  </div>
  <?php include 'includes/tutee_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  $(document).on('click', '.view', function(e){
    e.preventDefault();
    $('#view').modal('show'); // Display the view modal
    var id = $(this).data('id');
    getViewRow(id);
  });
});

function getViewRow(id){
  $.ajax({
    type: 'POST',
    url: 'tutee_view.php', // PHP file to handle the AJAX request and retrieve tutee information
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Populate the modal with the retrieved data
      $('#view_firstname').text(response.firstname);
      $('#view_lastname').text(response.lastname);
      $('#view_age').text(response.age);
      $('#view_tutee_birthday').text(response.tutee_birthday);
      $('#view_sex').text(response.sex);
      $('#view_number').text(response.number);
      $('#view_guardianname').text(response.guardianname);
      $('#view_fblink').text(response.fblink);
      $('#view_emailaddress').text(response.emailaddress);
      $('#view_barangay').text(response.barangay);
      $('#view_school').text(response.school);
      $('#view_grade').text(response.grade);
   
    }
  });
}


$(function(){
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  

  $(document).on('click', '.restoreAllTutee', function(e) {
    e.preventDefault();
    $('#restoreAllTutee').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.deleteAllTutee', function(e) {
    e.preventDefault();
    $('#deleteAllTutee').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(function(){
  $(document).on('click', '.restoreTutee', function(e){
    e.preventDefault();
    $('#restoreTutee').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
});

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'tutee_row_archive.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.id);
      $('#edit_firstname').val(response.firstname);
      $('#edit_lastname').val(response.lastname);
      $('#edit_age').val(response.age);
      $('#edit_sex').val(response.sex);
      $('#edit_number').val(response.number);
      $('#edit_guardianname').val(response.guardianname);
      $('#edit_fblink').val(response.fblink);
      $('#edit_emailaddress').val(response.emailaddress);
      $('#edit_barangay').val(response.barangay);
      $('#edit_preferred_subject').val(response.preferred_subject);
      $('#edit_preferred_day').val(response.preferred_day);
      $('.fullname').html(response.firstname+' '+response.lastname);
      
    }
  });
}
</script>
</body>
</html>

<div class="modal fade" id="deleteAllTutee">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Resetting...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>RESET LIST OF TUTEE</p>
                  <h4>This will delete all data and counting back to 0.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <!-- Wrap the Delete All button inside a form -->
              <form method="POST" action="tutee_reset.php">
                  <input type="hidden" name="deleteAllTutee" value="1">
                  <button type="submit" class="btn btn-danger btn-flat"><i class="fa fa-trash"></i> Delete All</button>
              </form>
            </div>
        </div>
    </div>
</div>

