<?php

// check to make sure the form has been submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// submitted form data
$postName = $_POST['username'];
$postPassword = $_POST['password'];

echo "The username submitted is: " . $postName . "<br>";
echo "The password submitted is: " . $postPassword . "<br>";

// hash the password and assign to variable "$HashedPassword"
$HashedPassword=password_hash($postPassword, PASSWORD_DEFAULT);
echo "The HASHED password is: " . $HashedPassword . "<br>";

// "MD5" password and assign to variable "$md5Password"
$md5Password=md5($postPassword);
echo "The MD5 password is: " . $md5Password . "<br><br>";

} else{
    echo "only post requests are allowed";
}

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

// check to see if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$postName]);
$user = $stmt->fetch();

// check for plain text password
if ($user && ($postPassword === $user['password']))
{
    echo "valid, plaintext" . "<br>";
    // now update the database with hashed password
    updatePassword($HashedPassword, $user['id']);
} else{
    // now check if the password is in MD5 format
    if ($user && ($md5Password == $user['password']))
    {
        echo "valid, MD5" . "<br>";
     // now update the database with hashed password
     updatePassword($HashedPassword, $user['id']);
    } else {
        // finally, check if password is hashed
        if ($user && password_verify($postPassword, $user['password']))
        {
            echo "valid, hashed" . "<br>";
        } else {
            echo "invalid password";
        }
    }
}



function updatePassword($newPassword, $userId){
    global $conn;
    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt= $conn->prepare($sql);
    $stmt->execute([$newPassword, $userId]);
    echo "password has been updated";
}


