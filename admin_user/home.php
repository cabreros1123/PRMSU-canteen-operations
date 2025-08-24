<?php
include 'db.php';

// Fetch the total sales from the database where del_status = 0
$query = "SELECT SUM(product_sale) AS total_sales FROM sales WHERE del_status = 0";
$result = $conn->query($query);
$totalSales = 0;

if ($result && $row = $result->fetch_assoc()) {
    $totalSales = $row['total_sales'] ?? 0; // Default to 0 if no sales
}


// Fetch the count of active and not soft-deleted cantines
$query = "SELECT COUNT(*) AS total_cantines FROM cantines WHERE del_status = 0 AND active = 0";
$result = $conn->query($query);
$totalCantines = 0;

if ($result && $row = $result->fetch_assoc()) {
    $totalCantines = $row['total_cantines'] ?? 0; // Default to 0 if no cantines
}

// --- Ledger Total (sum of verified bills only) ---
$query = "SELECT SUM(payment) AS total_ledger FROM bills WHERE ver_status = 2 AND del_status = 0";
$result = $conn->query($query);
$totalLedger = 0;
if ($result && $row = $result->fetch_assoc()) {
    $totalLedger = $row['total_ledger'] ?? 0;
}

// --- Latest Best Food Safety Rating Canteen ---
$latestBestName = 'N/A';
$latestBestGmp = 'N/A';
$bestQ = $conn->query("
    SELECT c.name, f.group_code, f.rated_at,
        ROUND(SUM(CASE WHEN f.rating BETWEEN 1 AND 5 THEN f.rating * 20 * 0.1 ELSE 0 END) / (COUNT(*) * 10) * 100, 1) AS gmp
    FROM food_safety_ratings f
    JOIN cantines c ON f.cantine_id = c.id
    WHERE YEAR(f.rated_at) = YEAR(CURDATE())
    GROUP BY f.cantine_id, f.group_code
    HAVING gmp >= 80
    ORDER BY f.rated_at DESC
    LIMIT 1
");
if ($bestQ && $bestRow = $bestQ->fetch_assoc()) {
    $latestBestName = $bestRow['name'];
    $latestBestGmp = $bestRow['gmp'] . '%';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/home.css" />
    <link rel="icon" type="image/x-icon" href="img/icono-negro.ico">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <title>Home</title>
</head>
<body>
    <?php require_once "sidebar.php"; ?>
    <?php require_once "header.php"; ?>
<br>
    <div class="row">

        <!-- Ledger Box -->
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>₱<?php echo number_format($totalLedger, 2); ?></h3>
                    <p>Ledger</p>
                </div>
                <div class="icon">
                    <i class="ion ion-ios-book"></i>
                </div>
                <a href="cantine_bills.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Food Safety Rating Box -->
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>
                        <?php echo htmlspecialchars($latestBestName); ?>
                        <span style="font-size:0.9em;color:#051650;">GMP: <?php echo htmlspecialchars($latestBestGmp); ?></span>
                    </h3>
                    <p>Latest Best Rating Canteen</p>
                </div>
                <div class="icon">
                    <i class="ion ion-clipboard"></i>
                </div>
                <a href="food_safety_rating.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>_</h3>
                    <p>Obligations Status</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="food_safety.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo $totalCantines; ?></h3>
                    <p>Cantines</p>
                </div>
                <div class="icon">
                    <i class="ion ion-ios-cart"></i>
                </div>
                <a href="cantine.php" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
        <!-- Dashboard Lower Boxes: GMP Graph and Canteen Status -->
    <div style="display: flex; gap: 18px; margin-top: 18px;">
        <!-- GMP Graph Box -->
        <div style="flex: 1 1 0; border: 4px solid #039be5; border-radius: 6px; background: #f7fafc; padding: 18px 12px 12px 12px; margin-right: 8px; min-width: 0;">
            <div style="font-size:1.1em;font-weight:bold;margin-bottom:10px;">Total GMP this <?php echo date('Y'); ?></div>
            <canvas id="canteenGmpGraph" height="170"></canvas>
        </div>
        <!-- Canteen Status Box -->
        <div style="flex: 1 1 0; border: 4px solid #e53935; border-radius: 6px; background: #fff8f0; padding: 18px 12px 12px 12px; min-width: 0;">
            <div style="font-size:1.1em;font-weight:bold;margin-bottom:10px;">Canteen Status</div>
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:separate; border-spacing:0 8px;">
                <thead>
                    <tr style="background:none;">
                        <th style="border:none; text-align:left; padding:6px 10px;">Name</th>
                        <th style="border:none; text-align:left; padding:6px 10px;">Owner</th>
                        <th style="border:none; text-align:left; padding:6px 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $canteenStatusQ = $conn->query("SELECT name, owner, active FROM cantines WHERE del_status=0 ORDER BY name ASC");
                while($ct = $canteenStatusQ->fetch_assoc()):
                    $isActive = ($ct['active'] == 0);
                    $rowColor = $isActive ? '#fffde7' : '#ffe0e0';
                    $borderColor = '#ffd600';
                ?>
                    <tr style="background:<?php echo $rowColor; ?>; border:2px solid <?php echo $borderColor; ?>; border-radius:6px;">
                        <td style="padding:8px 10px; border:none; font-weight:600; color:#1976d2;"> <?php echo htmlspecialchars($ct['name']); ?> </td>
                        <td style="padding:8px 10px; border:none; color:#333;"> <?php echo htmlspecialchars($ct['owner']); ?> </td>
                        <td style="padding:8px 10px; border:none; font-weight:600; color:<?php echo $isActive ? '#43a047' : '#d32f2f'; ?>;"> <?php echo $isActive ? 'Active' : 'Deactive'; ?> </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Chart.js for GMP Graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php
    // --- GMP Graph Data (reuse from food_safety_rating.php) ---
    $currentYear = date('Y');
    $canteen_grades = [];
    $canteens_for_graph = $conn->query("SELECT id, name FROM cantines ORDER BY name ASC");
    while($c = $canteens_for_graph->fetch_assoc()) {
        $cid = intval($c['id']);
        // Fetch all group codes for this canteen for the current year only
        $groups = [];
        $res = $conn->query("SELECT group_code, MIN(rated_at) as rated_at FROM food_safety_ratings WHERE cantine_id=$cid AND YEAR(rated_at)=$currentYear GROUP BY group_code");
        while($row = $res->fetch_assoc()) {
            $groups[] = $row;
        }
        // Fetch all ratings grouped by group_code for the current year only
        $all_ratings = [];
        $res2 = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id=$cid AND YEAR(rated_at)=$currentYear ORDER BY rated_at DESC, group_code DESC, section_no ASC");
        while($row2 = $res2->fetch_assoc()) {
            $all_ratings[$row2['group_code']][] = $row2;
        }
        // Calculate all final grades for this canteen
        $final_grades = [];
        foreach ($groups as $g) {
            $group_code = $g['group_code'];
            $ratings = $all_ratings[$group_code] ?? [];
            $section_count = count($ratings);
            $total_grade = 0;
            foreach ($ratings as $s) {
                $section_grade = 0;
                if ($s['rating'] >= 1 && $s['rating'] <= 5) {
                    $section_grade = ($s['rating'] * 20);
                }
                $total_grade += $section_grade * 0.1;
            }
            $max_grade = $section_count * 10;
            $final_grade = $section_count > 0 ? round(($total_grade / $max_grade) * 100, 1) : 0;
            if ($final_grade > 0) $final_grades[] = $final_grade;
        }
        // Average grade for this canteen
        $avg_grade = count($final_grades) > 0 ? round(array_sum($final_grades) / count($final_grades), 2) : 0;
        $canteen_grades[] = [
            'name' => $c['name'],
            'avg_grade' => $avg_grade
        ];
    }
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const canteenLabels = <?php echo json_encode(array_column($canteen_grades, 'name')); ?>;
        const canteenGrades = <?php echo json_encode(array_column($canteen_grades, 'avg_grade')); ?>;
        const ctx = document.getElementById('canteenGmpGraph').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: canteenLabels,
                datasets: [{
                    label: 'Average GMP Grade (%)',
                    data: canteenGrades,
                    backgroundColor: '#1976d2',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: { display: true, text: 'GMP Grade (%)' }
                    },
                    x: {
                        title: { display: true, text: 'Canteens' }
                    }
                },
                animation: {
                    duration: 800
                }
            }
        });
    });
    </script>

</body>
</html>