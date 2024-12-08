<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Tutor Modal</title>
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
  <!-- Activity Logs Modal --> 
  <div class="modal fade" id="viewLogsModal" tabindex="-1" role="dialog" aria-labelledby="viewLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="viewLogsModalLabel"><b id="professorName">View Logs</b></h4>
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
<!-- Add New Tutor Modal -->
<div id="add" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="tutor_upload.php" method="post" enctype="multipart/form-data">
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
                  <p>RESET LIST OF TUTORS</p>
                  <h4>This will delete all data and counting back to 0.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <a href="tutor_reset.php" class="btn btn-danger btn-flat"><i class="fa fa-refresh"></i> Reset</a>
            </div>
        </div>
    </div>
</div>

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
              <form class="form-horizontal" method="POST" action="tutor_delete.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE TUTOR</p>
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

<!-- View Tutor Modal -->
<div class="modal fade" id="view">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>View Tutor</b></h4>
      </div>
      <div class="modal-body">
        <!-- Tutor Information -->
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label">Name</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_firstname"><?php echo isset($row['firstname']) ? $row['firstname'] : ''; ?></p>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-3 control-label">Surname</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_lastname"><?php echo isset($row['lastname']) ? $row['lastname'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Age</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_age"><?php echo isset($row['age']) ? $row['age'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Sex</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_sex"><?php echo isset($row['sex']) ? $row['sex'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Contact Number</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_number"><?php echo isset($row['number']) ? $row['number'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Barangay</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_barangay"><?php echo isset($row['barangay']) ? $row['barangay'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
          <label class="col-sm-3 control-label">Student ID</label>
          <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_student_id"><?php echo isset($row['student_id']) ? $row['student_id'] : ''; ?></p>
          </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Course</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_course"><?php echo isset($row['course']) ? $row['course'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Year</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_year_section"><?php echo isset($row['year_section']) ? $row['year_section'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Professor ID</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_professor"><?php echo isset($row['professor']) ? $row['professor'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Facebook Link</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_fblink"><?php echo isset($row['fblink']) ? $row['fblink'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Email Address</label>
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

<!-- View Event -->
<div class="modal fade" id="viewEvent">
  <div class="modal-dialog" style="max-width: 600px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>View Event</b></h4>
      </div>
      <div class="modal-body">
        <!-- Event Information -->
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label">Event</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_event_name"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Rendered Hours</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_rendered_hours"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_description"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Event Image</label>
            <div class="col-sm-9">
              <div id="view_attached_file" class="d-flex justify-content-center">
                <img src="" alt="Event Image" id="event_image">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Date Submitted</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_created_at"></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Remarks</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_remarks"></p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
     
      </div>
    </div>
  </div>
</div>

<!-- View Weekly Report -->
<div class="modal fade" id="viewWeeklyReport">
  <div class="modal-dialog" style="max-width: 600px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>View Weekly Progress</b></h4>
      </div>
      <div class="modal-body">
        <!-- Event Information -->
        <form class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label">Week</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_week_number"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Rendered Hours</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_weekly_rendered_hours"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Location</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_location"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Subject</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_subject"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_weekly_description"></p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Attached File</label>
            <div class="col-sm-9" id="view_weekly_attached_file">
              <!-- View button will be inserted here dynamically -->
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Date Submitted</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_date"></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Remarks</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_weekly_remarks"></p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>


<!-- CSS for Image Field Adjustment -->
<style>
  /* Custom styles for the image inside the modal */
.event-image {
  max-width: 100%; /* Makes sure the image doesn't exceed the container's width */
  max-height: 250px; /* Limit the image height */
  object-fit: contain; /* Maintain aspect ratio */
  display: block; /* Prevents any inline element issues */
  margin: 0 auto; /* Centers the image horizontally */
}

#view_attached_file {
  display: flex;
  justify-content: center; /* Centers the image horizontally */
  align-items: center; /* Centers the image vertically */
  width: 100%; /* Ensures the container takes the full width of the modal */
  padding-top: 10px;
  padding-bottom: 10px;
}
</style>

<!-- Accept Event  -->
<div class="modal fade" id="accept" tabindex="-1" role="dialog" aria-labelledby="acceptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
         <!-- Modal Header -->
         <div class="modal-header" style="background-color: #32a85e; color: white">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>Accepting...</b></h4>
      </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Form for Remarks and Submission -->
                <form action="accept_event.php" method="POST">
                    <!-- Event ID (Hidden Input) -->
                    <input type="hidden" id="event_id" name="event_id" value="">
                    <input type="hidden" id="tutor_id" name="tutor_id" value="">
                    <!-- Remarks Section -->
                    <div class="form-group">
                        <label for="remarks" class="control-label" style="font-weight: bold;">Remarks</label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Enter remarks here..." required></textarea>
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="modal-footer" style="border-top: none;">
                        <button type="submit" class="btn btn-success btn-flat" name="accept">
                            <i class="fa fa-check-circle"></i> Accept
                        </button>
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                            <i class="fa fa-close"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Accept weekly Progress -->
<div class="modal fade" id="weeeklyProgressAccept" tabindex="-1" role="dialog" aria-labelledby="acceptWeeklyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
         <!-- Modal Header -->
         <div class="modal-header" style="background-color: #32a85e; color: white">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>Accepting...</b></h4>
      </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Form for Remarks and Submission -->
                <form action="accept_weeklyRequest.php" method="POST">
                    <!-- Event ID (Hidden Input) -->
                    <input type="hidden" id="weekly_id3" name="weekly_id" value="">
                    <input type="hidden" id="tutor_id3" name="tutor_id" value="">

                    <!-- Remarks Section -->
                    <div class="form-group">
                        <label for="remarks" class="control-label" style="font-weight: bold;">Remarks</label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Enter remarks here..." required></textarea>
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="modal-footer" style="border-top: none;">
                        <button type="submit" class="btn btn-success btn-flat" name="accept">
                            <i class="fa fa-check-circle"></i> Accept
                        </button>
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                            <i class="fa fa-close"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Event Modal -->
<div class="modal fade" id="reject" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background-color: #DE4931; color: white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Rejecting...</b></h4>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Form for Remarks and Submission -->
                <form action="reject_event.php" method="POST">
                    <!-- Event ID (Hidden Input) -->
                    <input type="hidden" id="event_id2" name="event_id" value="">
                    <input type="hidden" id="tutor_id2" name="tutor_id" value="">

                    <!-- Remarks Section -->
                    <div class="form-group">
                        <label for="remarks" class="control-label" style="font-weight: bold;">Remarks</label>
                        <textarea id="remarks2" name="remarks" class="form-control" rows="3" placeholder="Enter remarks here..." required></textarea>
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="modal-footer" style="border-top: none;">
                        <button type="submit" class="btn btn-danger btn-flat" name="reject">
                            <i class="fa fa-times-circle"></i> Reject
                        </button>
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                            <i class="fa fa-close"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Weekly Progress Modal -->
<div class="modal fade" id="weeklyProgressReject" tabindex="-1" role="dialog" aria-labelledby="rejectWeeklyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background-color: #DE4931; color: white">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Rejecting...</b></h4>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Form for Remarks and Submission -->
                <form action="reject_weeklyRequest.php" method="POST">
                    <!-- Event ID (Hidden Input) -->
                    <input type="hidden" id="weekly_id4" name="weekly_id" value="">
                    <input type="hidden" id="tutor_id4" name="tutor_id" value="">

                    <!-- Remarks Section -->
                    <div class="form-group">
                        <label for="remarks" class="control-label" style="font-weight: bold;">Remarks</label>
                        <textarea id="remarks2" name="remarks" class="form-control" rows="3" placeholder="Enter remarks here..." required></textarea>
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="modal-footer" style="border-top: none;">
                        <button type="submit" class="btn btn-danger btn-flat" name="reject">
                            <i class="fa fa-times-circle"></i> Reject
                        </button>
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                            <i class="fa fa-close"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Reject All -->
<div class="modal fade" id="eventRejectAll">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Reject All...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>REJECTING ALL REQUEST</p>
                  <h4>This will reject all selected request.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <form action="rejectAllEventRequest.php" method="POST">
                <button type="submit" name="eventRejectAll" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-times-circle"></i> Reject All</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tutor Modal -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">
      <form class="form-horizontal" method="POST" action="tutor_edit.php" onsubmit="return validateForm()">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><b>Edit Tutor</b></h4>
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

<script>
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
</script>
          <div class="form-group">
            <label for="edit_age" class="col-sm-3 control-label">Age</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_age" name="age" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2)">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_sex" class="col-sm-3 control-label">Sex</label>
              <div class="col-sm-9">
                <select class="form-control" id="edit_sex" name="sex">
                  <option disabled>Sex</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_year_section" class="col-sm-3 control-label">Year & Section</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_year_section" name="year_section">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_student_id" class="col-sm-3 control-label">Student ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_student_id" name="student_id">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_fblink" class="col-sm-3 control-label">Facebook Link</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_fblink" name="fblink">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_emailaddress" class="col-sm-3 control-label">Email Address</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_emailaddress" name="emailaddress">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_professor" class="col-sm-3 control-label">Professor ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_professor" name="professor">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_number" class="col-sm-3 control-label">Contact Number</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_number" name="number" pattern="\d{11}" title="Please enter 11 digits only" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_barangay" class="col-sm-3 control-label">Barangay</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_barangay" name="barangay">
                <option disabled>Barangay</option>
                <option class="option" value="Arkong Bato">Arkong Bato</option>
                <option class="option" value="Bagbaguin">Bagbaguin</option>
                <option class="option" value="Bignay">Bignay</option>
                <option class="option" value="Bisig">Bisig</option>
                <option class="option" value="Canumayn">Canumay</option>
                <option class="option" value="Coloong">Coloong</option>
                <option class="option" value="Dalandanan">Dalandanan</option>
                <option class="option" value="Isla">Isla</option>
                <option class="option" value="Karuhatan">Karuhatan</option>
                <option class="option" value="Lawang Bato">Lawang Bato</option>
                <option class="option" value="Lingunan">Lingunan</option>
                <option class="option" value="Mabolo">Mabolo</option>
                <option class="option" value="Malanday">Malanday</option>
                <option class="option" value="Malinta">Malinta</option>
                <option class="option" value="Mapulang Lupa">Mapulang Lupa</option>
                <option class="option" value="Maysan">Maysan</option>
                <option class="option" value="Palasan">Palasan</option>
                <option class="option" value="Pariancillo Villa">Pariancillo Villa</option>
                <option class="option" value="Pasolo">Pasolo</option>
                <option class="option" value="Paso de Blas">Paso de Blas</option>
                <option class="option" value="Poblacion">Poblacion</option>
                <option class="option" value="Polo">Polo</option>
                <option class="option" value="Punturin">Punturin</option>
                <option class="option" value="Rincon">Rincon</option>
                <option class="option" value="Tagalag">Tagalag</option>
                <option class="option" value="Viente Reales">Viente Reales</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_course" class="col-sm-3 control-label">Course</label>
              <div class="col-sm-9">
                <select class="form-control" id="edit_course" name="course">
                  <option disabled>Select Course</option>
                  <option value="BECE">Bachelor of Early Childhood Education</option>
                  <option value="BSEE">Bachelor of Secondary Education Major in English</option>
                  <option value="BSEF">Bachelor of Secondary Education Major in Filipino</option>
                  <option value="BSEM">Bachelor of Secondary Education Major in Mathematics</option>
                  <option value="BSES">Bachelor of Secondary Education Major in Science</option>
                  <option value="BSESS">Bachelor of Secondary Education Major in Social Studies</option>
                  <option value="BSCE">BS Civil Engineering</option>
                  <option value="BSEE">BS Electrical Engineering</option>
                  <option value="BSIT">BS Information Technology</option>
                  <option value="BACTA">BA Communication Major in Theater Arts</option>
                  <option value="BSP">BS Psychology</option>
                  <option value="BSWS">BS Social Work</option>
                  <option value="BSA">BS Accountancy</option>
                  <option value="BSBAFM">BS Business Administration Major in Financial Management</option>
                  <option value="BSBAHRDM">BS Business Administration Major in Human Resource Development Management</option>
                  <option value="BSBAMM">BS Business Administration Major in Marketing Management</option>
                  <option value="BSPA">BS Public Administration</option>
                </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_password" class="col-sm-3 control-label">Password</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_password" name="password">
            </div>
          </div>
<div class="modal-footer">
    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
    <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Update</button>
</div>


<script>

document.addEventListener('DOMContentLoaded', function() {
    var studentIdInput = document.getElementById('edit_student_id');

    studentIdInput.addEventListener('input', function(event) {
        // Remove any non-digit characters
        var input = this.value.replace(/\D/g, '');

        // Ensure the input is limited to 6 digits
        input = input.slice(0, 6);

        // Format the input as "XX-XXXX"
        if (input.length > 2) {
            input = input.slice(0, 2) + '-' + input.slice(2);
        }

        // Update the input value
        this.value = input;
    });
});

function validateForm() {
  var firstname = document.getElementById("edit_firstname").value.trim();
  var lastname = document.getElementById("edit_lastname").value.trim();
  var age = document.getElementById("edit_age").value.trim();
  var sex = document.getElementById("edit_sex").value.trim();
  var number = document.getElementById("edit_number").value.trim();
  var barangay = document.getElementById("edit_barangay").value.trim();
  var studentId = document.getElementById("edit_student_id").value.trim();
  var course = document.getElementById("edit_course").value.trim();
  var year_section = document.getElementById("edit_year_section").value.trim();
  var fblink = document.getElementById("edit_fblink").value.trim();
  var emailaddress = document.getElementById("edit_emailaddress").value.trim();
  var professor = document.getElementById("edit_professor").value.trim();
  var preferredDay = document.getElementById("edit_preferred_day").value.trim();
  var preferredDay = document.getElementById("edit_preferred_subject").value.trim();
  var password = document.getElementById("edit_password").value.trim();

  if (
    !firstname ||
    !lastname ||
    !age ||
    !sex ||
    !number ||
    !barangay ||
    !studentId ||
    !course ||
    !fblink ||
    !emailaddress ||
    !year_section ||
    !professor ||
    !preferredDay ||
    !preferredSubject ||
    !password
  ) {
    alert("Please fill out all the required fields.");
    return false; // Prevent form submission
  }

  return true; // Allow form submission
}

</script>

</body>
</html>