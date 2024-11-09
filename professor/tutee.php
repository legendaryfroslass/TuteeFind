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
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    INNER JOIN requests r ON t.id = r.tutor_id
    INNER JOIN tutee ON r.tutee_id = tutee.id
    WHERE p.id = ?
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
    FROM tutor t
    INNER JOIN professor p ON t.professor = p.faculty_id
    INNER JOIN requests r ON t.id = r.tutor_id
    INNER JOIN tutee ON r.tutee_id = tutee.id
    WHERE p.id = ?
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

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Tutees' List
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Tutee</li>
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
            </div>
            <div class="box-body">



             <!-- Search Form --> 
  <form method="GET" action="tutee.php" class="form-inline d-flex justify-content-between align-items-center">
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
                <th onclick="sortTable(1)">Barangay <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(2)">Age <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th onclick="sortTable(2)">School <i class="fa fa-sort" aria-hidden="true"></i></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php
// Assuming you're storing the professor's ID in the session
if (isset($_SESSION['professor_id'])) {
    $professor_id = $_SESSION['professor_id'];
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%'; // Prepare search term for LIKE query

    // SQL query to fetch required data
    $sql = "
        SELECT 
            tutee.id AS tutee_id, 
            tutee.firstname AS tutee_firstname, 
            tutee.lastname AS tutee_lastname, 
            tutee.barangay, 
            tutee.age, 
            tutee.school
        FROM tutor t
        INNER JOIN professor p ON t.professor = p.faculty_id
        INNER JOIN requests r ON t.id = r.tutor_id
        INNER JOIN tutee ON r.tutee_id = tutee.id
        WHERE p.id = ?
        AND (
            CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE ? OR
            LOWER(tutee.barangay) LIKE ? OR
            LOWER(tutee.age) LIKE ? OR
            LOWER(tutee.school) LIKE ?
        )
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $professor_id, $search, $search, $search, $search); // Bind search term to relevant columns
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row['tutee_firstname']) . ' ' . htmlspecialchars($row['tutee_lastname']); // Sanitize output
                echo "
                    <tr>
                        <td>$name</td>
                        <td>" . htmlspecialchars($row['barangay']) . "</td>
                        <td>" . htmlspecialchars($row['age']) . "</td>
                        <td>" . htmlspecialchars($row['school']) . "</td>
                        <td>
                            <button class='btn btn-primary btn-sm btn-flat view' data-id='" . htmlspecialchars($row['tutee_id']) . "'><i class='fa fa-eye'></i> View</button>
                        </td>
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
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/tutee_modal.php'; ?>
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
    url: 'tutee_view.php', // PHP file to handle the AJAX request and retrieve tutee information
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Populate the modal with the retrieved data
      $('#view_firstname').text(response.firstname);
      $('#view_lastname').text(response.lastname);
      $('#view_age').text(response.age);
      $('#view_sex').text(response.sex);
      $('#view_number').text(response.number);
      $('#view_guardianname').text(response.guardianname);
      $('#view_fblink').text(response.fblink);
      $('#view_emailaddress').text(response.emailaddress);
      $('#view_barangay').text(response.barangay);
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
    url: 'tutee_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.id);
      $('#edit_firstname').val(response.firstname);
      $('#edit_lastname').val(response.lastname);
      $('#edit_age').val(response.age);
      $('#edit_sex').val(response.sex);
      $('#edit_number').val(response.number);
      $('#edit_guardianname').val(response.guardianname);
      $('#edit_fblink').val(response.fblink);
      $('#edit_emailaddress').val(response.emailaddress);
      $('#edit_barangay').val(response.barangay);
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
