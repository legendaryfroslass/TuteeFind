<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php'; ?>
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
// Fetch pagination, search, and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Current professor's ID
$current_faculty_id = $_SESSION['professor_id'];

// Fetch pagination, search, and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Current professor's ID
$current_faculty_id = $_SESSION['professor_id'];

// Prepare SQL query with placeholders
$sql = "
    SELECT 
    t.id, 
    t.lastname, 
    t.firstname, 
    t.student_id, 
    t.course, 
    t.year_section, 
    COALESCE(MAX(ts.completed_weeks), 0) AS completed_weeks,
    COALESCE(MAX(rt.rating), 'No Rating') AS rating,
    COALESCE(MAX(rt.comment), 'No Comment') AS comment,
    MAX(r.tutor_id) AS tutor_id, 
    MAX(r.tutee_id) AS tutee_id,
    COALESCE(SUM(tp.rendered_hours), 0) AS total_rendered_hours,
    COALESCE(SUM(e.rendered_hours), 0) AS event_rendered_hours,
    MAX(rt.pdf_content) AS pdf_content
FROM tutor t
INNER JOIN professor p ON t.professor = p.faculty_id
LEFT JOIN requests r ON t.id = r.tutor_id AND r.status = 'accepted'
LEFT JOIN tutor_ratings rt ON t.id = rt.tutor_id
LEFT JOIN tutee_summary ts ON r.tutee_id = ts.tutee_id
LEFT JOIN tutee_progress tp ON t.id = tp.tutor_id
LEFT JOIN events e ON t.id = e.tutor_id
WHERE p.id = ? 
AND (
    LOWER(t.lastname) LIKE LOWER(?) OR
    LOWER(t.firstname) LIKE LOWER(?) OR
    LOWER(t.course) LIKE LOWER(?) OR
    LOWER(t.year_section) LIKE LOWER(?)
)
GROUP BY t.id
LIMIT $limit OFFSET $offset;
";

// Set up wildcard search term and bind parameters
$search_term = '%' . $search . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $current_faculty_id, $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total count for pagination
$total_sql = "
    SELECT COUNT(*) AS total
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    LEFT JOIN requests r ON t.id = r.tutor_id AND r.status = 'accepted'
    WHERE p.id = ? 
    AND (
        LOWER(t.lastname) LIKE LOWER(?) OR
        LOWER(t.firstname) LIKE LOWER(?) OR
        LOWER(t.course) LIKE LOWER(?) OR
        LOWER(t.year_section) LIKE LOWER(?)
    )";

$stmt_total = $conn->prepare($total_sql);
$stmt_total->bind_param("issss", $current_faculty_id, $search_term, $search_term, $search_term, $search_term);
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
        Student's List
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Student</li>
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
              
            </div>
            <div class="box-body">
 <!-- Search Form --> 
 <form method="GET" action="progress.php" class="form-inline d-flex justify-content-between align-items-center">
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
                <th onclick="sortTable(0)">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(1)">Student ID <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(2)">Course: Year & Section <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(3)">Rendered Hours <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th>Ratings and Feedback</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
<?php
$professor_id = $_SESSION['professor_id'];
$search = isset($_GET['search']) ? '%' . strtolower($_GET['search']) . '%' : '%%'; // Get the search term and prepare for LIKE query

