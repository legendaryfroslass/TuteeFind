<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<style>
  .scrollable-table {
    max-height: 230px;
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
$sql = "
    SELECT t.id, t.lastname, t.firstname, t.student_id, t.course, t.year_section
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ?
    AND (
        CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE LOWER(?) OR
        CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE LOWER(?) OR
        LOWER(t.student_id) LIKE LOWER(?)
    )
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("isssii", $professor_id, $search_param, $search_param, $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Query to get total count for pagination
$total_sql = "
    SELECT COUNT(*) as total
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ?
    AND (
        CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE LOWER(?) OR
        CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE LOWER(?) OR
        LOWER(t.student_id) LIKE LOWER(?)
    )";

$stmt_total = $conn->prepare($total_sql);
$stmt_total->bind_param("isss", $professor_id, $search_param, $search_param, $search_param);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
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
      <h1>
        Tutor's List
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Tutor</li>
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
            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Accept All</button>
            <button class='btn btn-danger btn-sm archiveTutor btn-flat' data-id='".$row['id']."'><i class='fa fa-archive'></i> Reject All</button>     
              <a href="tutor_pdf.php?search=<?php echo urlencode($search); ?>" class="btn btn-primary btn-sm btn-flat" target="_blank">
                <i class="fa fa-file-pdf-o"></i> Export to PDF
              </a>
            </div>
            
            <div class="box-body">
            
<!-- Search Form --> 
<form method="GET" action="tutor_request.php" class="form-inline d-flex justify-content-between align-items-center">
    <div class="form-group me-4"> 
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
      <div style="max-height: 270px; overflow-y: auto;" class="scrollable-table">
        <table id="example1" class="table table-bordered dataTable no-footer" role="grid" aria-describedby="example1_info">
          <thead>
            <tr role="row">
              <th><input type="checkbox" id="selectAll"></th> <!-- Checkbox for selecting all -->
              <th onclick="sortTable(1)">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(2)">Student ID <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(3)">Course: Year & Section <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(4)">Rendered Hour/s <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(5)">Status <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
if (isset($_SESSION['professor_id'])) {
    $professor_id = $_SESSION['professor_id'];
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%'; // Prepare search term for LIKE query

    $sql = "
        SELECT 
            t.id, 
            t.firstname, 
            t.lastname, 
            t.student_id, 
            t.course, 
            t.year_section
        FROM tutor t
        INNER JOIN professor p ON t.professor = p.faculty_id
        WHERE p.id = ?
        AND (
            CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE ? OR
            LOWER(t.student_id) LIKE ? OR
            CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE ?
        )
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $professor_id, $search, $search, $search); // Bind search term to relevant columns
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']); // Sanitize output
                $courseYearSection = htmlspecialchars($row['course']) . ' ' . htmlspecialchars($row['year_section']);
                echo "
                    <tr>
                        <td><input type='checkbox' class='selectRow' value='" . $row['id'] . "'></td>
                        <td>$name</td>
                        <td>" . htmlspecialchars($row['student_id']) . "</td>
                        <td>$courseYearSection</td>
                        <td></td>
                        <td></td>
                        <td>
                            <button class='btn btn-primary btn-sm btn-flat view' data-id='".$row['id']."'><i class='fa fa-eye'></i> View</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Accept</button>
                            <button class='btn btn-danger btn-sm archiveTutor btn-flat' data-id='".$row['id']."'><i class='fa fa-archive'></i> Reject</button>                        </td>
                    </tr>
                ";
            }
        } else {
            echo "<tr><td colspan='4'>No records found.</td></tr>";
        }
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "<tr><td colspan='4'>No faculty ID found for the current professor.</td></tr>";
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
                    Showing <?php echo ($offset + 1) . ' to ' . min($offset + $limit, $total_rows) . ' of ' . $total_rows . ' entries'; ?>
                  </div>
                </div>
                <div class="col-sm-7">
                  <div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
                    <ul class="pagination">
                      <li class="paginate_button previous <?php if ($page <= 1) echo 'disabled'; ?>" id="example1_previous">
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" aria-controls="example1" data-dt-idx="0" tabindex="0">Previous</a>
                      </li>

                      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="paginate_button <?php if ($page == $i) echo 'active'; ?>">
                          <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" aria-controls="example1" data-dt-idx="<?php echo $i; ?>" tabindex="0"><?php echo $i; ?></a>
                        </li>
                      <?php endfor; ?>

                      <li class="paginate_button next <?php if ($page >= $total_pages) echo 'disabled'; ?>" id="example1_next">
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" aria-controls="example1" data-dt-idx="7" tabindex="0">Next</a>
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
<?php include 'includes/footer.php'; ?>
  <?php include 'includes/tutor_modal.php'; ?>
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
    url: 'tutor_view.php', // PHP file to handle the AJAX request and retrieve tutor information
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Populate the modal with the retrieved data
      $('#view_firstname').text(response.firstname);
      $('#view_lastname').text(response.lastname);
      $('#view_age').text(response.age);
      $('#view_sex').text(response.sex);
      $('#view_number').text(response.number);
      $('#view_barangay').text(response.barangay);
      $('#view_student_id').text(response.student_id);
      $('#view_course').text(response.course);
      $('#view_year_section').text(response.year_section);
      $('#view_professor').text(response.professor);
      $('#view_fblink').text(response.fblink);
      $('#view_emailaddress').text(response.emailaddress);
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

  $(document).on('click', '.archiveTutor', function(e){
    e.preventDefault();
    $('#archiveTutor').modal('show');
    var id = $(this).data('id');
    getRow(id);
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
    url: 'tutor_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.id);
      $('#edit_firstname').val(response.firstname);
      $('#edit_lastname').val(response.lastname);
      $('#edit_age').val(response.age);
      $('#edit_sex').val(response.sex);
      $('#edit_number').val(response.number);
      $('#edit_barangay').val(response.barangay);
      $('#edit_student_id').val(response.student_id);
      $('#edit_course').val(response.course);
      $('#edit_year_section').val(response.year_section);
      $('#edit_preferred_day').val(response.preferred_day);
      $('#edit_preferred_subject').val(response.preferred_subject);
      $('#edit_professor').val(response.professor);
      $('#edit_fblink').val(response.fblink);
      $('#edit_emailaddress').val(response.emailaddress);
      $('#edit_password').val(response.password);
      $('.fullname').html(response.firstname+' '+response.lastname);
    }
  });
}
</script>
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
    const tbody = table.querySelector("tbody");
    tbody.innerHTML = ""; // Clear current rows

    // Append sorted rows
    rows.forEach(row => tbody.appendChild(row));

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

// Submit the selected IDs for archiving
function archiveAllSelected() {
  if (selectedRows.length === 0) {
    alert('No tutor selected for archiving.');
    return;
  }

  // Create a form to submit the selected IDs
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'tutor_archiveAll.php';

  // Add the selected IDs as a hidden input
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_ids';
  input.value = JSON.stringify(selectedRows);
  form.appendChild(input);

  // Add a hidden field to indicate the archive action
  const archiveAction = document.createElement('input');
  archiveAction.type = 'hidden';
  archiveAction.name = 'archiveAll';
  archiveAction.value = 'true';
  form.appendChild(archiveAction);

  // Submit the form
  document.body.appendChild(form);
  form.submit();
}

  </script>

</body>
</html>

<!-- Archive All -->
<div class="modal fade" id="archiveAll">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Archiving All...</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                  <p>ARCHIVING LIST OF ALL TUTORS</p>
                  <h4>This will archive all data and counting back to 0.</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <form action="tutor_archiveAll.php" method="POST">
                <button type="submit" name="archiveAll" class="btn btn-warning btn-sm btn-flat"><i class="fa fa-archive"></i> Archive</button>
              </form>
            </div>
        </div>
    </div>
</div>
