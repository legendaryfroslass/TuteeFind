<?php
include 'includes/session.php';

if (isset($_POST['tutor_id']) && isset($_POST['tutee_id'])) {
    $tutorId = $_POST['tutor_id'];
    $tuteeId = $_POST['tutee_id'];

    // Updated query to select week_number, description, date, and uploaded_files
    $query = "SELECT week_number, description, date, uploaded_files FROM tutee_progress WHERE tutor_id = ? AND tutee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $tutorId, $tuteeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Week Number</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = $result->fetch_assoc()) {
            $weekNumber = $row['week_number'];
            $date = $row['date'];
            $description = $row['description'];
            $uploadedFiles = explode(',', $row['uploaded_files']);
            $collapseId = "collapseWeek" . htmlspecialchars($weekNumber);

            echo '<tr class="toggle-row" data-toggle="collapse" data-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" style="cursor: pointer;">
                    <td>Week ' . htmlspecialchars($weekNumber) . '</td>
                    <td>' . htmlspecialchars($date) . '</td>
                    <td><button class="btn btn-info btn-sm toggle-button" data-target="#' . $collapseId . '">View Details</button></td>
                  </tr>';
            
            // Collapsible details row
            echo '<tr id="' . $collapseId . '" class="collapse">
                    <td colspan="3">
                        <div class="p-3">
                            <p><strong>Description:</strong></p>
                            <p class="text-muted">' . nl2br(htmlspecialchars($description)) . '</p>
                            <h6 class="text-primary">Uploaded Files:</h6>
                            <ul class="list-group">';
            foreach ($uploadedFiles as $file) {
                if (!empty($file)) {
                    echo '<li class="list-group-item"><a href="../final/uploads/' . htmlspecialchars($file) . '" target="_blank">' . htmlspecialchars($file) . '</a></li>';
                }
            }
            echo '</ul>
                        </div>
                    </td>
                  </tr>';
        }

        echo '</tbody></table>'; // Close table
    } else {
        echo '<p class="text-danger">No progress available.</p>';
    }

    $stmt->close();
    $conn->close();
} else {
    echo '<p class="text-danger">Error: Tutor ID and tutee ID are required.</p>';
}
?>

<script>
    $(document).ready(function () {
        $('.toggle-button').on('click', function (event) {
            event.stopPropagation(); // Prevent triggering the row click event
            const target = $(this).data('target');

            // Toggle the target collapse
            $(target).collapse('toggle');
        });

        $('.toggle-row').on('click', function () {
            const target = $(this).data('target');

            // Collapse other open rows
            $('.collapse.show').not(target).collapse('hide');
        });
    });
</script>

