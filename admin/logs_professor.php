
<?php   
include 'includes/session.php'; 
include 'includes/header.php'; 

date_default_timezone_set('Asia/Manila');
?>

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
// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Define the time threshold for "Active" status (e.g., within the last 30 minutes)
$active_threshold = strtotime("-30 minutes");

// Query to retrieve professor logs with pagination and search
$sql = "
    SELECT 
        id, 
        lastname, 
        firstname, 
        middlename, 
        faculty_id, 
        last_login 
    FROM professor
    WHERE 
        CONCAT(LOWER(firstname), ' ', LOWER(middlename), ' ', LOWER(lastname)) LIKE LOWER('%$search%')
        OR LOWER(faculty_id) LIKE LOWER('%$search%')
    LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Query to get the total count for pagination
$total_sql = "
    SELECT COUNT(*) as total 
    FROM professor
    WHERE 
        CONCAT(LOWER(firstname), ' ', LOWER(middlename), ' ', LOWER(lastname)) LIKE LOWER('%$search%')
        OR LOWER(faculty_id) LIKE LOWER('%$search%')";

$stmt_total = $conn->prepare($total_sql);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>
  
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Professors' Logs</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Professors' Logs</li>
            </ol>
        </section>

        <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
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
  <div class="box-body">
             <!-- Search Form --> 
<form method="GET" action="logs_professor.php" class="form-inline d-flex justify-content-between align-items-center">
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
      <div style="max-height: 390px; overflow-y: auto;" class="scrollable-table">
        <table id="example1" class="table table-bordered dataTable no-footer" role="grid" aria-describedby="example1_info">
          <thead>
            <tr role="row">
                <th onclick="sortTable(0)">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(1)">Faculty ID <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(2)">Status <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>  
            <?php
// Get search term from the request, default to an empty string if none provided
$search = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Query to retrieve all professors
// $sql = "
//     SELECT 
//         id, 
//         lastname, 
//         firstname, 
//         middlename, 
//         faculty_id, 
//         last_login 
//     FROM professor";
$query = $conn->query($sql);

// Temporary array to store filtered results
$results = [];

while ($row = $query->fetch_assoc()) {
    $name = $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'];
    $faculty_id = $row['faculty_id'];
    $status = "Inactive"; // Set default status to Inactive

    // Check for activity logs for the current professor
    $activitySql = "SELECT * FROM professor_logs WHERE professor_id = ?";
    $activityStmt = $conn->prepare($activitySql);
    $activityStmt->bind_param("i", $row['id']);
    $activityStmt->execute();
    $activityResult = $activityStmt->get_result();

    // Update status if last login is recent
    if (!empty($row['last_login']) && $activityResult->num_rows > 0) {
        $lastLoginDate = strtotime($row['last_login']);
        if ($lastLoginDate >= strtotime('-2 weeks')) {
            $status = "Active"; 
        }
    }

    // Check if the search term matches the name, faculty_id, or status
    if (
        strpos(strtolower($name), $search) !== false || 
        strpos(strtolower($faculty_id), $search) !== false || 
        ($search === 'active' && $status === 'Active') || 
        ($search === 'inactive' && $status === 'Inactive')
    ) {
        // Add matching row to results array, including id
        $results[] = [
            'id' => $row['id'],
            'name' => $name,
            'faculty_id' => $faculty_id,
            'status' => $status
        ];
    }
}

// Output the filtered results
foreach ($results as $row) {
    // Determine status and apply appropriate button styles
    $statusClass = $row['status'] === "Active" ? "btn-success" : "btn-danger";
    $statusText = $row['status'];

    echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['faculty_id']}</td>
            <td style='text-align: center;'>
                <button class='btn $statusClass btn-sm' style='border-radius: 10px; padding: 1px 10px; width: 100px;'>
                    $statusText
                </button>
            </td>
            <td>
                <button class='btn btn-primary btn-sm btn-flat view' data-id='".$row['id']."' data-professor-name='".htmlspecialchars($row['name'], ENT_QUOTES)."'>
                    <i class='fa fa-eye'></i> View
                </button>
            <a 
    class='btn btn-primary btn-sm btn-flat'  
    href='professorLogspdf.php?professor_id=".htmlspecialchars($row['id'], ENT_QUOTES)."' 
    target='_blank'>
    <i class='fa fa-file-pdf-o'></i> Export PDF
</a>
            </td>
          </tr>";
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
            <li class="paginate_button   
 previous <?php if ($page <= 1) echo 'disabled'; ?>" id="example1_previous">
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
</section>
</div>
<?php include 'includes/professor_modal.php'; ?>

</div>
<?php include 'includes/scripts.php'; ?>
</body>
            <script>
                $(document).ready(function(){
                    $('.view').click(function(){
                        var id = $(this).data('id'); // Get the professor ID
                        var professorName = $(this).data('professor-name'); // Get the professor name

                        // Set the professor's name in the modal title
                        $('#professorName').text(professorName);

                        // Fetch activity logs using AJAX
                        $.ajax({
                            url: 'professorfetch_logs.php', // Create this file to fetch logs based on the professor ID
                            type: 'POST',
                            data: { id: id },
                            success: function(data){
                                $('#logsTable tbody').html(data);
                                $('#viewLogsModal').modal('show');
                            },
                            error: function(){
                                alert('Error retrieving logs.');
                            }
                        });
                    });
                });

                // Your existing code for viewing logs
$('.view').click(function() {
    var id = $(this).data('id');
    var professorName = $(this).data('professor-name');
    $('#professorName').text(professorName);

    $.ajax({
        url: 'professorfetch_logs.php',
        type: 'POST',
        data: { id: id },
        success: function(data) {
            $('#logsTable tbody').html(data);
            
            // Check if the logs are empty
            if ($('#logsTable tbody tr').length === 0) {
                $('#emptyLogsMessage').show(); // Show the empty message
            } else {
                $('#emptyLogsMessage').hide(); // Hide the empty message
            }

            $('#viewLogsModal').modal('show');
        },
        error: function() {
            alert('Error retrieving logs.');
        }
    });
});
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
</html>
