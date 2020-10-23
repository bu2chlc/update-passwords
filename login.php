<?php

// check to make sure the form has been submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// submitted form data
$postName = $_POST['username'];
$postPassword = $_POST['password'];

// hash the password and assign to variable "$HashedPassword"
$HashedPassword=password_hash($postPassword, PASSWORD_DEFAULT);

// "MD5" password and assign to variable "$md5Password"
$md5Password=md5($postPassword);

// set up database connection
$servername = "localhost";
$username = "root";
$password = "";

try {
  $conn = new PDO("mysql:host=$servername;dbname=passwords", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

// define the function to update passwords in the DB
function updatePassword($newPassword, $userId){
    global $conn;
    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt= $conn->prepare($sql);
    $stmt->execute([$newPassword, $userId]);
    echo "password has been updated";
}

// check to see if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$postName]);
$user = $stmt->fetch();

// check for plain text password

if ($user){

    switch ($user['password']) {
    
        case password_verify($postPassword, $user['password']):
            echo "valid, hashed" . "<br>";
        break;
        
        case $postPassword:
            echo "valid, plaintext" . "<br>";
            // now update the database with hashed password
            updatePassword($HashedPassword, $user['id']);
        break;
        
        case $md5Password:
            echo "valid, MD5" . "<br>";
            // now update the database with hashed password
            updatePassword($HashedPassword, $user['id']);
        break;

        default:
            echo "invalid password";
    }
}  else {
    echo "user not found";
}
} else{
    echo "only post requests are allowed";
}

