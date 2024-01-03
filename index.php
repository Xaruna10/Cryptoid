<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: src/dash.php");
    exit();
}
include 'src/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['signup'])) {

        $username = $_POST['username'];
        $email = $_POST['email']; 
        $password = $_POST['password'];

        // Input Validation
        if(!preg_match('/^[\w_\-]+$/', $username)) {
            $error = "Invalid username"; 
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email"; 
        }

        if(strlen($password) < 12 || !preg_match("/[a-z]/i", $password) || !preg_match("/[0-9]/", $password)) {
            $error = "Weak password";
        }

        if(!isset($error)) {
            // Password encryption
            $hashed_pwd = password_hash($password, PASSWORD_BCRYPT);  

            // Insert user into database
            $sql = "INSERT INTO users(username, email, password) VALUES('$username', '$email', '$hashed_pwd')";

            if(mysqli_query($conn, $sql)){
                $_SESSION['user_id'] = mysqli_insert_id($conn); 
                $_SESSION['username'] = $username; 

                header("Location: dash.php");
                exit();
            } 
        } 
    }

    if(isset($_POST['login'])) {

        $username = $_POST['username']; 
        $password = $_POST['password'];

        // Get user data based on username 
        $sql = "SELECT * FROM users WHERE username='$username'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1) {   
            $row = mysqli_fetch_assoc($result);

            // Verify password  
            if(password_verify($password, $row['password'])) {    

                $_SESSION['user_id'] = $row['id']; 
                $_SESSION['username'] = $username; 

                // Redirect to dashboard
                header("Location: dash.php");
                exit();
            }   
        }  
    }  
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Decryptoid </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #333;
            font-size: 2em;
        }

        .container {
            width: 300px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px; /* Increased bottom margin */
        }

        h2 {
            color: #333;
            text-align: center;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .footer {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Decryptoid</h1>
</div>


<!-- <div class="label-top">
    Decryptoid
</div> -->


<div class="container">

    <form method="POST">
        <h2>Sign Up</h2>
        <!-- Display error message for signup if any -->
        <?php if(isset($error) && isset($_POST['signup'])): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <input name="username" type="text" placeholder="Username">
        <input name="email" type="email" placeholder="Email">
        <input name="password" type="password" placeholder="Password">

        <button type="submit" name="signup">Sign Up</button>  
    </form>
</div>

<div class="container">
    <form method="POST">
        <h2>Login</h2>
        <!-- Display error message for login if any -->
        <?php if(isset($error) && isset($_POST['login'])): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <input name="username" type="text" placeholder="Username">
        <input name="password" type="password" placeholder="Password">

        <button type="submit" name="login">Log In</button>
    </form>
</div>

<div class="container">
    <form action="dash.php" method="get">
        <button type="submit">Continue as Guest</button>
    </form>
</div>



</body>
</html>
