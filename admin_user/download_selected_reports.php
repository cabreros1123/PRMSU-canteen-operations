<?php
// filepath: c:\xampp\htdocs\POS-PHP\admin_user\download_selected_reports.php
require_once "db.php";
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust path if needed
use Dompdf\Dompdf;

// Create a temp directory for the reports
$tmpDir = sys_get_temp_dir() . '/canteen_reports_' . uniqid();
mkdir($tmpDir);

// Helper: Save a file
function save_report($filename, $content, $tmpDir) {
    file_put_contents($tmpDir . '/' . $filename, $content);
}

function save_pdf_report($filename, $html, $tmpDir) {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($tmpDir . '/' . $filename, $dompdf->output());
}

// 1. Food Safety Ratings (GMP) - Download the full printable report as PDF
if (!empty($_POST['gmp'])) {
    foreach ($_POST['gmp'] as $gmp_val) {
        list($cantine_id, $group_code) = explode('|', $gmp_val);
        $cantine_id = intval($cantine_id);
        $group_code = $conn->real_escape_string($group_code);

        $canteen = $conn->query("SELECT name FROM cantines WHERE id=$cantine_id")->fetch_assoc();
        $group = $conn->query("SELECT note, date FROM food_safety_category_code WHERE group_code='$group_code'")->fetch_assoc();
        $ratings = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id=$cantine_id AND group_code='$group_code' ORDER BY section_no ASC");

        $section_count = $ratings->num_rows;
        $total_grade = 0;
        $ratings->data_seek(0);
        while ($s = $ratings->fetch_assoc()) {
            $section_grade = ($s['rating'] >= 1 && $s['rating'] <= 5) ? ($s['rating'] * 20) : 0;
            $total_grade += $section_grade * 0.1;
        }
        $max_grade = $section_count * 10;
        $final_grade = $section_count > 0 ? round(($total_grade / $max_grade) * 100, 1) : 0;

        $html = "<h2>Food Safety Rating Report</h2>
        <b>Canteen:</b> " . htmlspecialchars($canteen['name']) . "<br>
        <b>Date:</b> " . htmlspecialchars($group['date']) . "<br>
        <b>Note:</b> " . htmlspecialchars($group['note']) . "<br>
        <b>GMP Grade:</b> $final_grade%<br><br>
        <table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>
            <tr style='background:#f3f3f3;'>
                <th>Section</th>
                <th>Rating</th>
                <th>Note</th>
                <th>Photo</th>
            </tr>";

        $ratings = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id=$cantine_id AND group_code='$group_code' ORDER BY section_no ASC");
        while ($row = $ratings->fetch_assoc()) {
            $ratingText = '';
            switch ($row['rating']) {
                case 5: $ratingText = 'Excellent (100%)'; break;
                case 4: $ratingText = 'Good (80%)'; break;
                case 3: $ratingText = 'Improve (60%)'; break;
                case 2: $ratingText = 'Poor (40%)'; break;
                case 1: $ratingText = 'Very Poor (20%)'; break;
                default: $ratingText = 'N/A'; break;
            }
            $html .= "<tr>
                <td>" . htmlspecialchars($row['section_title']) . "</td>
                <td>" . $ratingText . "</td>
                <td>" . htmlspecialchars($row['evidence']) . "</td>
                <td>";
            if (!empty($row['img']) && $row['img'] !== 'NULL') {
                $html .= "Photo available";
            } else {
                $html .= "No photo";
            }
            $html .= "</td>
            </tr>";
        }
        $html .= "</table>";

        save_pdf_report("GMP_Report_Canteen_{$cantine_id}_{$group_code}.pdf", $html, $tmpDir);
    }
}

// 2. Obligation Reports as PDF
if (!empty($_POST['obligation'])) {
    foreach ($_POST['obligation'] as $ob_id) {
        $ob_id = intval($ob_id);
        $ob = $conn->query("SELECT * FROM obligations WHERE id=$ob_id")->fetch_assoc();
        $canteen = $conn->query("SELECT name FROM cantines WHERE id={$ob['cantine_id']}")->fetch_assoc();
        $ob_data = json_decode($ob['obligation_and_status'], true);
        $complied = 0;
        foreach ($ob_data as $item) if ($item['status'] === 'Complied') $complied++;
        $html = "<h2>Obligation Report</h2>
        <b>Canteen:</b> {$canteen['name']}<br>
        <b>Date:</b> {$ob['date_added']}<br>
        <b>Complied:</b> $complied/" . count($ob_data) . "<br>
        <b>Details:</b><ul>";
        foreach ($ob_data as $item) {
            $html .= "<li>{$item['obligation']} - <b>{$item['status']}</b></li>";
        }
        $html .= "</ul>";
        save_pdf_report("Obligation_{$canteen['name']}_{$ob_id}.pdf", $html, $tmpDir);
    }
}

// 3. Verified Bills Payments - Download the Excel file
if (!empty($_POST['bills'])) {
    foreach ($_POST['bills'] as $cantine_id) {
        $cantine_id = intval($cantine_id);
        $year = date('Y');
        // Fetch the Excel file from the export endpoint
        $url = "http://localhost/POS-PHP/admin_user/export_canteen_excel.php?cantine_id=$cantine_id&year=$year";
        $excel = @file_get_contents($url);
        if ($excel !== false) {
            save_report("Bills_{$cantine_id}_{$year}.xls", $excel, $tmpDir);
        } else {
            save_report("Bills_{$cantine_id}_{$year}_ERROR.txt", "Could not fetch Excel from $url", $tmpDir);
        }
    }
}

// Zip all files
$zipname = $tmpDir . '.zip';
$zip = new ZipArchive();
$zip->open($zipname, ZipArchive::CREATE);
foreach (glob("$tmpDir/*") as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

// Send the zip for download
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=selected_reports.zip');
header('Content-Length: ' . filesize($zipname));
readfile($zipname);

// Clean up
foreach (glob("$tmpDir/*") as $file) unlink($file);
rmdir($tmpDir);
unlink($zipname);
exit;
?>