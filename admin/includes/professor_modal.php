<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Professor Modal</title>
  <style>
    /* CSS for border box */
    .view-text {
      border: 1px solid #ccc; /* Add a border */
      padding: 10px; /* Add some padding for better appearance */
      border-radius: 4px; /* Add rounded corners */
    }
  </style>
</head>
<body>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="professor_delete">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE PROFESSOR</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Archive -->
<div class="modal fade" id="archive">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Archiving...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="professor_archive">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>ARCHIVE PROFESSOR</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-warning btn-flat" name="archive"><i class="fa fa-archive"></i> Archive</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore -->
<div class="modal fade" id="restore">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Restoring...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="restore_professor">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>RESTORE PROFESSOR</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-warning btn-flat" name="restore"><i class="fa fa-archive"></i> Restore</button>
              </form>
            </div>
        </div>
    </div>
</div>


<!-- RestoreAll -->
<div class="modal fade" id="restoreAll">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Restoring...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>RESTORE LIST OF PROFESSOR</p>
                  <h4>This will restore the entire list of professors.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <!-- Change the anchor to a form that submits POST data -->
              <form action="professor_restoreAll" method="POST">
                  <input type="hidden" name="restoreAll" value="1">
                  <button type="submit" class="btn btn-warning btn-flat"><i class="fa fa-refresh"></i> Restore All</button>
              </form>
            </div>
        </div>
    </div>
</div>


<!-- Add New Professor Modal -->
<div id="addnew" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="professor_upload" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Upload Excel File</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="excel_file">Choose Excel File:</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xls,.xlsx" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" name="upload" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Professor Modal -->
<div class="modal fade" id="view">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>View Professor</b></h4>
      </div>
      <div class="modal-body">
        <!-- Professor Information -->
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label">Firstname</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_firstname"><?php echo isset($row['firstname']) ? $row['firstname'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Lastname</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_lastname"><?php echo isset($row['lastname']) ? $row['lastname'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Middle Name</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_middlename"><?php echo isset($row['middlename']) ? $row['middlename'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Age</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_age"><?php echo isset($row['age']) ? $row['age'] : ''; ?></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Faculty ID</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_faculty_id"><?php echo isset($row['faculty_id']) ? $row['faculty_id'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Email:</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_emailaddress"><?php echo isset($row['emailaddress']) ? $row['emailaddress'] : ''; ?></p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
      </div>
    </div>
  </div>
</div>

 <!-- Activity Logs Modal --> 
 <div class="modal fade" id="viewLogsModal" tabindex="-1" role="dialog" aria-labelledby="viewLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="viewLogsModalLabel"><b id="professorName">Professor Name</b></h4>
            </div>
            <div class="modal-body">
                <!-- Message for empty logs -->
                <div id="emptyLogsMessage" style="display: none; text-align: center; margin: 10px 0;">
                    No activity logs available.
                </div>
                
                <!-- Activity Logs Table -->
                <table class="table" id="logsTable">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated here via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal">
                    <i class='fa fa-close'></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Professor Modal -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">
      <form class="form-horizontal" method="POST" action="professor_edit" onsubmit="return validateForm()">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><b>Edit Professor</b></h4>
        </div>
        <div class="modal-body">
          <input type="hidden" class="id" name="id">
          <div class="form-group">
            <label for="edit_firstname" class="col-sm-3 control-label">Firstname</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_firstname" name="firstname">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_lastname" class="col-sm-3 control-label">Lastname</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_lastname" name="lastname">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_middlename" class="col-sm-3 control-label">Middle Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_middlename" name="middlename">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_age" class="col-sm-3 control-label">Age</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_age" name="age" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2)">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_faculty_id" class="col-sm-3 control-label">Faculty ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_faculty_id" name="faculty_id" pattern="\d{2}-\d{4}" title="Please enter the format XX-XXXX" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_emailaddress" class="col-sm-3 control-label">Email</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_emailaddress" name="emailaddress">
            </div>
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('.edit-button').click(function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'professor_row',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                console.log(response); // Log response to console for debugging
                // Populate all the edit modal form fields with the received data
                $('#edit_firstname').val(response.firstname);
                $('#edit_lastname').val(response.lastname);
                $('#edit_middlename').val(response.middlename);
                $('#edit_age').val(response.age);
                $('#edit_faculty_id').val(response.faculty_id);
                $('#edit_prof_password').val(response.employment_status);
                $('#edit_prof_username').val(response.employment_status);

                // Open the edit modal
                $('#edit').modal('show');
            },
            error: function(xhr, status, error) {
                alert('Error fetching professor data: ' + error);
            }
        });
    });
});

  function validateForm() {
    var firstname = document.getElementById("edit_firstname").value;
    var lastname = document.getElementById("edit_lastname").value;
    var middlename = document.getElementById("edit_middlename").value;
    var age = document.getElementById("edit_age").value;
    var facultyId = document.getElementById("edit_faculty_id").value;
    var prof_password = document.getElementById("edit_prof_password").value;
    var prof_username = document.getElementById("edit_prof_username").value;

    var emptyFields = [];

    // Check if any of the required fields are empty and store their IDs in the emptyFields array
    if (firstname === "") {
      emptyFields.push("edit_firstname");
    }
    if (lastname === "") {
      emptyFields.push("edit_lastname");
    }
    if (middlename === "") {
      emptyFields.push("edit_middlename");
    }
    if (age === "") {
      emptyFields.push("edit_age");
    }
    if (facultyId === "") {
      emptyFields.push("edit_faculty_id");
    }
    if (prof_password === "") {
      emptyFields.push("edit_prof_password");
    }
    if (prof_username === "") {
      emptyFields.push("edit_prof_username");
    }

    // If any fields are empty, provide a warning and highlight the empty fields
    if (emptyFields.length > 0) {
      alert("Please fill out all the required fields.");
      emptyFields.forEach(function(fieldId) {
        document.getElementById(fieldId).classList.add("is-invalid");
      });
      return false; // Prevent form submission
    }
    return true; // Allow form submission
  }
</script>


</body>
</html>

