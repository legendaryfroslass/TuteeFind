<?php
session_start();
include 'includes/conn.php'; // Include the database connection file

if(!isset($_SESSION['professor_id'])){
  header('location: login');
  exit();
}
$professor_id = $_SESSION['professor_id'];
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
    <section style="position: relative;">
  <h1 style="font-size: 2em; display: inline;"><strong>Dashboard</strong></h1>
  <h1 style="font-size: 2em; float: right; margin: 0;">
    <strong>As of today:</strong> 
    <span id="currentDate" style="color: red;"></span>
  </h1>
</section>

<script>
  // Get the current date
  const today = new Date();

  // Format the date as MM/DD/YYYY
  const formattedDate = today.toLocaleDateString('en-US', {
    month: '2-digit',
    day: '2-digit',
    year: 'numeric',
  });

  // Display the date in the span
  document.getElementById('currentDate').textContent = formattedDate;
</script>

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
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <?php
                $sql = "SELECT COUNT(*) as count FROM requests 
                        INNER JOIN tutor ON requests.tutor_id = tutor.id
                        INNER JOIN professor ON tutor.professor = professor.faculty_id
                        WHERE requests.status = 'accepted' AND professor.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $professor_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                echo "<h3>".$row['count']."</h3>";
              ?>

              <p>No. of Pairs</p>
            </div>
            <div class="icon">
              <i class="fa fa-code-fork"></i>
            </div>
            <a href="matches" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

       <!-- ./col -->
       <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">

            <?php
$sql_events = "SELECT COUNT(*) as count_events
               FROM events e
               INNER JOIN tutor t ON e.tutor_id = t.id
               INNER JOIN professor p ON t.professor = p.faculty_id
               WHERE p.id = ? AND e.status = 'pending'";

$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $professor_id);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
$row_events = $result_events->fetch_assoc();
$count_events = $row_events['count_events'];

$sql_progress = "SELECT COUNT(*) as count_progress
                 FROM tutee_progress tp
                 INNER JOIN tutor t ON tp.tutor_id = t.id
                 INNER JOIN professor p ON t.professor = p.faculty_id
                 WHERE p.id = ? AND tp.status = 'pending'";

$stmt_progress = $conn->prepare($sql_progress);
$stmt_progress->bind_param("i", $professor_id);
$stmt_progress->execute();
$result_progress = $stmt_progress->get_result();
$row_progress = $result_progress->fetch_assoc();
$count_progress = $row_progress['count_progress'];

echo "<h3>" . ($count_progress + $count_events) . "</h3>";
?>
<p>No. of Requests</p>
</div>
<div class="icon">
    <i class="fa fa-paper-plane"></i>
</div>
<a href="event_request" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
</div>
</div>


        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <?php
                $sql = "SELECT COUNT(*) as count 
                        FROM tutor t
                        INNER JOIN professor p ON t.professor = p.faculty_id
                        WHERE p.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $professor_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo "<h3>".$row['count']."</h3>";
              ?>
              

              <p>No. of Tutor</p>
            </div>
            <div class="icon">
              <i class="fa fa-users"></i>
            </div>
            <a href="tutor" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <?php
                $sql = "SELECT COUNT(DISTINCT tutee.id) as count 
                        FROM tutee
                        INNER JOIN requests ON tutee.id = requests.tutee_id
                        INNER JOIN tutor ON requests.tutor_id = tutor.id
                        INNER JOIN professor ON tutor.professor = professor.faculty_id
                        WHERE professor.id = ? AND requests.status = 'accepted'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $professor_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                echo "<h3>".$row['count']."</h3>";
        
              ?>

              <p>No. of Tutee</p>
            </div>
            <div class="icon">
              <i class="fa fa-child"></i>
            </div>
            <a href="tutee" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>

      <div class="row">
      <div class="col-xs-12" style="display: flex; justify-content: space-between; align-items: center;">
      <h4><strong>Barangay Tally</strong></h4>
          <a href="home_pdf" class="btn btn-primary btn-sm btn-flat" target="_blank">
    <i class="fa fa-file-pdf-o"></i> Export PDF</a>
        </div>
      </div>
      
      
     <?php
        // Select distinct barangays for tutors handled by the professor
        $sqlTutorBarangays = "SELECT DISTINCT t.barangay, COUNT(*) AS total_tutors 
                FROM tutor t
                INNER JOIN professor p ON t.professor = p.faculty_id
                WHERE p.id = ?
                GROUP BY t.barangay 
                ORDER BY total_tutors DESC";
        $stmt = $conn->prepare($sqlTutorBarangays);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $queryTutorBarangays = $stmt->get_result();


        // Select distinct barangays for tutees handled by tutors under the professor
        $sqlTuteeBarangays = "SELECT DISTINCT tutee.barangay, COUNT(*) AS total_tutees 
                      FROM tutee 
                      INNER JOIN requests ON tutee.id = requests.tutee_id
                      INNER JOIN tutor ON requests.tutor_id = tutor.id
                      INNER JOIN professor p ON tutor.professor = p.faculty_id
                      WHERE p.id = ? AND requests.status = 'accepted'
                      GROUP BY tutee.barangay 
                      ORDER BY total_tutees DESC";
        $stmt = $conn->prepare($sqlTuteeBarangays);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $queryTuteeBarangays = $stmt->get_result();

      ?>

<!DOCTYPE html>
<html>
<head>
    <title>Barangay Data Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h4 class="box-title"><strong>Tutor Barangays</strong></h4>
                </div>
                <div class="box-body">
                    <canvas id="tutorChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h4 class="box-title"><strong>Tutee Barangays</strong></h4>
                </div>
                <div class="box-body">
                    <canvas id="tuteeChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tutor Chart
    const tutorData = <?php echo json_encode($queryTutorBarangays->fetch_all(MYSQLI_ASSOC)); ?>;
    const tutorLabels = tutorData.map(item => item.barangay);
    const tutorValues = tutorData.map(item => item.total_tutors);

    const tutorChartContext = document.getElementById('tutorChart').getContext('2d');
    new Chart(tutorChartContext, {
        type: 'bar',
        data: {
            labels: tutorLabels,
            datasets: [{
                label: 'Total Tutors',
                data: tutorValues,
                backgroundColor: 'rgba(243, 156, 17, 0.6)',  // Soft Yellow
                borderColor: 'rgba(243, 156, 17, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Tutee Chart
    const tuteeData = <?php echo json_encode($queryTuteeBarangays->fetch_all(MYSQLI_ASSOC)); ?>;
    const tuteeLabels = tuteeData.map(item => item.barangay);
    const tuteeValues = tuteeData.map(item => item.total_tutees);

    const tuteeChartContext = document.getElementById('tuteeChart').getContext('2d');
    new Chart(tuteeChartContext, {
        type: 'bar',
        data: {
            labels: tuteeLabels,
            datasets: [{
                label: 'Total Tutees',
                data: tuteeValues,
                backgroundColor: 'rgba(221, 76, 57, 0.6)',  // Soft Red
                borderColor: 'rgba(221, 76, 57, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
   
