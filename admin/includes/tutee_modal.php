<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Tutee Modal</title>
  <style>
    /* CSS for border box */
    .view-text {
      border: 1px solid #ccc; /* Add a border */
      padding: 10px; /* Add some padding for better appearance */
      border-radius: 4px; /* Add rounded corners */
    }
  </style>
</head>
</body>
<!-- View Tutee Modal -->
<div class="modal fade" id="view">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>View Tutee</b></h4>
      </div>
      <div class="modal-body">
        <!-- Tutee Information -->
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
            <label class="col-sm-3 control-label">Birthday</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_tutee_birthday"><?php echo isset($row['tutee_birthday']) ? $row['tutee_birthday'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Sex</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_sex"><?php echo isset($row['sex']) ? $row['sex'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Contact</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_number"><?php echo isset($row['number']) ? $row['number'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Guardian Name</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_guardianname"><?php echo isset($row['guardianname']) ? $row['guardianname'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Facebook Link</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_fblink"><?php echo isset($row['fblink']) ? $row['fblink'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Barangay</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_barangay"><?php echo isset($row['barangay']) ? $row['barangay'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">School</label>
            <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_school"><?php echo isset($row['school']) ? $row['school'] : ''; ?></p>
            </div>
          </div>
          <div class="form-group">
          <label class="col-sm-3 control-label">Grade</label>
          <div class="col-sm-9">
              <p class="form-control-static view-text" id="view_grade"><?php echo isset($row['grade']) ? $row['grade'] : ''; ?></p>
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

<!-- Restore Tutee -->
<div class="modal fade" id="restoreTutee">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Restoring...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="restore_tutee.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>RESTORE TUTOR</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-warning btn-flat" name="restoreTutee"><i class="fa fa-archive"></i> Restore</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore All -->
<div class="modal fade" id="restoreAllTutee">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Restoring All data...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>RESTORING LIST OF TUTEE</p>
                  <h4>This will restore all list of tutee.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                  <i class="fa fa-close"></i> Close
              </button>
              <form method="post" action="tutee_restoreAll.php">
                  <button type="submit" name="restoreAllTutee" class="btn btn-warning btn-sm btn-flat">
                      <i class="fa fa-refresh"></i> Restore All
                  </button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Archive -->
<div class="modal fade" id="archiveTutee">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Archiving...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="tutee_archive.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>ARCHIVE TUTEE</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-warning btn-flat" name="archiveTutee"><i class="fa fa-archive"></i> Archive</button>
              </form>
            </div>
        </div>
    </div>
</div>

Delete
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="tutee_delete.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE TUTEE</p>
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

<!-- Update Photo -->
<div class="modal fade" id="edit_photo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b><span class="fullname"></span></b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="tutee_photo.php" enctype="multipart/form-data">
                <input type="hidden" class="id" name="id">
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo</label>

                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-success btn-flat" name="upload"><i class="fa fa-check-square-o"></i> Update</button>
              </form>
            </div>
        </div>
    </div>
</div>



     