// SQL query to fetch required data with search functionality
$sql = "
    SELECT 
        t.id, 
        t.lastname, 
        t.firstname, 
        t.student_id, 
        t.course, 
        t.year_section, 
        COALESCE(ts.completed_weeks, 0) AS completed_weeks,
        COALESCE(rt.rating, 'No Rating') AS rating,
        COALESCE(rt.comment, 'No Comment') AS comment,
        r.tutor_id, 
        r.tutee_id,
        COALESCE(SUM(tp.rendered_hours), 0) AS total_rendered_hours,  -- Summing rendered hours from tutee_progress
        COALESCE(SUM(e.rendered_hours), 0) AS event_rendered_hours,  -- Summing rendered hours from events
        rt.pdf_content  -- Fetching PDF content from tutor_ratings
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    LEFT JOIN requests r ON t.id = r.tutor_id AND r.status = 'accepted'
    LEFT JOIN tutor_ratings rt ON t.id = rt.tutor_id
    LEFT JOIN tutee_summary ts ON r.tutee_id = ts.tutee_id
    LEFT JOIN tutee_progress tp ON t.id = tp.tutor_id  -- Joining tutee_progress table
    LEFT JOIN events e ON t.id = e.tutor_id  -- Joining events table
    WHERE p.id = ? 
    AND (
        LOWER(t.lastname) LIKE CONCAT('%', LOWER(?), '%') OR
        LOWER(t.firstname) LIKE CONCAT('%', LOWER(?), '%') OR
        LOWER(t.course) LIKE CONCAT('%', LOWER(?), '%') OR
        LOWER(t.year_section) LIKE CONCAT('%', LOWER(?), '%')
    )
    GROUP BY t.id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $professor_id, $search, $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

// Display results in HTML table
while ($row = $result->fetch_assoc()) {
    $name = htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
    $student_id = isset($row['student_id']) ? htmlspecialchars($row['student_id']) : 'N/A';
    $course_year_section = htmlspecialchars($row['course'] . ': ' . $row['year_section']);
    $total_rendered_hours = htmlspecialchars($row['total_rendered_hours']);
    $pdf_content = !empty($row['pdf_content']) ? nl2br(htmlspecialchars($row['pdf_content'])) : 'No Feedback Available';

    echo "
      <tr>
         <td>{$name}</td>
         <td>{$student_id}</td>
         <td>{$course_year_section}</td>
         <td>{$total_rendered_hours}</td>
         <td>{$pdf_content}</td>
         <td>
             <button class='btn btn-primary btn-sm view btn-flat' 
                     data-id='".htmlspecialchars($row['id'])."'
                     data-tutor-id='".htmlspecialchars($row['tutor_id'])."'
                     data-tutee-id='".htmlspecialchars($row['tutee_id'])."'>
                 <i class='fa fa-eye'></i> View
             </button>
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
    
  <?php include 'includes/progress_modal.php'; ?>
  <?php include 'includes/footer.php'; ?>

</div>
<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  $(document).on('click', '.view', function(e){
    e.preventDefault();
    $('#view').modal('show'); // Display the view modal
    var id = $(this).data('id');
    var tutorId = $(this).data('tutor-id');
    var tuteeId = $(this).data('tutee-id');
    getViewRow(id, tutorId, tuteeId);
  });
});

function getViewRow(id, tutorId, tuteeId){
  $.ajax({
    type: 'POST',
    url: 'progress_view.php', // PHP file to handle the AJAX request and retrieve progress information
    data: {id:id, tutor_id: tutorId, tutee_id: tuteeId},
    dataType: 'html', // Expect HTML response
    success: function(response){
      // Append the HTML to the modal body
      $('#view_firstname').text(response.firstname);
      $('#view_lastname').text(response.lastname);
      $('#view_middlename').text(response.middlename);
      $('#view_age').text(response.age);
      $('#view_birthday').text(response.birthday);
      $('#view_faculty_id').text(response.faculty_id);
      $('#view_employment_status').text(response.employment_status);
      $('.uploaded-images-container').html(response);
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
    url: 'progress_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.id);
      $('#edit_firstname').val(response.firstname);
      $('#edit_lastname').val(response.lastname);
      $('#edit_middlename').val(response.middlename);
      $('#edit_age').val(response.age);
      $('#edit_birthday').val(response.birthday);
      $('#edit_faculty_id').val(response.faculty_id);
      $('#edit_employment_status').val(response.employment_status);
      
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
</body>
</html>