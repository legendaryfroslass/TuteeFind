<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
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
                        WHERE requests.status = 'accepted'";
                $stmt = $conn->prepare($sql);
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
               $sql = "SELECT * FROM professor";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?>
          
              <p>No. of Professor</p>
            </div>
            <div class="icon">
              <i class="fa fa-user"></i>
            </div>
            <a href="professor" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">



              <?php
                $sql = "SELECT * FROM tutor";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
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
                $sql = "SELECT * FROM tutee";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
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
        <a href="home_pdf.php" class="btn btn-primary btn-sm btn-flat" target="_blank">
    <i class="fa fa-file-pdf-o"></i> Export PDF</a>

    </div>
</div>


<?php
// Select distinct barangays for tutors and order them by total tutors in descending order
$sqlTutorBarangays = "SELECT barangay, COUNT(*) AS total_tutors 
                      FROM tutor 
                      GROUP BY barangay 
                      ORDER BY total_tutors DESC";
$queryTutorBarangays = $conn->query($sqlTutorBarangays);

// Select distinct barangays for tutees and order them by total tutees in descending order
$sqlTuteeBarangays = "SELECT barangay, COUNT(*) AS total_tutees 
                      FROM tutee 
                      GROUP BY barangay 
                      ORDER BY total_tutees DESC";
$queryTuteeBarangays = $conn->query($sqlTuteeBarangays);
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


