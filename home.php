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
    <title>TuteeFind</title>
</head>
<body>

<!-- Navbar Section -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="">
            <img src="assets/TuteeFindLogoName.png" alt="" class="navbar-logo">
        </a>
        <!-- Navbar Toggler Button -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#get-started">Get Started</a></li>
            </ul>
            <button class="btn btn-dark" type="button" onclick="showRoleSelection()">Sign In</button>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start">
                <h1 class="head display-4 fw-bold">Develop your skill in a new and unique way</h1>
                <p class="lead">Our platform connects skilled tutors with motivated tutees, offering personalized guidance to help students achieve their learning goals and succeed academically.</p>
                <a href="#features" class="btn btn-dark btn-lg">Explore Features</a>
            </div>
            <div class="col-md-6">
                <img src="assets/babae.png" alt="Babae Image" class="img-fluid" style="max-width: 160%; height: auto;">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="feature-section">
    <div class="container text-center">
        <h2 class="mb-4 head1">Our Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-icon" style="font-size: 50px; color: #1d0a6b;">
                    <i class="bi bi-person-check"></i>
                </div>
                <h4 style="color: #1d0a6b;">Experienced Tutors</h4>
                <p>Learn from the best with highly experienced and skilled tutors who are dedicated to your success.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon" style="font-size: 50px; color: #1d0a6b;">
                    <i class="bi bi-clock"></i>
                </div>
                <h4 style="color: #1d0a6b;">Flexible Scheduling</h4>
                <p>Schedule your sessions at a time that works best for you. We offer flexible timing for every learner.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon" style="font-size: 50px; color: #1d0a6b;">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <h4 style="color: #1d0a6b;">Personalized Learning</h4>
                <p>Each lesson is tailored to meet your specific learning needs and goals, ensuring effective results.</p>
            </div>
        </div>
    </div>
</section>

<!-- Start Section -->
<section class="text-center Start py-5 text-white" id="get-started">
    <div class="container">
        <h2 class="head1">Start Your Learning Journey Today!</h2>
        <p class="lead">Join our community of dedicated learners and expert tutors.</p>
        <a href="#contact" class="btn btn-dark btn-lg" onclick="showRoleSelection()">Get Started</a>
    </div>
</section>



<!-- Footer Section -->
<footer class="footer">
    <p>&copy; 2024 TuteeFind | All Rights Reserved</p>
</footer>

<!-- Role Selection Modal (Initially Hidden) -->
<div id="roleSelection" class="container shadow-lg p-3" style="display: none;">
    <div class="title">What's your role?</div>
    <div class="card-container">
        <a href="tutee/login" class="card" onclick="selectRole(this)">
            <i class='bx bx-user'></i>
            <h3>Tutee</h3>
        </a>
        <a href="tutor/login" class="card" onclick="selectRole(this)">
            <i class='bx bx-chalkboard'></i>
            <h3>Tutor</h3>
        </a>
        <a href="professor/login" class="card" onclick="selectRole(this)">
            <i class='bx bx-brain'></i>
            <h3>Professor</h3>
        </a>
        <a href="admin/login" class="card" onclick="selectRole(this)">
            <i class='bx bx-cog'></i>
            <h3>Admin</h3>
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

function selectRole(card) {
    const allCards = document.querySelectorAll('.card');
    allCards.forEach(c => c.classList.remove('selected'));

    card.classList.add('selected');
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
