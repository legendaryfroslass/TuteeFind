<?php   
include 'includes/session.php'; 
include 'includes/header.php'; 

date_default_timezone_set('Asia/Manila');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Page</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="path_to_your_css_file.css"> <!-- Include your CSS file for styles -->
</head>
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
            <?php
            // Display messages
            if(isset($_SESSION['error'])){
                echo "<div class='alert alert-danger alert-dismissible'>".$_SESSION['error']."</div>";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "<div class='alert alert-success alert-dismissible'>".$_SESSION['success']."</div>";
                unset($_SESSION['success']);
            }
            ?>

            <div class="row"> 
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border"></div>
                        <div class="box-body">
                            <table id="example1" class="table table-bordered">
                                <thead>
                                    <th>Name</th>
                                    <th>Faculty ID</th>
                                    <th>Status</th>
                                    <th>Tools</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT id, lastname, firstname, middlename, faculty_id, last_login FROM professor";
                                    $query = $conn->query($sql);
                                    while($row = $query->fetch_assoc()){
                                        $name = $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'];
                                        $faculty_id = $row['faculty_id'];
                                        $status = "Inactive"; // Set default status to Inactive

                                        // Check for activity logs for the current professor
                                        $activitySql = "SELECT * FROM activity_logs WHERE professor_id = ?";
                                        $activityStmt = $conn->prepare($activitySql);
                                        $activityStmt->bind_param("i", $row['id']);
                                        $activityStmt->execute();
                                        $activityResult = $activityStmt->get_result();

                                        // If the last login exists and has activity logs, update status accordingly
                                        if (!empty($row['last_login']) && $activityResult->num_rows > 0) {
                                            $lastLoginDate = strtotime($row['last_login']);
                                            // If the last login was within the last two weeks
                                            if ($lastLoginDate >= strtotime('-2 weeks')) {
                                                $status = "Active"; 
                                            }
                                        }

                                        // Apply styles for status
                                        $statusStyle = $status == "Active" ? 
                                            "background-color: green; color: white; border-radius: 15px; padding: 3px 12px; display: inline-block; text-align: center; vertical-align: middle;" : 
                                            "background-color: red; color: white; border-radius: 15px; padding: 3px 12px; display: inline-block; text-align: center; vertical-align: middle;";

                                        // Output the row with styled status
                                        echo "<tr>
                                                <td>$name</td>
                                                <td>$faculty_id</td>
                                                <td style='text-align: center; vertical-align: middle; $statusStyle'>$status</td>
                                                <td>
                                                    <button class='btn btn-primary btn-sm btn-flat view' data-id='".$row['id']."' data-professor-name='".htmlspecialchars($name, ENT_QUOTES)."'><i class='fa fa-eye'></i> View</button>
                                                </td>
                                            </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'includes/scripts.php'; ?>

          <!-- Activity Logs Modal --> 
<div class="modal fade" id="viewLogsModal" tabindex="-1" role="dialog" aria-labelledby="viewLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="viewLogsModalLabel"><b id="professorName">Professor Name</b></h4>
            </div>
            <div class="modal-body">
                <!-- Message for empty logs -->
                <div id="emptyLogsMessage" style="display: none; text-align: center; margin: 10px 0;">
                    No activity logs available.
                </div>
                
                <!-- Activity Logs Table -->
                <table class="table" id="logsTable">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated here via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-right" data-dismiss="modal">
                    <i class='fa fa-close'></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


            <script>
                $(document).ready(function(){
                    $('.view').click(function(){
                        var id = $(this).data('id'); // Get the professor ID
                        var professorName = $(this).data('professor-name'); // Get the professor name

                        // Set the professor's name in the modal title
                        $('#professorName').text(professorName);

                        // Fetch activity logs using AJAX
                        $.ajax({
                            url: 'fetch_logs.php', // Create this file to fetch logs based on the professor ID
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
        url: 'fetch_logs.php',
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


        </section>
    </div>
</div>

</body>
</html>
