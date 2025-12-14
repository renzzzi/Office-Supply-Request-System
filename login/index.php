<?php

session_start();
require_once __DIR__ . "/../classes/database.php";
require_once __DIR__ . "/../classes/users.php";

$pdoConnection = (new Database())->connect();

$userObj = new Users($pdoConnection);

$userInput = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $userInput["email"] = trim(htmlspecialchars($_POST["email"]));
    $userInput["password"] = trim(htmlspecialchars($_POST["password"]));

    if (empty($userInput["email"]))
    {
        $errors["email"] = "Email address is required";
    }
    else if (!filter_var($userInput["email"], FILTER_VALIDATE_EMAIL))
    {
        $errors["email"] = "Email address must be in a valid format.";
    }

    if (empty($userInput["password"]))
    {
        $errors["password"] = "Password is required.";
    }

    if (empty(array_filter($errors)))
    {
        $user = $userObj->getUserByEmail($userInput["email"]);
        
        if ($user && password_verify($userInput["password"], $user["password_hash"]))
        {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_first_name"] = $user["first_name"];
            $_SESSION["user_last_name"] = $user["last_name"];
            $_SESSION["user_role"] = $user["role"];

            // Role check
            if ($_SESSION["user_role"] === "Admin")
            {
                header("Location: ../admin");
            }
            else if ($_SESSION["user_role"] === "Processor")
            {
                header("Location: ../processor");
            }
            else if ($_SESSION["user_role"] === "Requester")
            {
                header("Location: ../requester");
            }
            else
            {
                header("Location: logout.php");
            }
            exit();
        }
        else
        {
            $errors["input"] = "Email or password is incorrect";
        }
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
                <p class="error login-error"><?= $errors["input"] ?? "" ?></p>
                <form action="" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <p class="error"><?= $errors["email"] ?? "" ?></p>
                        <input type="email" id="email" name="email" 
                        value="<?= $userInput["email"] ?? '' ?>" placeholder="email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <p class="error"><?= $errors["password"] ?? ""?></p>
                        <input type="password" id="password" name="password" 
                        placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="login-button">Log In</button>
                </form>
<!--
                <footer class="login-footer">
                    <a href="" class="forgot-password-link">Forgot Password?</a>
                </footer>
-->
            </div>
        </section>
    </main>
</body>
</html>