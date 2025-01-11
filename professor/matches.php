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
// Search functionality (similar to admin's version)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Modify SQL query to only retrieve matches for the current professor
$current_faculty_id = $_SESSION['professor_id']; // Get professor ID

$sql = "
    SELECT 
        professor.lastname AS proflast, 
        tutor.firstname AS tutfirst, 
        tutor.lastname AS tutlast, 
        tutee.firstname AS tuteefirst, 
        tutee.lastname AS tuteelast, 
        tutee.barangay AS tuteebarangay,
        CONCAT(tutor.course, ' - ', tutor.year_section) AS course_section
    FROM requests
    LEFT JOIN tutor ON tutor.id = requests.tutor_id
    LEFT JOIN tutee ON tutee.id = requests.tutee_id
    LEFT JOIN professor ON professor.faculty_id = tutor.professor
    WHERE requests.status = 'accepted' AND professor.id = ?
    AND (
        CONCAT(LOWER(tutor.course), ' ', LOWER(tutor.year_section)) LIKE LOWER('%$search%') OR
        CONCAT(LOWER(tutor.firstname), ' ', LOWER(tutor.lastname)) LIKE LOWER('%$search%') OR
        CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER('%$search%') OR
        LOWER(tutee.barangay) LIKE LOWER('%$search%')
    )
    LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_faculty_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare statement for total count (modify to match the above query)
$total_sql = "
    SELECT COUNT(*) as total 
    FROM requests
    LEFT JOIN tutor ON tutor.id = requests.tutor_id
    LEFT JOIN tutee ON tutee.id = requests.tutee_id
    LEFT JOIN professor ON professor.faculty_id = tutor.professor
    WHERE requests.status = 'accepted' AND professor.id = ?
    AND (
        CONCAT(LOWER(tutor.course), ' ', LOWER(tutor.year_section)) LIKE LOWER('%$search%') OR
        CONCAT(LOWER(tutor.firstname), ' ', LOWER(tutor.lastname)) LIKE LOWER('%$search%') OR
        CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER('%$search%') OR
        LOWER(tutee.barangay) LIKE LOWER('%$search%')
    )";

$stmt_total = $conn->prepare($total_sql);
$stmt_total->bind_param("i", $current_faculty_id);
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
      
      <h1>Pairs</h1> <!-- Updated to match tutor.php title -->
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Pairs</li> <!-- Updated breadcrumb to match tutor.php -->
      </ol>
    </section>

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
            <a href="matches_pdf.php?search=<?php echo urlencode($search); ?>" class="btn btn-primary btn-sm btn-flat" target="_blank">
                <i class="fa fa-file-pdf-o"></i> Export to PDF
              </a>
            </div>

            <div class="box-body">
             <!-- Search Form --> 
<form method="GET" action="matches.php" class="form-inline d-flex justify-content-between align-items-center">
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
      <div style="max-height: 360px; overflow-y: auto;" class="scrollable-table">
        <table id="example1" class="table table-bordered dataTable no-footer" role="grid" aria-describedby="example1_info">
          <thead>
            <tr role="row">
                <th onclick="sortTable(0)">Tutor <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(1)">Tutee <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(2)">Barangay <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(3)">Course: Year & Section <i class="fa fa-sort" aria-hidden="true"></i></th>
              </tr>
            </thead>
            <tbody>            
            <?php
// Search functionality (similar to admin's version)
if (isset($_SESSION['professor_id'])) {
    $current_faculty_id = $_SESSION['professor_id'];
    $search = isset($_GET['search']) ? '%' . strtolower($_GET['search']) . '%' : '%%'; // Get the search term and prepare for LIKE query

    $sql = "
        SELECT 
            professor.lastname AS proflast, 
            tutor.firstname AS tutfirst, 
            tutor.lastname AS tutlast, 
            tutee.firstname AS tuteefirst, 
            tutee.lastname AS tuteelast, 
            tutee.barangay AS tuteebarangay,
            CONCAT(tutor.course, ' ', tutor.year_section) AS course_section
        FROM requests
        LEFT JOIN tutor ON tutor.id = requests.tutor_id
        LEFT JOIN tutee ON tutee.id = requests.tutee_id
        LEFT JOIN professor ON professor.faculty_id = tutor.professor
        WHERE requests.status = 'accepted' AND professor.id = ?
        AND (
            CONCAT(LOWER(tutor.course), ' ', LOWER(tutor.year_section)) LIKE ? OR
            CONCAT(LOWER(tutor.firstname), ' ', LOWER(tutor.lastname)) LIKE ? OR
            CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE ? OR
            LOWER(tutee.barangay) LIKE ?
        )
    ";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the search term to all relevant columns
        $stmt->bind_param("sssss", $current_faculty_id, $search, $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
                    <tr>
                        <td class='hidden'></td>
                        <td>" . htmlspecialchars($row['tutfirst']) . ' ' . htmlspecialchars($row['tutlast']) . "</td>
                        <td>" . htmlspecialchars($row['tuteefirst']) . ' ' . htmlspecialchars($row['tuteelast']) . "</td>
                        <td>" . htmlspecialchars($row['tuteebarangay']) . "</td>
                        <td>" . htmlspecialchars($row['course_section']) . "</td>
                    </tr>
                ";
            }
        } else {
            echo "<tr><td colspan='5'>No records found.</td></tr>";
        }
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "<tr><td colspan='5'>No faculty ID found for the current professor.</td></tr>";
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

<?php include 'includes/matches_modal.php'; ?>

</div>
<?php include 'includes/scripts.php'; ?>

</body>
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