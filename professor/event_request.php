<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; 

?>

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
// Search and pagination variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$professor_id = $_SESSION['professor_id'];

// Modify your SQL query to include LIMIT and OFFSET for pagination
// Query to fetch data with pagination
// SQL query to fetch tutor data with pagination and search
$sql = "
    SELECT 
        t.id AS tutor_id, 
        t.firstname, 
        t.lastname, 
        t.student_id, 
        t.course, 
        t.year_section, 
        e.id AS event_id, 
        e.rendered_hours, 
        e.status,
        e.remarks
    FROM tutor t
    INNER JOIN events e ON t.id = e.tutor_id
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ?
    AND (
        CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE ? OR
        LOWER(t.student_id) LIKE ? OR
        CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE ?
    )
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("isssii", $professor_id, $search_param, $search_param, $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();


// Query to get the total count for pagination
$total_sql = "
    SELECT COUNT(*) AS total
    FROM tutor t
    INNER JOIN events e ON t.id = e.tutor_id
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ?
    AND (
        CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE ? OR
        LOWER(t.student_id) LIKE ? OR
        CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE ?
    )
";

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
Event Request
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Event Request</li>
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
            <!-- <div class="box-header with-border">
            <button class='btn btn-success btn-sm eventAcceptAll btn-flat' data-id='".$row['event_id']."'><i class="fa fa-check-circle mr-2"></i> Accept All</button>
            <button class='btn btn-danger btn-sm btn-flat eventRejectAll' data-id='" . $row['event_id'] . "'>
                    <i class='fa fa-times-circle mr-2'></i> Reject All
                </button>  
            </div> -->
            
            <div class="box-body">
            
<!-- Search Form --> 
<form method="GET" action="event_request.php" class="form-inline d-flex justify-content-between align-items-center">
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
              <th onclick="sortTable(1)">Tutor <i class="fa fa-sort" aria-hidden="true"></i></th>
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
      t.id AS tutor_id, 
      t.firstname, 
      t.lastname, 
      t.student_id, 
      t.course, 
      t.year_section, 
      e.id AS event_id, 
      e.rendered_hours, 
      e.status,
      e.remarks
  FROM tutor t
  INNER JOIN events e ON t.id = e.tutor_id
  INNER JOIN professor p ON t.professor = p.faculty_id
  WHERE p.id = ?
  AND (
      CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE ? OR
      LOWER(t.student_id) LIKE ? OR
      CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE ?
  )
  LIMIT ? OFFSET ?
";

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param("isssii", $professor_id, $search_param, $search_param, $search_param, $limit, $offset);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']); // Sanitize output
        $courseYearSection = htmlspecialchars($row['course']) . ' ' . htmlspecialchars($row['year_section']);
        $renderedHours = htmlspecialchars($row['rendered_hours']);
        $status = htmlspecialchars($row['status']);

        // Define button color based on status
        $statusClass = '';
        $statusText = '';

        switch ($status) {
            case 'pending':
                $statusClass = 'btn-warning'; // Yellow for pending
                $statusText = 'Pending';
                break;
            case 'accepted':
                $statusClass = 'btn-success'; // Green for accepted
                $statusText = 'Accepted';
                break;
            case 'rejected':
                $statusClass = 'btn-danger'; // Red for rejected
                $statusText = 'Rejected';
                break;
            default:
                $statusClass = 'btn-secondary'; // Default for undefined status
                $statusText = 'Unknown';
                break;
        }

        // Render the row with conditional buttons
        echo "
            <tr>
                <td><input type='checkbox' class='selectRow' value='" . $row['tutor_id'] . "'></td>
                <td>$name</td>
                <td>" . htmlspecialchars($row['student_id']) . "</td>
                <td>$courseYearSection</td>
                <td>$renderedHours</td>
                <td style='text-align: center;'>
                    <button class='btn $statusClass btn-sm' style='border-radius: 10px; padding: 1px 10px; width: 100px;'>
                        $statusText
                    </button>
                </td>
                <td>
                    <button class='btn btn-primary btn-sm btn-flat viewEvent' data-id='" . $row['event_id'] . "'>
                        <i class='fa fa-eye'></i> View
                    </button>";

        // Conditionally render Accept and Reject buttons
        if ($status === 'pending') {
            echo "
                <button class='btn btn-success btn-sm accept' data-id='" . $row['event_id'] . "' data-tutor-id='" . $row['tutor_id'] . "'>
                    <i class='fa fa-check-circle mr-2'></i> Accept
                </button>
                <button class='btn btn-danger btn-sm btn-flat reject' data-id='" . $row['event_id'] . "' data-tutor-id='" . $row['tutor_id'] . "'>
                    <i class='fa fa-times-circle mr-2'></i> Reject
                </button>";
        }

        echo "
                </td>
            </tr>
        ";
    }
}

  } else {
      echo "<tr><td colspan='7'>No records found</td></tr>";
  }
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

$(function() { 
  $(document).on('click', '.viewEvent', function(e) {
    e.preventDefault();
    $('#viewEvent').modal('show');
    var id = $(this).data('id');
    getViewRow(id); // Fetch event details
  });
});

$(function() { 
  $(document).on('click', '.accept', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var tutorId = $(this).data('tutor-id');
    $('#accept').modal('show');
    // Set the event_id in the hidden field when the modal is shown
    $('#event_id').val(id);
    $('#tutor_id').val(tutorId);
    getViewRow(id); // Fetch event details
  });
});

$(function() {
    $(document).on('click', '.reject', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var tutorId = $(this).data('tutor-id');  // Get the event ID from the button's data-id attribute
        $('#reject').modal('show');   // Show the modal
        
        // Dynamically set the event_id in the hidden input
        $('#event_id2').val(id);       // Set the value of the hidden input
        $('#tutor_id2').val(tutorId);
        
        // Optionally, you can fetch additional details about the event if needed
        getViewRow(id);  // Call a function to get event details (if required)
    });
});

$(function() {
    $(document).on('click', '.eventRejectAll', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');  // Get the event ID from the button's data-id attribute
        $('#eventRejectAll').modal('show');   // Show the modal
        
        // Dynamically set the event_id in the hidden input
        $('#event_id3').val(id);       // Set the value of the hidden input

        // Optionally, you can fetch additional details about the event if needed
        getViewRow(id);  // Call a function to get event details (if required)
    });
});



function getViewRow(id) {
    $.ajax({
        type: 'POST',
        url: 'view_events.php',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response && response.event_name) {
                $('#view_event_name').text(response.event_name);
                $('#view_rendered_hours').text(response.rendered_hours);
                $('#view_description').text(response.description);
                $('#view_created_at').text(response.created_at);
                $('#view_status').text(response.status);
                $('#edit_remarks').val(response.remarks);
                $('#view_remarks').text(response.remarks);

                if (response.attached_file) {
                    $('#view_attached_file').html('<img src="' + response.attached_file + '" class="event-image" alt="Event Image">');
                } else {
                    $('#view_attached_file').text("No image available.");
                }
            } else {
                $('#view_event_name').text("No event found");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error: " + status + " - " + error);
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

</body>
</html>



