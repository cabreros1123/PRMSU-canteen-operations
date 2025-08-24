<?php
// Start the session at the very beginning of the file
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['loginUser'];
    $password = $_POST['loginPass'];

    $conn = new mysqli('localhost', 'root', '', 'posystem');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use BINARY for case-sensitive comparison
    $sql = "SELECT * FROM users WHERE BINARY user = ? AND BINARY password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $_SESSION['user'] = $row['user']; // Store username
        $_SESSION['admin_name'] = $row['name']; // Store admin name as admin_name

        // Update the current user's status to active
        $updateSql = "UPDATE users SET status = 1 WHERE BINARY user = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('s', $username);
        $updateStmt->execute();
        $updateStmt->close();

        header("Location: admin_user/home.php"); // Redirect to homepage
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<html>

<head>
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        body {
            background: linear-gradient(to bottom, #f8e8c1,rgb(165, 139, 84)); /* Parchment-like background */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px; /* Ensures compatibility with mobile screens */
            box-sizing: border-box;
        }
        .login-box-body {
            margin-top: 20px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #218838;
        }
        .login-link {
            margin-top: 10px;
            display: block;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        .login-link:hover {
            text-decoration: underline;
        }
        .header-title {
            font-family: "Cinzel Decorative", serif; /* Decorative font for 1600s style */
            font-size: 36px; /* Larger font size for grandeur */
            color: #4b2e2e; /* Dark brown color for an antique look */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); /* Add depth with a shadow */
            letter-spacing: 2px; /* Slightly spaced letters for elegance */
            text-align: center;
            margin-bottom: 20px;
            animation: fadeIn 2s ease-out forwards; /* Smooth fade-in animation */
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Slide-up animation */
        .slide-up {
            width: 100%;
            max-width: 320px; /* Match login box width */
            opacity: 0;
            transform: translate3d(0, 100%, 0);
            transition: transform 1s ease-out, opacity 1s ease-out;
        }
        .slide-up.active {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
        /* Responsive Design */
        @media (max-width: 480px) {
            .login-box {
                max-width: 90%;
                padding: 15px;
            }
            .slide-up {
                max-width: 90%;
            }
            .header-title {
                font-size: 28px;
            }
            .btn {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&display=swap" rel="stylesheet">
    <script>
        // Wait for the page to load before applying the animation
        window.onload = function() {
            document.querySelector('.slide-up').classList.add('active');
        };
    </script>
    <!-- Add this at the top of admin_login.php, before any other JS -->
    <script>
        // Clear all sessionStorage on login page load (after logout)
        sessionStorage.clear();
    </script>
</head>
<body>
<img class="slide-up" src="logo-blanco-bloque.png" style="padding: 25px 90px 0 90px">
<br>
<div id="back"></div>
<div class="login-box">
    <div class="header-title">Admin Login</div>
    <div class="login-box-body">
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" placeholder="Username" name="loginUser" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="Password" name="loginPass" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-success btn-block btn-flat">Log In</button>
                </div>
            </div>
            <a href="/POS-PHP/index.php" class="login-link">Exit</a>
        </form>
    </div>
</div>
<style>

.wave {
    background: rgb(255 255 255 / 25%);
    border-radius: 1000% 1000% 0 0;
    position: fixed;
    width: 200%;
    height: 12em;
    animation: wave 10s -3s linear infinite;
    transform: translate3d(0, 0, 0);
    opacity: 0.8;
    bottom: 0;
    left: 0;
    z-index: -1;
}

.wave:nth-of-type(2) {
    bottom: -1.25em;
    animation: wave 18s linear reverse infinite;
    opacity: 0.8;
}

.wave:nth-of-type(3) {
    bottom: -2.5em;
    animation: wave 20s -1s reverse infinite;
    opacity: 0.9;
}

@keyframes wave {
    2% {
        transform: translateX(1);
    }

    25% {
        transform: translateX(-25%);
    }

    50% {
        transform: translateX(-50%);
    }

    75% {
        transform: translateX(-25%);
    }

    100% {
        transform: translateX(1);
    }
}
</style>
		
	<div>
     <div class="wave"></div>
     <div class="wave"></div>
     <div class="wave"></div>
  </div>

</body>
</html>