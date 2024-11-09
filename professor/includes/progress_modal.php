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

<!-- View -->
<div class="modal fade" id="view">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="bold fullname"></h4>
            </div>
            <div class="modal-body">
                <div class="uploaded-images-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>

