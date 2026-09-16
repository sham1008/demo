<?php

require_once __DIR__ . "/config/database.php";

session_start();


// --------------------------------------------------
// ALREADY LOGGED IN
// --------------------------------------------------

/* Already logged in */
if (isset($_SESSION["user_id"], $_SESSION["role"])) {

    if ($_SESSION["role"] === "manager") {
        header("Location: manager/dashboard.php");
        exit;
    }

    if ($_SESSION["role"] === "admin") {
        header("Location: index.php");
        exit;
    }

    if ($_SESSION["role"] === "user") {
        header("Location: user/dashboard.php");
        exit;
    }
}


$error = "";


// --------------------------------------------------
// LOGIN
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // --------------------------------------------------
    // VALIDATION
    // --------------------------------------------------

    if ($email === "" || $password === "") {

        $error = "Email and password are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {


        // --------------------------------------------------
        // FIND USER
        // --------------------------------------------------

        $user = $db
            ->from("users")
            ->where("email", $email)
            ->select("*")
            ->one();


        // --------------------------------------------------
        // VERIFY PASSWORD
        // --------------------------------------------------

        if (
            $user &&
            password_verify(
                $password,
                $user["password"]
            )
        ) {


            // --------------------------------------------------
            // GENERATE 6-DIGIT OTP
            // --------------------------------------------------

            $otp = (string) random_int(100000, 999999);


            // OTP valid for 5 minutes

            $expiresAt = date(
                "Y-m-d H:i:s",
                time() + (5 * 60)
            );


            // --------------------------------------------------
            // DELETE PREVIOUS UNUSED OTPs
            // --------------------------------------------------

            $db
                ->from("login_otps")
                ->where(
                    "user_id",
                    $user["id"]
                )
                ->where(
                    "is_verified",
                    0
                )
                ->delete()
                ->execute();


            // --------------------------------------------------
            // STORE NEW OTP
            // --------------------------------------------------

            $otpResult = $db
                ->from("login_otps")
                ->insert([
                    "user_id" => $user["id"],
                    "otp" => $otp,
                    "expires_at" => $expiresAt,
                    "is_verified" => 0
                ])
                ->execute();


            // --------------------------------------------------
            // CHECK OTP INSERT
            // --------------------------------------------------

            if ($otpResult) {


                // --------------------------------------------------
                // TEMPORARY OTP SESSION
                // --------------------------------------------------

                /*
                 * We are NOT creating the actual login session yet.
                 *
                 * These session values only remember which user
                 * is currently completing OTP verification.
                 */

                $_SESSION["otp_user_id"] = $user["id"];

                $_SESSION["otp_user_name"] = $user["name"];

                $_SESSION["otp_user_email"] = $user["email"];

                $_SESSION["otp_role"] = $user["role"];


                // --------------------------------------------------
                // REDIRECT TO OTP PAGE
                // --------------------------------------------------

                header(
                    "Location: otp_verify.php"
                );

                exit;

            } else {

                $error =
                    "Unable to generate OTP. Please try again.";

            }

        } else {

            $error =
                "Invalid email or password.";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login - Inventory Management
    </title>

</head>


<body>


    <h2>
        Inventory Management System
    </h2>


    <?php if ($error): ?>

        <p>

            <?php
            echo htmlspecialchars($error);
            ?>

        </p>

    <?php endif; ?>


    <form method="POST">


        <label for="email">
            Email
        </label>

        <br>

        <input
            type="email"
            name="email"
            id="email"
            required
            value="<?php
                echo htmlspecialchars(
                    $_POST["email"] ?? ""
                );
            ?>"
        >


        <br><br>


        <label for="password">
            Password
        </label>

        <br>

        <input
            type="password"
            name="password"
            id="password"
            required
        >


        <br><br>


        <button type="submit">
            Login
        </button>


    </form>


</body>

</html>