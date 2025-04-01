<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to access session variables
session_start();

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Include database connection
require_once 'db_conn.php'; // Ensure this file contains the $conn PDO instance

// Handle Registration
if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['register_email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_role = $_POST['user_role'];
    $profile_pic = 'profile_pic.png';



    // Validate inputs
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)|| empty($user_role)) {
        echo "<script>alert('All fields are required!');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare and execute the query using PDO
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, password, user_role, profile_pic) VALUES (:fullname, :email, :phone, :password, :user_role, :profile_pic)");
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_role', $user_role);
            $stmt->bindParam(':profile_pic', $profile_pic);


            if ($stmt->execute()) {
                header("Location: index.php?registration=success");
                exit();
            } else {
                echo "<script>alert('Email already exists or an error occurred!');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['login_email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        try {
            // Prepare and execute the query using PDO
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['id'] = $user['id'];
                $_SESSION['user'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['user_role']; // Store user role in session

                // Redirect users based on their role
                if (in_array($user['user_role'], [100, 111, 222])) {
                    header("Location: home_page.php");
                } elseif (in_array($user['user_role'], [333, 444])) {
                    header("Location: stock_control.php");
                } else {
                    header("Location: default_page.php"); // Optional: Redirect to a default page
                }
                exit();
            } else {
                echo "<script>alert('Invalid login credentials!');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}

// Close the database connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register/Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        .navbar-text {
            font-family: 'Great Vibes', cursive;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <!-- Transparent Navbar -->
    <nav class="bg-transparent fixed w-full top-0 left-0 py-4 px-8 shadow-md">
        <div class="flex items-center justify-between">
            <div class="text-5xl font-semibold navbar-text text-indigo-600">
                <a href="#" class="hover:text-indigo-700">Mono Dev</a>
            </div>
            <div>
                <a href="#register-form" class="text-indigo-600 hover:text-indigo-700 italic font-semibold">Login</a>
            </div>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-3xl bg-white shadow-lg rounded-lg p-8 space-y-6">
            <div class="text-center">
                <div class="text-5xl font-semibold navbar-text text-indigo-600">
                    <a href="#" class="hover:text-indigo-700">Mono Dev</a>
                </div>
                <p class="text-gray-500 text-sm mt-2">Welcome! Please fill out the form to continue.</p>
            </div>

            <!-- Registration Form -->
            <div id="register-form" class="hidden">
                <form method="POST" class="space-y-4" onsubmit="return validateForm()">
                    <div>
                        <label for="reg-fullname" class="block text-sm font-medium text-gray-700">Full Name:</label>
                        <input type="text" id="reg-fullname" name="fullname" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="register_email" class="block text-sm font-medium text-gray-700">Email:</label>
                            <input type="email" id="register_email" name="register_email" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone:</label>
                            <input type="tel" id="phone" name="phone" pattern="[0-9]{10}"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="1234567890">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="reg-password" class="block text-sm font-medium text-gray-700">Password:</label>
                            <input type="password" id="reg-password" name="password" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                            <input type="password" id="confirm-password" name="confirm_password" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="error-message" class="text-red-500 text-sm mt-1 hidden">Passwords do not match!</p>
                        </div>
                    </div>
                    <div>
                        <label for="user_role" class="block text-sm font-medium text-gray-700">User Role:</label>
                        <select id="user_role" name="user_role" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="555">Directore of operations</option>
                            <option value="444">Administration Manage</option>
                            <option value="333">Stock Controler</option>
                            <option value="222">Branch Manager</option>
                            <option value="111">Bank Officer</option>
                            <option value="100">System Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" required
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="terms" class="ml-2 text-sm text-gray-700">I agree to the <a href="#" class="text-indigo-600 hover:underline">Terms and Conditions</a></label>
                    </div>

                    <div>
                        <button type="submit" name="register"
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition duration-300">
                            Register
                        </button>
                    </div>
                </form>

                <p class="mt-4 text-center text-sm text-gray-600">
                    Already have an account? <a href="javascript:void(0);" onclick="toggleForms()" class="text-indigo-600 hover:text-indigo-700">Login here</a>
                </p>
            </div>

            <!-- Login Form -->
            <div id="login-form">
                <form method="POST" class="space-y-4" onsubmit="return validateLoginForm()">
                    <div>
                        <label for="login_email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" id="login_email" name="login_email" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="login-password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" id="login-password" name="password" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <button type="submit" name="login"
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition duration-300">
                            Login
                        </button>
                    </div>
                </form>

                <p class="mt-4 text-center text-sm text-gray-600">
                    Don't have an account? <a href="javascript:void(0);" onclick="toggleForms()" class="text-indigo-600 hover:text-indigo-700">Register here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleForms() {
            document.querySelectorAll('#register-form, #login-form').forEach(form => form.classList.toggle('hidden'));
        }

        function validateForm() {
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const errorMessage = document.getElementById('error-message');
            
            const isValid = password === confirmPassword;
            errorMessage.classList.toggle('hidden', isValid);
            return isValid;
        }

        function validateLoginForm() {
            return true; // Always allow login form submission
        }
    </script>

</body>
</html>