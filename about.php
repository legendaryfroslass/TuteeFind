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
<section class="hero-section1 mb-5" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start">
                <h1 class="head display-4 fw-bold">About TuteeFind</h1>
                <p class="lead">TuteeFind is a platform that connects people for free learning opportunities in numeracy and literacy. Our mission is to foster a supportive learning environment that empowers individuals to develop essential skills for success.</p>
                <p>Through TuteeFind, students can collaborate with volunteers and tutors to enhance their literacy and numeracy proficiency. This initiative not only benefits learners but also helps students from participating schools meet the requirements of the Literacy Training Service (LTS), promoting education and community involvement.</p>
                <p>We are committed to bridging educational gaps by providing accessible, high-quality learning experiences. Together, we can create a brighter future for everyone by building a community of learners and mentors dedicated to growth and development.</p>
            </div>
            <div class="col-md-6 text-end">
                <img src="assets/statue.png" alt="About Image" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </div>
</section>


<!-- Mission & Vision Section
<section class="mission-section bg-light py-5">
    <div class="container">
        <div class="row text-center">

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="mb-4 head1">Our Mission</h2>
                        <p class="lead">To provide a platform where skilled tutors and motivated learners can come together and achieve their educational goals through personalized, high-quality tutoring services. We aim to bridge the gap between academic challenges and success by offering flexible, accessible, and engaging learning experiences.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="mb-4 head1">Our Vision</h2>
                        <p class="lead">To create a world where learning is universally accessible, empowering individuals to grow and thrive through education. We envision a community where every student has the opportunity to reach their full potential through collaboration, mentorship, and lifelong learning.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->





<!-- Footer Section -->
<footer class="footer mt-5">
    <div class="footer-content">
        <!-- Logo Section -->
        <div class="footer-logo">
            <img src="assets/TuteeFindLogoName.png" alt="TuteeFind Logo" class="footer-logo-img">
        </div>
        <p>&copy; 2024 TuteeFind | All Rights Reserved</p>
    </div>
</footer>

<!-- Role Selection Modal (Initially Hidden) -->
<div id="roleSelection" class="container shadow-lg p-3" style="display: none;">
    <div class="title">What's your role?</div>
<div class="card-container">
    <!-- Tutee Card with Image -->
    <a href="tutee/login" class="card" onclick="selectRole(this)">
        <img src="assets/owl.png" alt="Tutee" class="card-img" style="width: 150px; display: block; margin-left: auto; margin-right: auto;">
        <h3>Tutee</h3>
    </a>
    <!-- Tutor Card with Image -->
    <a href="tutor/login" class="card" onclick="selectRole(this)">
        <img src="assets/cat.png" alt="Tutor" class="card-img" style="width: 150px; display: block; margin-left: auto; margin-right: auto;">
        <h3>Tutor</h3>
    </a>
</div>

</div>

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
