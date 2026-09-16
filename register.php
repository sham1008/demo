<?php

require_once __DIR__ . "/config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* If already logged in */


$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $phone = trim($_POST["phone"] ?? "");
    $company = trim($_POST["company"] ?? "");
    $address = trim($_POST["address"] ?? "");

    /* Validation */

    if ($name === "") {

        $error = "Name is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    } else {

        /* Check whether email already exists in users */
        $existingUser = $db->from("users")
                           ->where("email", $email)
                           ->select("*")
                           ->one();

        if ($existingUser) {

            $error = "An account with this email already exists.";

        } else {

            /* Check customer email as well */
            $existingCustomer = $db->from("customers")
                                  ->where("email", $email)
                                  ->select("*")
                                  ->one();

            if ($existingCustomer) {

                $error = "A customer account with this email already exists.";

            } else {

                /*
                 * Hash password
                 */
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                 * Create user login account
                 */
                $userResult = $db->from("users")
                                 ->insert([
                                     "name" => $name,
                                     "email" => $email,
                                     "password" => $hashedPassword,
                                     "role" => "user"
                                 ])
                                 ->execute();

                if (!$userResult) {

                    $error = "Unable to create your account.";

                } else {

                    /*
                     * Get newly created user ID
                     */
                    $userId = $db->insert_id;

                    if (!$userId) {

                        $error = "Unable to create customer account.";

                    } else {

                        /*
                         * Create customer record
                         */
                        $customerResult = $db->from("customers")
                                             ->insert([
                                                 "user_id" => $userId,
                                                 "name" => $name,
                                                 "company" => $company,
                                                 "phone" => $phone,
                                                 "email" => $email,
                                                 "address" => $address,
                                                 "status" => "active"
                                             ])
                                             ->execute();

                        if (!$customerResult) {

                            /*
                             * Remove user if customer record
                             * could not be created.
                             */
                            $db->from("users")
                               ->where("id", $userId)
                               ->delete()
                               ->execute();

                            $error = "Unable to create customer profile.";

                        } else {

                            $success =
                                "Registration successful. You can now login.";

                        }
                    }
                }
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

    <title>Customer Registration - Inventory System</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .register-card {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            margin-bottom: 8px;
        }

        .register-header p {
            color: #666;
        }

        .register-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .register-full {
            grid-column: 1 / -1;
        }

        .register-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .register-footer a {
            color: #5b4bc4;
            font-weight: 600;
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .register-button {
            width: 100%;
            margin-top: 10px;
        }

        @media (max-width: 600px) {

            .register-card {
                padding: 25px 20px;
            }

            .register-grid {
                grid-template-columns: 1fr;
            }

            .register-full {
                grid-column: auto;
            }

        }

    </style>

</head>

<body>

<div class="register-wrapper">

    <div class="register-card">

        <div class="register-header">

            <h1>Create Customer Account</h1>

            <p>
                Register to browse products and place orders.
            </p>

        </div>


        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="alert alert-success">

                <?= htmlspecialchars($success) ?>

                <br><br>

                <a href="login.php">
                    Go to Login
                </a>

            </div>

        <?php else: ?>


            <form method="POST">

                <div class="register-grid">


                    <!-- NAME -->

                    <div class="form-group register-full">

                        <label>
                            Full Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            placeholder="Enter your name"
                            value="<?= htmlspecialchars(
                                $_POST["name"] ?? ""
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label>
                            Email *
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= htmlspecialchars(
                                $_POST["email"] ?? ""
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="form-group">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            placeholder="Enter phone number"
                            value="<?= htmlspecialchars(
                                $_POST["phone"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label>
                            Password *
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Minimum 6 characters"
                            required
                        >

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label>
                            Confirm Password *
                        </label>

                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            required
                        >

                    </div>


                    <!-- COMPANY -->

                    <div class="form-group register-full">

                        <label>
                            Company
                        </label>

                        <input
                            type="text"
                            name="company"
                            placeholder="Company name (optional)"
                            value="<?= htmlspecialchars(
                                $_POST["company"] ?? ""
                            ) ?>"
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="form-group register-full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                            rows="4"
                            placeholder="Enter your address (optional)"
                        ><?= htmlspecialchars(
                            $_POST["address"] ?? ""
                        ) ?></textarea>

                    </div>


                    <!-- SUBMIT -->

                    <div class="register-full">

                        <button
                            type="submit"
                            class="btn btn-primary register-button"
                        >
                            Create Account
                        </button>

                    </div>

                </div>

            </form>


            <div class="register-footer">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>