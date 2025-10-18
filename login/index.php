<?php

require_once "../classes/database.php";

$user = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $user["email"] = trim(htmlspecialchars($_POST["email"]));
    $user["password"] = trim(htmlspecialchars($_POST["password"]));

    if (empty($user["email"]))
    {
        $errors["email"] = "Email address is required";
    }
    else if (!filter_var($user["email"], FILTER_VALIDATE_EMAIL))
    {
        $errors["email"] = "Email address must be in a valid format.";
    }

    if (empty($user["password"]))
    {
        $errors["password"] = "Password is required.";
    }

    if (empty(array_filter($errors)))
    {
        echo "Success!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/universal-style.css">
    <link rel="stylesheet" href="../assets/login-style.css">
    <title>Log In | Supply Desk</title>
</head>
<body>
    <main class="login-page">
        <header class="page-header">
            <h1>Supply Desk</h1>
        </header>
        <section class="login-section">
            <div class="login-container">
                <header class="login-header">
                    <h2>Log In</h2>
                </header>
                <form action="" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <p class="error"><?= $errors["email"] ?? "" ?></p>
                        <input type="email" id="email" name="email" 
                        value="<?= $user["email"] ?? '' ?>" placeholder="email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <p class="error"><?= $errors["password"] ?? ""?></p>
                        <input type="password" id="password" name="password" 
                        value="<?= $user["password"] ?? '' ?>" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="login-button">Log In</button>
                </form>
                <footer class="login-footer">
                    <a href="" class="forgot-password-link">Forgot Password?</a>
                </footer>
            </div>
        </section>
    </main>
</body>
</html>