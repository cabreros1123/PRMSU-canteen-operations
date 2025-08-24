<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        body {
            background: linear-gradient(to bottom, #f8e8c1,rgb(194, 176, 136)); /* Parchment-like background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            overflow: hidden; /* Prevent scrollbars from appearing */
            position: relative;
        }
        .container {
            text-align: center;
            z-index: 10; /* Ensure the content stays above the effect */
            position: relative;
        }
        .btn {
            display: block;
            width: 200px;
            padding: 15px;
            margin: 10px auto;
            border: none;
            border-radius: 5px;
            background-color: white;
            color: black;
            font-size: 16px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
            font-weight: bold;
            opacity: 0; /* Start hidden */
        }
        .btn:hover {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            background-color: #f0f0f0; /* Slightly change the background color on hover */
        }
        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%; /* Start the shine effect off-screen */
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255, 217, 30, 0.75), rgba(15, 0, 20, 0));
            transform: skewX(-45deg); /* Create the angled shine effect */
            transition: none;
        }
        .btn:hover::before {
            left: 100%; /* Move the shine effect across the button */
            transition: left 0.5s ease; /* Smooth animation for the shine effect */
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            width: 90px;
            height: auto;
            opacity: 0; /* Start hidden */
            animation: slideDown 1s ease-out forwards; /* Slide down animation */
            animation-delay: 3s; /* Delay the logo animation by 3 seconds */
        }
        .header-text {
            margin-bottom: 20px;
            opacity: 0; /* Start hidden */
            animation: slideDown 1s ease-out forwards; /* Slide down animation */
            font-family: "Cinzel Decorative", serif; /* Decorative font for 1600s style */
            font-size: 36px; /* Larger font size for grandeur */
            color: #4b2e2e; /* Dark brown color for an antique look */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); /* Add depth with a shadow */
            letter-spacing: 2px; /* Slightly spaced letters for elegance */
            text-align: center;
        }
        .header-text h2 {
            margin: 0;
            font-weight: bold;
        }
        .header-text p {
            margin: 0;
            font-size: 20px;
            font-style: italic; /* Add an italicized subtitle */
            color: #6b4e4e; /* Slightly lighter brown for contrast */
        }
        .btn.admin {
            animation: slideInLeft 1.2s ease-out forwards; /* Slide in from left */
            animation-delay: 0.5s; /* Delay for staggered effect */
        }
        .btn.canteen {
            animation: slideInRight 1.5s ease-out forwards; /* Slide in from right */
            animation-delay: 0.8s; /* Delay for staggered effect */
        }
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .cursor-effect {
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            filter: blur(30px); /* Add a blur effect */
            pointer-events: none; /* Prevent interaction with the effect */
            transform: translate(-50%, -50%);
            transition: opacity 0.2s ease-out;
            opacity: 0; /* Start invisible */
        }
        .cursor-effect.active {
            opacity: 1; /* Make the effect visible when active */
        }
        .cursor-tail {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            pointer-events: none; /* Prevent interaction with the tail */
            animation: fadeOut 1s ease-out forwards;
        }
        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: scale(0.5);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <a href="index.php">
            <img src="icono-negro.png" alt="Logo" class="logo">
        </a>
        
        <!-- Header Text -->
        <div class="header-text">
            <h2>PRMSU IBA CANTEENS PORTAL</h2>
            <p>Management System</p>
        </div>
        <br><br>
        <!-- Buttons -->
        <button class="btn admin" onclick="location.href='admin_login.php'">Admin Login</button>
        <button class="btn canteen" onclick="location.href='cantine_login.php'">Canteen Login</button>
    </div>

    <!-- Cursor Effect -->
    <div class="cursor-effect" id="cursorEffect"></div>

    <script>
        const cursorEffect = document.getElementById("cursorEffect");

        // Track mouse movement and position the effect
        document.addEventListener("mousemove", (e) => {
            cursorEffect.style.left = `${e.pageX}px`;
            cursorEffect.style.top = `${e.pageY}px`;
            cursorEffect.classList.add("active");
        });

        // Hide the effect when the mouse stops moving
        document.addEventListener("mouseout", () => {
            cursorEffect.classList.remove("active");
        });

        document.addEventListener("mousemove", (e) => {
            const tail = document.createElement("div");
            tail.classList.add("cursor-tail");
            tail.style.left = `${e.pageX}px`;
            tail.style.top = `${e.pageY}px`;
            document.body.appendChild(tail);

            // Remove the tail after the animation ends
            setTimeout(() => {
                tail.remove();
            }, 1000); // Matches the duration of the fadeOut animation
        });
    </script>
</body>
</html>