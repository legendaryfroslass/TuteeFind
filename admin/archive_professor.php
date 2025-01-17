<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<style>
  .scrollable-table {
    max-height: 360px;
    overflow-y: auto;
    border-collapse: collapse;
    display: block;
  }
  .scrollable-table thead {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 1;
  }
  .scrollable-table::-webkit-scrollbar {
    width: 2px;
  }
  .scrollable-table::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  .scrollable-table::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 8px;
  }
  .scrollable-table::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
  /* Make sure the table takes up the full width */
table {
    width: 100%;
    border-collapse: collapse;
}

/* Add padding and border to table cells */
table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
}

/* Style for active/inactive status in the table */
table td.status {
    background-color: #f0f0f0;
    font-weight: bold;
}

/* Button styling for the actions column */
table td button {
    padding: 5px 10px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

/* Hover effect for buttons */
table td button:hover {
    background-color: #0056b3;
}
/* Center text in table headers */
table th {
    text-align: center;
}

/* Center text inside table body cells */
table td {
    text-align: center;
}

/* Optional: Adjust button alignment */
table td button {
    display: inline-block;
    text-align: center;
}
</style>

<?php
// Capture the search input if it exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get the current page and the number of entries to show
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default to 10
$offset = ($page - 1) * $limit;

// Modify your SQL query to include LIMIT and OFFSET for pagination
$sql = "SELECT id, lastname, firstname, middlename, faculty_id, archived_at FROM archive_professor 
        WHERE lastname LIKE '%$search%' 
        OR firstname LIKE '%$search%' 
        OR middlename LIKE '%$search%' 
        OR faculty_id LIKE '%$search%' 
        LIMIT $limit OFFSET $offset";
$query = $conn->query($sql);

// Get total records for pagination
$total_sql = "SELECT COUNT(*) as total FROM archive_professor 
              WHERE lastname LIKE '%$search%' 
              OR firstname LIKE '%$search%' 
              OR middlename LIKE '%$search%' 
              OR archived_at LIKE '%$search%' 
              OR faculty_id LIKE '%$search%'";

$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Archived Professors' List</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Archived Professor</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if (isset($_SESSION['error'])) {
          echo "<div class='alert alert-danger alert-dismissible'>
                  <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                  <h4><i class='icon fa fa-warning'></i> Error!</h4>
                  " . $_SESSION['error'] . "
                </div>";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success alert-dismissible'>
                  <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                  <h4><i class='icon fa fa-check'></i> Success!</h4>
                  " . $_SESSION['success'] . "
                </div>";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <button type="button" class="btn btn-warning btn-sm restoreAll btn-flat" onclick="restoreAllSelected()"><i class="fa fa-refresh"></i> Restore All</button>
              <button type="button" class="btn btn-danger btn-sm deleteAll btn-flat" onclick="deleteAllSelected()"><i class="fa fa-trash"></i> Delete All</button>
              <a href="archive_professorpdf.php" class="btn btn-primary btn-sm btn-flat" target="_blank"><i class="fa fa-file-pdf-o"></i> Export to PDF</a>
            </div>

            <div class="box-body">
  <!-- Search Form -->
  <form method="GET" action="archive_professor.php" class="form-inline d-flex justify-content-between align-items-center">
    <div class="form-group me-4"> <!-- Dropdown for showing entries -->
        <label>Show 
            <select name="limit" class="form-control" onchange="this.form.submit()">
                <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25</option>
                <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
            </select> entries 
        </label>
        
        <div class="form-group me-2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="text" name="search" placeholder="" value="<?php echo htmlspecialchars($search); ?>" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
    </div>
      </form>

  <div id="example1_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
    <div class="row">
      <div class="col-sm-12">
        <!-- Add a wrapper div with custom styles for scrolling -->
        <div style="max-height: 360px; overflow-y: auto;" class="scrollable-table">
  <table id="example1" class="table table-bordered dataTable no-footer" role="grid" aria-describedby="example1_info">
  <thead>
  <tr role="row">
    <th><input type="checkbox" id="selectAll"></th> <!-- Checkbox for selecting all -->
    <th onclick="sortTable(1)">Lastname <i class="fa fa-sort" aria-hidden="true"></i></th>
    <th onclick="sortTable(2)">Firstname <i class="fa fa-sort" aria-hidden="true"></i></th>
    <th onclick="sortTable(3)">Middle Name <i class="fa fa-sort" aria-hidden="true"></i></th>
    <th onclick="sortTable(4)">Faculty ID <i class="fa fa-sort" aria-hidden="true"></i></th>
    <th onclick="sortTable(5)">Archive Time <i class="fa fa-sort" aria-hidden="true"></i></th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>
  <?php
  // $sql = "SELECT id, lastname, firstname, middlename, faculty_id, archived_at FROM archive_professor";
  $query = $conn->query($sql);  
    while ($row = $query->fetch_assoc()) {
      $formatted_date_time = date('d/m/Y h:i:s A', strtotime($row['archived_at']));
      echo "
        <tr role='row' class='odd'>
          <td><input type='checkbox' class='selectRow' value='" . $row['id'] . "'></td>
          <td class='sorting_1'>" . htmlspecialchars($row['lastname']) . "</td>
          <td>" . htmlspecialchars($row['firstname']) . "</td>
          <td>" . htmlspecialchars($row['middlename']) . "</td>
          <td>" . htmlspecialchars($row['faculty_id']) . "</td>
          <td>".$formatted_date_time."</td>
          <td>
            <button class='btn btn-warning btn-sm restore btn-flat' data-id='".$row['id']."'><i class='fa fa-refresh'></i> Restore</button>
            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
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
                <div class="row">
                  <div class="col-sm-5">
                  <div class="dataTables_info" id="example1_info" role="status" aria-live="polite">
  <?php
  if ($total_rows > 0) {
      echo 'Showing ' . ($offset + 1) . ' to ' . min($offset + $limit, $total_rows) . ' of ' . $total_rows . ' entries';
  } else {
      echo 'Showing 0 to 0 of 0 entries';
  }
  ?>
</div>

                  </div>
                  <div class="col-sm-7">
        <div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
            <ul class="pagination">
                <!-- Disable Previous button if on the first page -->
                <li class="paginate_button previous <?php if ($page <= 1) echo 'disabled'; ?>" id="example1_previous">
                    <a href="<?php echo ($page > 1) ? '?search=' . urlencode($search) . '&page=' . ($page - 1) . '&limit=' . $limit : '#'; ?>" aria-controls="example1" data-dt-idx="0" tabindex="0">Previous</a>
                </li>

                <?php
                // Display the first page
                if ($page > 3) {
                    echo '<li class="paginate_button"><a href="?search=' . urlencode($search) . '&page=1&limit=' . $limit . '" aria-controls="example1" data-dt-idx="1" tabindex="0">1</a></li>';
                }

                // Display the first ellipsis if needed
                if ($page > 4) {
                    echo '<li class="paginate_button disabled"><span>...</span></li>';
                }

                // Loop through pages, showing only a range of pages around the current page
                $start = max(2, $page - 2);
                $end = min($total_pages - 1, $page + 2);

                for ($i = $start; $i <= $end; $i++) {
                    echo '<li class="paginate_button ' . ($page == $i ? 'active' : '') . '">
                            <a href="?search=' . urlencode($search) . '&page=' . $i . '&limit=' . $limit . '" aria-controls="example1" data-dt-idx="' . $i . '" tabindex="0">' . $i . '</a>
                          </li>';
                }

                // Display the last ellipsis if needed
                if ($page < $total_pages - 3) {
                    echo '<li class="paginate_button disabled"><span>...</span></li>';
                }

                // Display the last page
                if ($page < $total_pages - 2) {
                    echo '<li class="paginate_button"><a href="?search=' . urlencode($search) . '&page=' . $total_pages . '&limit=' . $limit . '" aria-controls="example1" data-dt-idx="' . $total_pages . '" tabindex="0">' . $total_pages . '</a></li>';
                }
                ?>

                <!-- Disable Next button if on the last page -->
                <li class="paginate_button next <?php if ($page >= $total_pages) echo 'disabled'; ?>" id="example1_next">
                    <a href="<?php echo ($page < $total_pages) ? '?search=' . urlencode($search) . '&page=' . ($page + 1) . '&limit=' . $limit : '#'; ?>" aria-controls="example1" data-dt-idx="7" tabindex="0">Next</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
<?php include 'includes/professor_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>

<script>


<!-- Inside your professor.php file, add the following script -->


  $(document).on('click', '.restore', function(e) {
    e.preventDefault();
    $('#restore').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.restoreAll', function(e) {
    e.preventDefault();
    $('#restoreAll').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.delete', function(e) {
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  function getViewRow(id) {
    $.ajax({
      type: 'POST',
      url: 'professor_view.php',
      data: {id: id},
      dataType: 'json',
      success: function(response) {
        $('#view_firstname').text(response.firstname);
        $('#view_lastname').text(response.lastname);
        $('#view_middlename').text(response.middlename);
        $('#view_age').text(response.age);
        $('#view_birthday').text(response.birthday);
        $('#view_faculty_id').text(response.faculty_id);
        $('#view_employment_status').text(response.employment_status);
        $('#view_prof_password').text(response.prof_password);
        $('#view_prof_username').text(response.prof_username);
      }
    });
  }

  function getRow(id) {
    $.ajax({
      type: 'POST',
      url: 'professor_row_archive.php',
      data: {id: id},
      dataType: 'json',
      success: function(response) {
        $('.id').val(response.id);
        $('#edit_firstname').val(response.firstname);
        $('#edit_lastname').val(response.lastname);
        $('#edit_middlename').val(response.middlename);
        $('#edit_age').val(response.age);
        $('#edit_birthday').val(response.birthday);
        $('#edit_faculty_id').val(response.faculty_id);
        $('#edit_employment_status').val(response.employment_status);
        $('#edit_prof_password').val(response.prof_password);
        $('#edit_prof_username').val(response.prof_username);

        $('.fullname').html(response.firstname + ' ' + response.lastname);
      }
    });
  }

</script>
 
  <!-- for sorting -->
<script>
  let sortDirection = false;
  function sortTable(columnIndex) {
    const table = document.getElementById("example1");
    const rows = Array.from(table.querySelectorAll("tbody tr"));
    
    // Toggle sorting direction
    sortDirection = !sortDirection;
    const direction = sortDirection ? 1 : -1;

    // Sort rows
    rows.sort((a, b) => {
      const cellA = a.children[columnIndex].textContent.trim().toLowerCase();
      const cellB = b.children[columnIndex].textContent.trim().toLowerCase();

      if (cellA < cellB) {
        return -1 * direction;
      }
      if (cellA > cellB) {
        return 1 * direction;
      }
      return 0;
    });

    // Remove existing rows
    while (table.querySelector("tbody").firstChild) {
      table.querySelector("tbody").removeChild(table.querySelector("tbody").firstChild);
    }

    // Append sorted rows
    rows.forEach(row => table.querySelector("tbody").appendChild(row));

    // Update sort icon
    updateSortIcons(columnIndex);
  }

  function updateSortIcons(columnIndex) {
    const headers = document.querySelectorAll("#example1 thead th");
    headers.forEach((header, index) => {
      const icon = header.querySelector(".fa");
      if (icon) {
        if (index === columnIndex) {
          icon.className = sortDirection ? "fa fa-sort-up" : "fa fa-sort-down";
        } else {
          icon.className = "fa fa-sort";
        }
      }
    });
  }
</script>

<script>
  // Initialize an array to store selected row IDs
let selectedRows = JSON.parse(sessionStorage.getItem('selectedRows')) || [];

// Function to update the state of checkboxes based on selected rows
function updateCheckboxes() {
  document.querySelectorAll('.selectRow').forEach(checkbox => {
    if (selectedRows.includes(checkbox.value)) {
      checkbox.checked = true;
    }
  });
}

// Save selected rows to sessionStorage
function saveSelectedRows() {
  sessionStorage.setItem('selectedRows', JSON.stringify(selectedRows));
}

// When the page loads, update the checkboxes based on previously selected rows
document.addEventListener('DOMContentLoaded', () => {
  updateCheckboxes();

  // Event listener for individual row selection
  document.querySelectorAll('.selectRow').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
      const rowId = this.value;
      if (this.checked) {
        // Add rowId to selectedRows if it's not already in the array
        if (!selectedRows.includes(rowId)) {
          selectedRows.push(rowId);
        }
      } else {
        // Remove rowId from selectedRows if it is unchecked
        selectedRows = selectedRows.filter(id => id !== rowId);
      }
      saveSelectedRows(); // Save updated selected rows to sessionStorage
    });
  });

  // Event listener for "Select All" checkbox
  document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.selectRow').forEach(checkbox => {
      checkbox.checked = this.checked;
      const rowId = checkbox.value;
      if (this.checked) {
        if (!selectedRows.includes(rowId)) {
          selectedRows.push(rowId);
        }
      } else {
        selectedRows = [];
      }
    });
    saveSelectedRows(); // Save updated selected rows to sessionStorage
  });
});

