<?php
require_once 'db.php';
$inspection_id = isset($_GET['inspection_id']) ? intval($_GET['inspection_id']) : 0;
$res = $conn->query("SELECT * FROM obligations WHERE id=$inspection_id LIMIT 1");
if (!$row = $res->fetch_assoc()) {
    die("Inspection not found.");
}
$obligations = json_decode($row['obligation_and_status'], true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inspection Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; }
        h2 { margin-bottom: 8px; }
        .section-title { font-weight: bold; margin-top: 24px; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px;}
        th, td { border: 1px solid #aaa; padding: 6px 10px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Canteen Inspection Report</h2>
    <div class="section-title">1. TERM OF THE CONTRACT</div>
    <div>
        This contract shall be for a term of one (1) year commencing on January, <?= date('Y', strtotime($row['date_added'])) ?> subject to an Annual Performance Appraisal to be conducted by the OWNER up to December, <?= date('Y', strtotime($row['date_added'])) ?>.
        <br>
        Sixty (60) days before the expiration of the (1) year period of this contract, the CANTEEN OPERATOR shall notify the OWNER of his/her intention to continue or not the operation of the canteen.
    </div>
    <div class="section-title">2. OBLIGATIONS OF THE OWNER</div>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Obligation</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($obligations as $ob): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($ob['obligation']) ?></td>
                <td><?= htmlspecialchars($ob['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
        $complied = 0;
        $pending = 0;
        $not_complied = 0;
        foreach ($obligations as $ob) {
            if ($ob['status'] === 'Complied') $complied++;
            elseif ($ob['status'] === 'Pending') $pending++;
            elseif ($ob['status'] === 'Not Complied') $not_complied++;
        }
    ?>
    <div style="margin-top:18px; font-size:1.1em;">
        <strong>Summary:</strong><br>
        Complied: <?= $complied ?><br>
        Pending: <?= $pending ?><br>
        Not Complied: <?= $not_complied ?>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>