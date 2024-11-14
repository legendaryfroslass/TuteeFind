<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/TuteeFindLogo.png" type="image/png">
    <link rel="stylesheet" href="landing.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>About Us - TuteeFind</title>
</head>

<body>

<!-- Navbar Section -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index">
            <img src="assets/TuteeFindLogoName.png" alt="" class="navbar-logo">
        </a>
        <!-- Navbar Toggler Button -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100 text-lg-start text-center">
                <li class="nav-item"><a class="nav-link" href="index">Tutee</a></li>
                <li class="nav-item"><a class="nav-link" href="index">Tutor</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="index">FAQ</a></li>
                <li class="nav-item ms-lg-auto text-lg-end text-center">
                    <button class="btn btn-dark" type="button" onclick="showRoleSelection()">Sign In</button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- About Section -->
<section class="hero-section" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start">
                <h1 class="head display-4 fw-bold">Welcome to TuteeFind</h1>
                <p class="lead">TuteeFind is a platform designed to connect skilled tutors with motivated tutees. Our goal is to empower students with personalized guidance that helps them achieve their academic aspirations.</p>
                <p>At TuteeFind, we believe that quality education and mentorship can transform lives. Whether you're looking for expert guidance in a specific subject or looking to teach and inspire others, we are here to make that happen. Our platform ensures a seamless experience for both tutors and tutees, helping them grow and thrive in their educational journey.</p>
            </div>
            <div class="col-md-6 text-end">
                <img src="assets/statue.png" alt="About Image" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="mission-section bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4 head1">Our Mission</h2>
        <p class="lead">To provide a platform where skilled tutors and motivated learners can come together and achieve their educational goals through personalized, high-quality tutoring services. We aim to bridge the gap between academic challenges and success by offering flexible, accessible, and engaging learning experiences.</p>
    </div>
</section>


<!-- Footer Section -->
<footer class="footer">
    <div class="footer-content">
        <!-- Logo Section -->
        <div class="footer-logo">
            <img src="assets/TuteeFindLogoName.png" alt="TuteeFind Logo" class="footer-logo-img">
        </div>
        <p>&copy; 2024 TuteeFind | All Rights Reserved</p>
    </div>
</footer>

<!-- Overlay for Modal -->
<div id="overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000;"></div>

<script>
function showRoleSelection() {
    const roleSelection = document.getElementById('roleSelection');
    const overlay = document.getElementById('overlay');
    
    if (roleSelection && overlay) {
        roleSelection.style.display = 'block';
        overlay.style.display = 'block';
        
        roleSelection.scrollIntoView({ behavior: 'smooth', block: 'start' });

        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

// Close the modal when clicking on the overlay
document.getElementById('overlay')?.addEventListener('click', function() {
    document.getElementById('roleSelection').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    
    document.body.style.overflow = 'hidden'; // Restore background scrolling
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
