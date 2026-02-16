<?php
session_start();
include "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Heal2Rise - Mental Health Support</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="./CSS/style.css">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
    a { text-decoration:none; }
    header, section, footer { width:100%; }
    .hero {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('./images/img.jpg');
        background-size: cover;
        background-position: center;
        color:white;
        height: 500px;
        display:flex;
        justify-content:center;
        align-items:center;
        text-align:center;
    }
    .hero h1 { font-size:3em; margin-bottom:20px; }
    .hero p { font-size:1.2em; margin-bottom:30px; }
    .cta-button { padding:15px 30px; background:#28a745; color:white; border-radius:5px; transition:0.3s; }
    .cta-button:hover { background:#218838; }
    .section { padding:60px 20px; }
    .container { max-width:1200px; margin:0 auto; }
    .section-title { text-align:center; margin-bottom:40px; }
    .section-title h2 { font-size:2em; color:#007BFF; }
    .cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap:30px; }
    .card { background:white; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); text-align:center; transition:0.3s;}
    .card:hover { transform:translateY(-5px); }
    .card h3 { margin:15px 0; color:#007BFF; }
    .card p { margin:10px 0; }
    .team-member img { width:120px; height:120px; border-radius:50%; object-fit:cover; margin-bottom:10px; }
    .contact-form { max-width:600px; margin:0 auto; display:flex; flex-direction:column; gap:15px; }
    .contact-form input, .contact-form textarea { padding:10px; border:1px solid #ccc; border-radius:5px; width:100%; }
    .contact-form button { padding:12px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer; }
    .contact-form button:hover { background:#218838; }
    footer { background:#007BFF; color:white; text-align:center; padding:40px 20px; }
</style>
</head>
<body>

<?php include('navbar.php'); ?>

<!-- HERO -->
<div class="hero">
    <div>
        <h1>Heal2Rise</h1>
        <p>Your mental health matters. We provide support, guidance, and care to help you rise above challenges.</p>
        <a href="#services" class="cta-button">Our Services</a>
    </div>
</div>

<!-- MISSION -->
<section class="section" id="mission">
    <div class="container">
        <div class="section-title">
            <h2>Our Mission</h2>
        </div>
        <p style="text-align:center; font-size:1.2em; max-width:900px; margin:0 auto;">
            Heal2Rise is dedicated to providing mental health support to individuals facing depression, family issues, marital challenges, or emotional distress.
            Our mission is to offer personalized care, rehabilitative programs, and counseling to empower our clients and help them achieve emotional wellbeing.
        </p>
    </div>
</section>

<!-- SERVICES -->
<section class="section" id="services">
    <div class="container">
        <div class="section-title">
            <h2>Our Programs</h2>
        </div>
        <div class="cards">
            <div class="card">
                <i class="fas fa-user-md fa-2x"></i>
                <h3>Counseling Sessions</h3>
                <p>One-on-one professional counseling to address personal and emotional challenges.</p>
            </div>
            <div class="card">
                <i class="fas fa-users fa-2x"></i>
                <h3>Group Support</h3>
                <p>Join support groups with peers facing similar issues for shared growth and motivation.</p>
            </div>
            <div class="card">
                <i class="fas fa-heart fa-2x"></i>
                <h3>Rehabilitation Programs</h3>
                <p>Structured rehabilitation and recovery programs guided by trained mental health professionals.</p>
            </div>
        </div>
    </div>
</section>

<!-- TEAM -->
<section class="section" id="team">
    <div class="container">
        <div class="section-title">
            <h2>Meet Our Team</h2>
        </div>
        <div class="cards">
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe">
                <h3>John Doe</h3>
                <p><strong>Role:</strong> Counselor</p>
                <p><strong>Email:</strong> john@example.com</p>
            </div>
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Jane Smith">
                <h3>Jane Smith</h3>
                <p><strong>Role:</strong> Psychiatrist</p>
                <p><strong>Email:</strong> jane@example.com</p>
            </div>
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/men/56.jpg" alt="Michael Lee">
                <h3>Michael Lee</h3>
                <p><strong>Role:</strong> Social Worker</p>
                <p><strong>Email:</strong> michael@example.com</p>
            </div>
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Sarah Brown">
                <h3>Sarah Brown</h3>
                <p><strong>Role:</strong> Counselor</p>
                <p><strong>Email:</strong> sarah@example.com</p>
            </div>
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/men/78.jpg" alt="David Wilson">
                <h3>David Wilson</h3>
                <p><strong>Role:</strong> Monitor</p>
                <p><strong>Email:</strong> david@example.com</p>
            </div>
            <div class="card team-member">
                <img src="https://randomuser.me/api/portraits/women/82.jpg" alt="Emma Davis">
                <h3>Emma Davis</h3>
                <p><strong>Role:</strong> Social Worker</p>
                <p><strong>Email:</strong> emma@example.com</p>
            </div>
        </div>
    </div>
</section>


<!-- GET INVOLVED -->
<section class="section" id="contact">
    <div class="container">
        <div class="section-title">
            <h2>Get Involved / Contact Us</h2>
        </div>
        <p style="text-align:center; margin-bottom:20px;">Reach out to us to become a volunteer, request support, or partner with Heal2Rise.</p>
        <form class="contact-form" method="POST" action="contact_submit.php">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; <?= date("Y") ?> Heal2Rise. All rights reserved.</p>
</footer>

<?php mysqli_close($conn); ?>
</body>
</html>
