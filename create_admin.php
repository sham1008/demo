<?php

require_once __DIR__ . "/config/database.php";

$name = "Admin";
$email = "admin@gmail.com";
$password = "admin123";
$role = "admin";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$existingUser = $db->from("users")
                   ->where("email", $email)
                   ->select("id")
                   ->one();

if ($existingUser) {
    echo "Admin user already exists.";
    exit;
}

$result = $db->from("users")
             ->insert(array(
                 "name" => $name,
                 "email" => $email,
                 "password" => $hashedPassword,
                 "role" => $role
             ))
             ->execute();

if ($result) {
    echo "Admin user created successfully.";
} else {
    echo "Unable to create admin user.";
}