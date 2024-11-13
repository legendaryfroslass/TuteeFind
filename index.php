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
        <a class="navbar-brand" href="index">
            <img src="assets/TuteeFindLogoName.png" alt="" class="navbar-logo">
        </a>
        <!-- Navbar Toggler Button -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100 text-lg-start text-center">
                <li class="nav-item"><a class="nav-link" href="#tutee">Tutee</a></li>
                <li class="nav-item"><a class="nav-link" href="#tutor">Tutor</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item ms-lg-auto text-lg-end text-center">
                    <button class="btn btn-dark" type="button" onclick="showRoleSelection()">Sign In</button>
                </li>
            </ul>
        </div>
    </div>
</nav>



<!-- Hero Section -->
<section class="hero-section" id="tutee">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start">
                <h1 class="head display-4 fw-bold">Develop your skill in a new and unique way</h1>
                <p class="lead">Our platform connects skilled tutors with motivated tutees, offering personalized guidance to help students achieve their learning goals and succeed academically.</p>
                <a href="tutee/login" class="btn btn-dark btn-lg mb-5">Login as Tutee</a>
            </div>
            <div class="col-md-6 text-end">
                <img src="assets/owl.png" alt="Babae Image" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </div>
</section>


<!-- Hero Section -->
<section class="hero-section mb-5" id="tutor">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="assets/cat.png" alt="Babae Image" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
            <div class="col-md-6 text-end">
                <h1 class="head display-4 fw-bold">Empower students with knowledge and guidance</h1>
                <p class="lead">Join a platform where your expertise helps motivated students reach their academic goals. Provide personalized tutoring that empowers learners to thrive.</p>
                <a href="tutor/login" class="btn btn-dark btn-lg mb-3">Login as Tutor</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="feature-section mt-5">
    <div class="container text-center">
        <h2 class="mb-4 head1">Our Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-icon" style="font-size: 50px; color: #1d0a6b;">
                    <i class="bi bi-person-check"></i>
                </div>
                <h4 style="color: #1d0a6b;">Skilled Tutors</h4>
                <p>Learn from the best with highly skilled tutors who are dedicated to your success.</p>
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

<!-- FAQ Section -->
<section class="text-center py-5" id="faq">
    <div class="container">
        <h2 class="head1 mb-4">Frequently Asked Questions</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card faq-card" style="cursor: pointer;">
                    <div class="card-body">
                        <h4 class="card-title">How do I get started?</h4>
                        <p class="card-text">Simply choose your role and sign up as a tutor or tutee. You’ll then be able to connect with others and start learning or teaching.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card faq-card" style="cursor: pointer;">
                    <div class="card-body">
                        <h4 class="card-title">What makes TuteeFind different?</h4>
                        <p class="card-text">Our platform provides personalized tutoring based on your individual needs, ensuring a custom-tailored experience for every learner.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card faq-card" style="cursor: pointer;">
                    <div class="card-body">
                        <h4 class="card-title">Can I change my role later?</h4>
                        <p class="card-text">Yes! You can update your profile and role at any time to suit your needs and interests.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card faq-card" style="cursor: pointer;">
                    <div class="card-body">
                        <h4 class="card-title">How do I contact support?</h4>
                        <p class="card-text">If you have any questions or issues, you can reach out to our support team by emailing support@tuteefind.com.</p>
                    </div>
                </div>
            </div>
        </div>
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
