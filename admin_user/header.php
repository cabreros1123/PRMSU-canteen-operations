<!-- filepath: c:\xampp\htdocs\POS-PHP\admin_user\header.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <style>
        .header {
            width: 100%;
            background-color: #FFFFF0;
            color: black;
            padding: 25px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Welcome to the Admin Panel</h1>
        <nav>
            <a><?php echo $adminName; ?></a>
        </nav>
    </header>
</body>
</html>