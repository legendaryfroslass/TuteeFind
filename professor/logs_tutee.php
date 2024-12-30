<?php   
include 'includes/session.php'; 
include 'includes/header.php'; 
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

// Get the professor's ID from session
$professor_id = $_SESSION['professor_id'];

// SQL query to retrieve tutee data with search functionality
$sql = "
  SELECT tutee.id AS tutee_id, tutee.firstname AS tutee_firstname, tutee.lastname AS tutee_lastname, 
           tutee.barangay, tutee.age, tutee.school
    FROM tutee
    INNER JOIN requests r ON tutee.id = r.tutee_id
    INNER JOIN tutor t ON r.tutor_id = t.id
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ? 
    AND r.status = 'accepted'
    AND (
        CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER(?) OR
        LOWER(tutee.barangay) LIKE LOWER(?) OR
        LOWER(tutee.school) LIKE LOWER(?) OR
        LOWER(tutee.age) LIKE LOWER(?)
    )
    LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("issssii", $professor_id, $search_param, $search_param, $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

// Query to get total count for pagination
$total_sql = "
    SELECT COUNT(*) as total
    FROM tutee
    INNER JOIN requests r ON tutee.id = r.tutee_id
    INNER JOIN tutor t ON r.tutor_id = t.id
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ? 
    AND r.status = 'accepted'
    AND (
        CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER(?) OR
        LOWER(tutee.barangay) LIKE LOWER(?) OR
        LOWER(tutee.school) LIKE LOWER(?) OR
        LOWER(tutee.age) LIKE LOWER(?)
    )";

$stmt_total = $conn->prepare($total_sql);
$stmt_total->bind_param("issss", $professor_id, $search_param, $search_param, $search_param, $search_param);
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
            <h1>Tutees' Logs</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Tutees' Logs</li>
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
<form method="GET" action="logs_tutee.php" class="form-inline d-flex justify-content-between align-items-center">
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
      <div style="max-height: 250px; overflow-y: auto;" class="scrollable-table">
        <table id="example1" class="table table-bordered dataTable no-footer" role="grid" aria-describedby="example1_info">
          <thead>
            <tr role="row">
              <th onclick="sortTable(0)">Last Name <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(1)">First Name <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(2)">Barangay <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th onclick="sortTable(3)">Status <i class="fa fa-sort" aria-hidden="true"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>  
       <?php
// Get the professor ID from the request
$professorId = isset($_GET['professor_id']) ? $_GET['professor_id'] : null;

// Get search term from the request, default to an empty string if none provided
$search = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Pagination parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Set a default limit
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; // Set a default offset

// Query to retrieve tutee data based on professor ID and accepted request status
$sql = "
    SELECT tutee.id AS tutee_id, tutee.firstname AS tutee_firstname, tutee.lastname AS tutee_lastname, 
           tutee.barangay, tutee.age, tutee.school
    FROM tutee
    INNER JOIN requests r ON tutee.id = r.tutee_id
    INNER JOIN tutor t ON r.tutor_id = t.id
    INNER JOIN professor p ON t.professor = p.faculty_id
    WHERE p.id = ? 
    AND r.status = 'accepted'
    AND (
        CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER(?) OR
        LOWER(tutee.barangay) LIKE LOWER(?) OR
        LOWER(tutee.school) LIKE LOWER(?) OR
        LOWER(tutee.age) LIKE LOWER(?)
    )
    LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("issssii", $professor_id, $search_param, $search_param, $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
// Loop through the query result
while ($row = $result->fetch_assoc()) {
    // Sanitize fields from the query
    $firstname = htmlspecialchars($row['tutee_firstname'], ENT_QUOTES);
    $lastname = htmlspecialchars($row['tutee_lastname'], ENT_QUOTES);
    $barangay = htmlspecialchars($row['barangay'], ENT_QUOTES);
    
    $status = "Inactive"; // Default status to Inactive
    $tutee_id = $row['tutee_id']; // Use the tutee_id from your row
    $query = "SELECT * FROM tutee_logs WHERE tutee_id = $tutee_id AND activity LIKE '%login%'";
    $log_result = $conn->query($query);
    
    // Check if any login activity exists
    if ($log_result->num_rows > 0) {
        $status = "Active"; // If login activity exists, set status to Active
    } else {
        $status = "Inactive"; // Default status is Inactive
    }
    
  
    // Check if the search term matches any of the relevant fields
    if (
        strpos(strtolower($firstname . ' ' . $lastname), $search) !== false || 
        strpos(strtolower($barangay), $search) !== false || 
        ($search === 'active' && $status === 'Active') || 
        ($search === 'inactive' && $status === 'Inactive')
    ) {
        // Add matching row to results array, including a concatenated 'name'
        $results[] = [
            'id' => $row['tutee_id'],
            'lastname' => $lastname,
            'firstname' => $firstname,
            'barangay' => $barangay,
            'status' => $status,
            'name' => $firstname . ' ' . $lastname // Construct the name here
        ];
    }
}

// Output the filtered results
foreach ($results as $row) {
    // Determine status and apply appropriate button styles
    $statusClass = $row['status'] === "Active" ? "btn-success" : "btn-danger";
    $statusText = $row['status'];

    echo "<tr>
            <td>" . htmlspecialchars($row['lastname'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['firstname'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['barangay'], ENT_QUOTES) . "</td>
            <td style='text-align: center;'>
                <button class='btn $statusClass btn-sm' style='border-radius: 10px; padding: 1px 10px; width: 100px;'>
                    $statusText
                </button>
            </td>
            <td>
                <button class='btn btn-primary btn-sm btn-flat view' data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "' data-professor-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "'>
                    <i class='fa fa-eye'></i> View
                </button>
                  <a 
              class='btn btn-primary btn-sm btn-flat'  
              href='tuteeLogspdf.php?tutee_id=".htmlspecialchars($row['id'], ENT_QUOTES)."' 
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
          Showing   
 <?php echo ($offset + 1) . ' to ' . min($offset + $limit, $total_rows) . ' of ' . $total_rows . ' entries'; ?>
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
<?php include 'includes/footer.php'; ?>
<?php include 'includes/tutee_modal.php'; ?>

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
                            url: 'tuteefetch_logs.php', // Create this file to fetch logs based on the professor ID
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
        url: 'tuteefetch_logs.php',
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