// Submit the selected IDs for deletion
function deleteAllSelected() {
  if (selectedRows.length === 0) {
    alert('No professors selected for deletion.');
    return;
  }

  // Create a form to submit the selected IDs
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'professor_deleteAll.php';

  // Add the selected IDs as a hidden input
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_ids';
  input.value = JSON.stringify(selectedRows);
  form.appendChild(input);

  // Add a hidden field to indicate the delete action
  const deleteAction = document.createElement('input');
  deleteAction.type = 'hidden';
  deleteAction.name = 'deleteAll';
  deleteAction.value = 'true';
  form.appendChild(deleteAction);

  // Submit the form
  document.body.appendChild(form);
  form.submit();
}

// Submit the selected IDs for restoration
function restoreAllSelected() {
  if (selectedRows.length === 0) {
    alert('No professors selected for restoration.');
    return;
  }

  // Create a form to submit the selected IDs
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'professor_restoreAll.php';

  // Add the selected IDs as a hidden input
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_ids';
  input.value = JSON.stringify(selectedRows);
  form.appendChild(input);

  // Add a hidden field to indicate the delete action
  const restoreAction = document.createElement('input');
  restoreAction.type = 'hidden';
  restoreAction.name = 'restoreAll';
  restoreAction.value = 'true';
  form.appendChild(restoreAction);

  // Submit the form
  document.body.appendChild(form);
  form.submit();
}
</script>
</body>
</html>


<!-- Reset -->
<!-- <div class="modal fade" id="resett">
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
</div> -->





