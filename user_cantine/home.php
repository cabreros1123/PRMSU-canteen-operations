<?php
session_start(); // Ensure session is started before using $_SESSION

// Check if 'id_cantine' is set in session to avoid warnings
if (!isset($_SESSION['id_cantine'])) {
    die('Error: You are not authorized to view this page. Please log in.');
}

$id_cantine = $_SESSION['id_cantine']; // Get the logged-in canteen's ID

include 'db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <script src="js/chart.js"></script>
    <link rel="stylesheet" href="css/home.css">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 24px;
        }

        .col-lg-3, .col-xs-6 {
            flex: 1 1 220px;
            max-width: 260px;
            min-width: 180px;
        }

        .small-box {
            margin: 0 auto;
            width: 100%;
            min-width: 160px;
            max-width: 260px;
        }

        /* Responsive: Stack boxes vertically on mobile */
        @media (max-width: 700px) {
            .row {
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }
            .col-lg-3, .col-xs-6 {
                max-width: 95vw;
                min-width: 0;
                width: 100%;
            }
            .small-box {
                max-width: 95vw;
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php require_once "header.php"; ?>
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3></h3> <!-- Display total sales -->
                    <p>Verified Payments</p>
                </div>
                <div class="icon">
                    <i class="pesos"></i>
                </div>
                <a href="verified_payments.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3></h3> <!-- Display total categories -->
                    <p>Messager</p>
                </div>
                <div class="icon">
                    <i class="ion ion-clipboard"></i>
                </div>
                <a href="messenger.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3></h3> <!-- Display total products -->
                    <p>Add Bills</p>
                </div>
                <div class="icon">
                    <i class="ion ion-ios-cart"></i>
                </div>
                <a href="add_bills.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>