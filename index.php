<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FCC Login</title>

    <link rel="icon" type="image/png" href="assets/images/Logo white.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="home-container">

    <!-- LEFT : IMAGE SECTION -->
    <div class="image-section">
        <div class="image-overlay">

    <!-- TOP TEXT -->
    <div class="overlay-top">
        <h1>FUTSAL CRICKET CLUB</h1>
        <p class="tagline">Automated Scoring & Ranking System</p>
    </div>

    <!-- BOTTOM TEXT -->
    <div class="overlay-bottom">
        <h3>One Team. One Passion.</h3>
        <p>
            FCC is a united cricket family built on teamwork, discipline, and performance.
            This system modernizes match scoring, player statistics, and rankings to
            take our game to the next level.
        </p>
    </div>

</div>
    </div>

    <!-- RIGHT : LOGIN SECTION -->
    <div class="login-section">
        <div class="login-box">

            <img src="assets/images/Logo white.png" alt="FCC Logo" class="logo">

            <h2>Welcome Back</h2>
            <p class="subtitle">Login to continue</p>

            <form method="post" action="auth/login.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit">Login</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
