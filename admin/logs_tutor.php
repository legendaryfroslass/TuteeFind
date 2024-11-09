<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archived Tutor's List</title>
  <!-- Include jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <!-- Include other meta tags, stylesheets, and scripts as needed -->
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <!-- Your HTML content -->

  <?php include 'includes/session.php'; ?>
  <?php include 'includes/header.php'; ?>

  <div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
        Tutors' Logs
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Tutors' Logs</li>
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
                <a href="#restoreAllTutor" data-toggle="modal" class="btn btn-warning btn-sm restoreAllTutor btn-flat"><i class="fa fa-refresh"></i> Restore All</a>
                <a href="#resett" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete All</a>
                <a href="archive_tutorpdf.php" class="btn btn-primary btn-sm btn-flat" target="_blank"><i class="fa fa-file-pdf-o"></i> Export to PDF</a>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Lastname</th>
                    <th>Firstname</th>
                    <th>Student ID</th>
                    <th>Course</th>
                    <th>Year & Section</th>
                    <th>Archive Time</th>
                    <th>Tools</th>
                  </thead>
                  <tbody>
                    <?php
                      $sql = "SELECT id, lastname, firstname, student_id, course, year_section, archive_at FROM archive_tutor";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        // Format the archived_at field
                        $formatted_date_time = date('d/m/Y h:i:s A', strtotime($row['archive_at']));
                        echo "
                          <tr>
                            <td>".$row['lastname']."</td>
                            <td>".$row['firstname']."</td>
                            <td>".$row['student_id']."</td>
                            <td>".$row['course']."</td>
                            <td>".$row['year_section']."</td>
                            <td>".$formatted_date_time."</td>
                            <td>
                              <button class='btn btn-warning btn-sm restore btn-flat' data-id='".$row['id']."'><i class='fa fa-refresh'></i> Restore</button>
                              <button class='btn btn-danger btn-sm deleteArchive btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
                            </td>
                          </tr>
                        ";
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

    <?php include 'includes/tutor_modal.php'; ?>
  </div>
  
  <?php include 'includes/scripts.php'; ?>

  <script>
     $(document).on('click', '.restore', function(e) {
      e.preventDefault();
      $('#restore').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });

    $(document).on('click', '.restoreAllTutor', function(e) {
      e.preventDefault();
      $('#restoreAllTutor').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });

    $(document).on('click', '.deleteArchive', function(e) {
      e.preventDefault();
      $('#deleteArchive').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });

    

    function getRow(id) {
      $.ajax({
        type: 'POST',
        url: 'tutor_row_archive.php',
        data: {id: id},
        dataType: 'json',
        success: function(response) {
          $('.id').val(response.id);
          $('#edit_firstname').val(response.firstname);
          $('#edit_lastname').val(response.lastname);
          $('#edit_student_id').val(response.student_id);
          $('#edit_course').val(response.course);
          $('#edit_year_section').val(response.year_section);
          $('#edit_preferred_day').val(response.preferred_day);
          $('#edit_preferred_subject').val(response.preferred_subject);
          $('#edit_professor').val(response.professor);
          $('#edit_fblink').val(response.fblink);
          $('#edit_emailaddress').val(response.emailaddress);

          $('.fullname').html(response.firstname + ' ' + response.lastname);
        }
      });
    }
  </script>
</body>
</html>

<!-- Reset -->
<div class="modal fade" id="resett">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Reseting...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>RESET LIST OF PROFESSOR</p>
                  <h4>This will delete all data and counting back to 0.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <a href="professor_reset.php" class="btn btn-danger btn-flat"><i class="fa fa-trash"></i> Delete All</a>
            </div>
        </div>
    </div>
</div>

