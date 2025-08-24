<?php
require_once "db.php";
$cantine_id = intval($_GET['cantine_id'] ?? 0);
$year = intval($_GET['year'] ?? date('Y'));

// Fetch all group codes, notes, and dates for this canteen from food_safety_category_code
$groups = [];
$res = $conn->query("SELECT id, group_code, note, date FROM food_safety_category_code WHERE group_code IN (
    SELECT DISTINCT group_code FROM food_safety_ratings WHERE cantine_id=$cantine_id AND YEAR(rated_at)=$year
) ORDER BY date DESC");
while($row = $res->fetch_assoc()) {
    $groups[] = $row;
}

// Fetch all ratings grouped by group_code
$all_ratings = [];
$res2 = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id=$cantine_id AND YEAR(rated_at)=$year ORDER BY rated_at DESC, group_code DESC, section_no ASC");
while($row2 = $res2->fetch_assoc()) {
    $all_ratings[$row2['group_code']][] = $row2;
}

// Fetch notes for all group codes in this canteen
$notes = [];
if (!empty($groups)) {
    $group_codes = array_map(function($g) use ($conn) { return "'".$conn->real_escape_string($g['group_code'])."'"; }, $groups);
    $group_codes_str = implode(',', $group_codes);
    $note_res = $conn->query("SELECT group_code, note FROM food_safety_category_code WHERE group_code IN ($group_codes_str)");
    while($n = $note_res->fetch_assoc()) {
        $notes[$n['group_code']] = $n['note'];
    }
}

if (!$groups) {
    echo "<h3>No ratings found for this canteen.</h3>";
    exit;
}

echo "<div style='font-weight:bold;font-size:1.1em;margin-bottom:8px;'>Past Inspections</div>";

foreach ($groups as $idx => $g) {
    $group_code = $g['group_code'];
    $note = htmlspecialchars($g['note']);
    $date = date('F j, Y - g:ia', strtotime($g['date']));

    // Fetch ratings for this group code
    $ratings = $all_ratings[$group_code] ?? [];
    $section_count = count($ratings);
    $total_grade = 0;
    foreach ($ratings as $s) {
        $section_grade = ($s['rating'] >= 1 && $s['rating'] <= 5) ? ($s['rating'] * 20) : 0;
        $total_grade += $section_grade * 0.1;
    }
    $max_grade = $section_count * 10;
    $final_grade = $section_count > 0 ? round(($total_grade / $max_grade) * 100, 1) : 0;

    // Prepare search string (for group code only)
    $searchString = strtolower($group_code);

    echo "<div class='gmp-search-item' data-search=\"{$searchString}\" style='margin-bottom:8px;'>";
    echo "<button type='button' class='gmp-dropdown-btn' onclick=\"toggleGmpGroup('gmpgroup$idx')\" style='width:100%;text-align:left;padding:10px 16px;font-size:1em;border-radius:8px;border:1px solid #ccc;background:#f7f7f7;cursor:pointer;'>";
    echo "<b>$date</b> &nbsp; <span style='color:#888;'>$group_code</span>";
    if ($note) {
        echo " <span style='color:#388e3c;font-weight:600;background:#eafbe7;padding:2px 8px;border-radius:6px;margin-left:8px;'>$note</span>";
    }
    echo " <span style='color:#1976d2;font-weight:bold;'>$final_grade%</span>";
    echo " <span style='float:right;' id='arrow-gmpgroup$idx'>&#9660;</span>";
    echo "</button>";
    echo "<div id='gmpgroup$idx' class='gmp-dropdown-content' style='display:none;padding:10px 0 0 0;'>";
    // Ratings table for this group
    echo "<table style='width:100%;border-collapse:collapse;'>";
    echo "<tr><th style='text-align:left;'>Section</th><th>Rating</th><th>Note</th><th>Photo</th></tr>";
    foreach ($ratings as $s) {
        $ratingLabel = ['1'=>'Very poor','2'=>'Poor','3'=>'Improve','4'=>'Good','5'=>'Excellent'][$s['rating']] ?? $s['rating'];
        $section_grade = ($s['rating'] >= 1 && $s['rating'] <= 5) ? ($s['rating'] * 20) : 0;
        echo "<tr>";
        echo "<td>".htmlspecialchars($s['section_title'])."</td>";
        echo "<td style='font-weight:bold;'>$ratingLabel <span style='color:#1976d2;font-size:0.95em;'>($section_grade%)</span></td>";
        echo "<td>".htmlspecialchars($s['evidence'])."</td>";
        echo "<td>";
        if ($s['img']) {
            echo "<a href='../{$s['img']}' target='_blank'><img src='../{$s['img']}' class='view-img'></a>";
        } else {
            echo "<span style='color:#aaa;'>No photo</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    // Print button for this group only
    echo "<button onclick=\"printGmpReport('gmpgroup$idx', '$group_code', '$date')\" class='save-btn no-print' style='background:#1976d2;color:#fff;margin-top:12px;'>Print This Inspection</button>";
    echo "</div>";
    echo "</div>";
}

echo "<button onclick='closeViewModal()' class='save-btn' style='background:#888;margin-top:18px;'>Exit</button>";
?>
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .view-img {
        width: 2cm !important;
        height: 2cm !important;
        object-fit: cover !important;
        border: 1px solid #888;
    }
}
.view-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #888;
}
</style>
<script>
function printGmpReport(groupId, groupCode, date) {
    // Clone the group content so we can modify it
    var groupContentElem = document.getElementById(groupId).cloneNode(true);
    // Remove all elements with class 'no-print'
    groupContentElem.querySelectorAll('.no-print').forEach(function(el) {
        el.parentNode.removeChild(el);
    });
    var groupContent = groupContentElem.innerHTML;

    var contractImg = '<img src="PATH_TO_YOUR_CONTRACT_IMAGE.png" style="max-width:80px;height:auto;display:block;margin-bottom:18px;">';
    var contractText = `<div style="margin-bottom:18px;">
        <b>1. TERM OF THE CONTRACT</b><br>
        This contract shall be for a term of one (1) year commencing on January, 2025 subject to an Annual Performance Appraisal to be conducted by the OWNER up to December, 2025.<br>
        Sixty (60) days before the expiration of the (1) year period of this contract, the CANTEEN OPERATOR shall notify the OWNER of his/her intention to continue or not the operation of the canteen.
    </div>`;    
    var summary = `<div style="margin-bottom:18px;">
        <b>GMP Rating for Food Safety</b><br>
        Group Code: ${groupCode}<br>
        Date: ${date}
    </div>`;
    var printWindow = window.open('', '', 'width=900,height=700');
    printWindow.document.write('<html><head><title>GMP Report</title>');
    printWindow.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#1976d2;color:#fff;} img.view-img{width:2cm;height:2cm;object-fit:cover;border:1px solid #888;} @media print {.no-print{display:none!important;}}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(contractImg);
    printWindow.document.write(contractText);
    printWindow.document.write(summary);
    printWindow.document.write(groupContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function toggleGmpGroup(groupId) {
    var content = document.getElementById(groupId);
    var arrow = document.getElementById('arrow-' + groupId);
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        if (arrow) arrow.innerHTML = '&#9650;'; // up arrow
    } else {
        content.style.display = 'none';
        if (arrow) arrow.innerHTML = '&#9660;'; // down arrow
    }
}
</script>