<?php

require_once __DIR__ . "/config/database.php";

session_start();


// --------------------------------------------------
// CHECK OTP SESSION
// --------------------------------------------------

if (!isset($_SESSION["otp_user_id"])) {

    header("Location: login.php");
    exit;

}


$error = "";


// --------------------------------------------------
// USER INFORMATION
// --------------------------------------------------

$userId = $_SESSION["otp_user_id"];


// --------------------------------------------------
// OTP VERIFICATION
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $enteredOtp = trim($_POST["otp"] ?? "");


    // --------------------------------------------------
    // VALIDATE OTP FORMAT
    // --------------------------------------------------

    if ($enteredOtp === "") {

        $error = "Please enter the OTP.";

    } elseif (!preg_match("/^[0-9]{6}$/", $enteredOtp)) {

        $error = "OTP must contain exactly 6 digits.";

    } else {


        // --------------------------------------------------
        // FETCH LATEST OTP
        // --------------------------------------------------

        $otpRecord = $db
            ->from("login_otps")
            ->where("user_id", $userId)
            ->where("is_verified", 0)
            ->select("*")
            ->many();


        // --------------------------------------------------
        // FIND VALID OTP
        // --------------------------------------------------

        $validOtp = null;

        foreach ($otpRecord as $record) {

            /*
             * Check whether this OTP has expired.
             */

            if (
                strtotime($record["expires_at"]) >= time()
            ) {

                $validOtp = $record;
                break;

            }

        }


        // --------------------------------------------------
        // NO VALID OTP
        // --------------------------------------------------

        if (!$validOtp) {

            $error =
                "OTP has expired. Please login again.";

        }


        // --------------------------------------------------
        // CHECK OTP VALUE
        // --------------------------------------------------

        elseif (
            $enteredOtp !== $validOtp["otp"]
        ) {

            $error =
                "Invalid OTP. Please try again.";

        }


        // --------------------------------------------------
        // OTP IS CORRECT
        // --------------------------------------------------

        else {


            // --------------------------------------------------
            // MARK OTP AS VERIFIED
            // --------------------------------------------------

            $verifyResult = $db
                ->from("login_otps")
                ->where(
                    "id",
                    $validOtp["id"]
                )
                ->update([
                    "is_verified" => 1
                ])
                ->execute();


            if ($verifyResult) {


                // --------------------------------------------------
                // CREATE REAL LOGIN SESSION
                // --------------------------------------------------

                session_regenerate_id(true);


                $_SESSION["user_id"] =
                    $_SESSION["otp_user_id"];


                $_SESSION["user_name"] =
                    $_SESSION["otp_user_name"];


                $_SESSION["user_email"] =
                    $_SESSION["otp_user_email"];


                $_SESSION["role"] =
                    $_SESSION["otp_role"];


                // --------------------------------------------------
                // REMOVE TEMPORARY OTP SESSION DATA
                // --------------------------------------------------

                unset(
                    $_SESSION["otp_user_id"],
                    $_SESSION["otp_user_name"],
                    $_SESSION["otp_user_email"],
                    $_SESSION["otp_role"]
                );


                // --------------------------------------------------
                // ROLE-BASED REDIRECT
                // --------------------------------------------------

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

            } else {

                $error =
                    "Unable to verify OTP. Please try again.";

            }

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
        OTP Verification - Inventory Management
    </title>

</head>


<body>


    <h2>
        Inventory Management System
    </h2>


    <h3>
        OTP Verification
    </h3>


    <p>
        Enter the 6-digit OTP generated for your account.
    </p>


    <?php if ($error): ?>

        <p>

            <?php
            echo htmlspecialchars($error);
            ?>

        </p>

    <?php endif; ?>


    <form method="POST">


        <label for="otp">
            Enter OTP
        </label>

        <br>

        <input
            type="text"
            name="otp"
            id="otp"
            maxlength="6"
            minlength="6"
            pattern="[0-9]{6}"
            inputmode="numeric"
            autocomplete="one-time-code"
            required
        >


        <br><br>


        <button type="submit">
            Verify OTP
        </button>


    </form>


    <br>


    <a href="login.php">
        Back to Login
    </a>


</body>

</html>