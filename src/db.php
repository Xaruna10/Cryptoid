<?php

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'cipher_app');

// Connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


// Create users table (if not exists)
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
)";

if(!mysqli_query($conn, $sql)){
  echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
}


// Create inputs table  (if not exists)
$sql = "CREATE TABLE IF NOT EXISTS inputs (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT,
    input_text TEXT,
    cipher VARCHAR(50),
    action VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!mysqli_query($conn, $sql)){
  echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
}

?>