<?php
require_once "db.php";

// --- DEFINE $sections FIRST ---
$sections = [
    ["title" => "Equipment Hygiene", "desc" => "- Utensils (e.g knives)/Tables/Sinks<br>- Fridges/Freezers/Chilled-Hot Display Cabinets/Vending Machines<br>- Microwaves"],
    ["title" => "Environmental Hygiene", "desc" => "- Floor areas should be clean and not slippery under foot<br>- Evidence of 'clean as you go' to keep area hygienic<br>- Racking / Cupboards clean"],
    ["title" => "Personal Hygiene", "desc" => "- No personal possessions or jewellery.<br>- Blue plasters only.<br>- No eating or drinking permitted (exception water).<br>- Changing room and toilet should be suitable and maintained in a sound condition."],
    ["title" => "Clothing and PPE Requirements", "desc" => "- Uniform worn correctly<br>- PPE available - Oven gloves, chain gloves...<br>- Hairnets must be correctly worn by all, ears must be covered.<br>- Safety boots worn by all."],
    ["title" => "Foreign Body Controls", "desc" => "- Evidence of broken glass / brittle plastic<br>- Loose nuts and bolts found<br>- Pest control issues<br>- Miscellaneous foreign bodies in area - ripped cardboard, wood, broken equipment, peeling labels, cable ties etc."],
    ["title" => "Engineering & Fabrication", "desc" => "- Temporary fixes including any 'selotape' engineering.<br>- Damage to the equipment including rust and flaking paint<br>- Fabrication issues - focus on floor, walls, doors, ceiling.<br>- Lighting issues"],
    ["title" => "Cleaning Equipment", "desc" => "- Single use cloths / green scourers discarded after use.<br>- Mop and mop bucket in good condition.<br>- Cleaning equipment clean and stored hygienically.<br>- Separate clearly identify cleaning equipment should be used for raw and cooked food"],
    ["title" => "Records & Document Control", "desc" => "- No evidence of poor photocopying (missing text etc)<br>- Factory pens in use<br>- Records (temperatures, cleaning...) correctly filled out, in good condition, no crossings out and legible<br>- Signed off by supervisor as required"],
    ["title" => "Packaging and Product Controls", "desc" => "- No evidence of product packaging being used incorrectly (storing liquids, date coding).<br>- Separate clearly identifiable cutting boards, knives and other equipment should be used for raw and cooked foods.<br>- Clear signage to indicate which type of food is to be prepared in each area."],
    ["title" => "Chemical Controls", "desc" => "- All chemicals correctly labelled<br>- Correct chemicals in use for application<br>- Chemical stored correctly"]
];

$currentYear = date('Y');
$years_res = $conn->query("SELECT DISTINCT YEAR(rated_at) as year FROM food_safety_ratings ORDER BY year DESC");
$years = [];
while ($row = $years_res->fetch_assoc()) {
    $years[] = $row['year'];
}
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

// Fetch all canteens
$canteens = $conn->query("SELECT * FROM cantines ORDER BY name ASC");


// --- Calculate average GMP grade for each canteen for the current year ---
$canteen_grades = [];
$best_ratings = [];
$below_ratings = [];
$canteen_names = [];
$canteens_for_graph = $conn->query("SELECT id, name FROM cantines ORDER BY name ASC");
while($c = $canteens_for_graph->fetch_assoc()) {
    $cid = intval($c['id']);
    $canteen_names[$cid] = $c['name'];
    // Fetch all group codes for this canteen for the current year only
    $groups = [];
    $res = $conn->query("SELECT group_code, MIN(rated_at) as rated_at FROM food_safety_ratings WHERE cantine_id=$cid AND YEAR(rated_at)=$selectedYear GROUP BY group_code");
    while($row = $res->fetch_assoc()) {
        $groups[] = $row;
    }
    // Fetch all ratings grouped by group_code for the current year only
    $all_ratings = [];
    $res2 = $conn->query("SELECT * FROM food_safety_ratings WHERE cantine_id=$cid AND YEAR(rated_at)=$selectedYear ORDER BY rated_at DESC, group_code DESC, section_no ASC");
    while($row2 = $res2->fetch_assoc()) {
        $all_ratings[$row2['group_code']][] = $row2;
    }
    // Calculate all final grades for this canteen
    $final_grades = [];
    $latest_group = null;
    $latest_grade = null;
    $latest_group_info = null;
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
        // For best/below boxes: get latest group info
        if ($latest_group === null || strtotime($g['rated_at']) > strtotime($latest_group['rated_at'])) {
            $latest_group = $g;
            $latest_grade = $final_grade;
            $latest_group_info = [
                'canteen_name' => $c['name'],
                'group_code' => $group_code,
                'rated_at' => $g['rated_at'],
                'final_grade' => $final_grade
            ];
        }
    }
    // Calculate average grade for each section title for the selected year
    $section_averages = [];
    foreach ($sections as $section) {
        $title = $section['title'];
        $grades = [];
        foreach ($groups as $g) {
            $group_code = $g['group_code'];
            $ratings = $all_ratings[$group_code] ?? [];
            foreach ($ratings as $s) {
                // Use section_title as per your DB screenshot
                if (isset($s['section_title']) && $s['section_title'] == $title && isset($s['rating']) && $s['rating'] >= 1 && $s['rating'] <= 5) {
                    $grades[] = ($s['rating'] * 20); // Each rating is out of 100
                }
            }
        }
        $section_averages[$title] = count($grades) > 0 ? round(array_sum($grades) / count($grades), 1) : 0;
    }

    // Average grade for this canteen
    $avg_grade = count($final_grades) > 0 ? round(array_sum($final_grades) / count($final_grades), 2) : 0;
    $canteen_grades[] = [
        'name' => $c['name'],
        'avg_grade' => $avg_grade,
        'section_averages' => $section_averages
    ];
    // Fetch note for latest group_code if available
    if ($latest_group_info && $latest_group_info['group_code']) {
        $note = '';
        $note_res = $conn->query("SELECT note FROM food_safety_category_code WHERE group_code='".$conn->real_escape_string($latest_group_info['group_code'])."' LIMIT 1");
        if ($note_res && $note_row = $note_res->fetch_assoc()) {
            $note = $note_row['note'];
        }
        $latest_group_info['note'] = $note;
        // Assign to best or below
        if ($latest_group_info['final_grade'] >= 80) {
            $best_ratings[] = $latest_group_info;
        } else if ($latest_group_info['final_grade'] > 0) {
            $below_ratings[] = $latest_group_info;
        }
    }
}
$sections = [
    ["title" => "Equipment Hygiene", "desc" => "- Utensils (e.g knives)/Tables/Sinks<br>- Fridges/Freezers/Chilled-Hot Display Cabinets/Vending Machines<br>- Microwaves"],
    ["title" => "Environmental Hygiene", "desc" => "- Floor areas should be clean and not slippery under foot<br>- Evidence of 'clean as you go' to keep area hygienic<br>- Racking / Cupboards clean"],
    ["title" => "Personal Hygiene", "desc" => "- No personal possessions or jewellery.<br>- Blue plasters only.<br>- No eating or drinking permitted (exception water).<br>- Changing room and toilet should be suitable and maintained in a sound condition."],
    ["title" => "Clothing and PPE Requirements", "desc" => "- Uniform worn correctly<br>- PPE available - Oven gloves, chain gloves...<br>- Hairnets must be correctly worn by all, ears must be covered.<br>- Safety boots worn by all."],
    ["title" => "Foreign Body Controls", "desc" => "- Evidence of broken glass / brittle plastic<br>- Loose nuts and bolts found<br>- Pest control issues<br>- Miscellaneous foreign bodies in area - ripped cardboard, wood, broken equipment, peeling labels, cable ties etc."],
    ["title" => "Engineering & Fabrication", "desc" => "- Temporary fixes including any 'selotape' engineering.<br>- Damage to the equipment including rust and flaking paint<br>- Fabrication issues - focus on floor, walls, doors, ceiling.<br>- Lighting issues"],
    ["title" => "Cleaning Equipment", "desc" => "- Single use cloths / green scourers discarded after use.<br>- Mop and mop bucket in good condition.<br>- Cleaning equipment clean and stored hygienically.<br>- Separate clearly identify cleaning equipment should be used for raw and cooked food"],
    ["title" => "Records & Document Control", "desc" => "- No evidence of poor photocopying (missing text etc)<br>- Factory pens in use<br>- Records (temperatures, cleaning...) correctly filled out, in good condition, no crossings out and legible<br>- Signed off by supervisor as required"],
    ["title" => "Packaging and Product Controls", "desc" => "- No evidence of product packaging being used incorrectly (storing liquids, date coding).<br>- Separate clearly identifiable cutting boards, knives and other equipment should be used for raw and cooked foods.<br>- Clear signage to indicate which type of food is to be prepared in each area."],
    ["title" => "Chemical Controls", "desc" => "- All chemicals correctly labelled<br>- Correct chemicals in use for application<br>- Chemical stored correctly"]
];

// Calculate average grade for each section title for the selected year
$section_averages = [];
foreach ($sections as $section) {
    $title = $section['title'];
    $grades = [];
    foreach ($groups as $g) {
        $group_code = $g['group_code'];
        $ratings = $all_ratings[$group_code] ?? [];
        foreach ($ratings as $s) {
            // Use section_title as per your DB screenshot
            if (isset($s['section_title']) && $s['section_title'] == $title && isset($s['rating']) && $s['rating'] >= 1 && $s['rating'] <= 5) {
                $grades[] = ($s['rating'] * 20); // Each rating is out of 100
            }
        }
    }
    $section_averages[$title] = count($grades) > 0 ? round(array_sum($grades) / count($grades), 1) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Safety Ratings</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gmp-modal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center; }
        .gmp-modal-content { background:#fff; border-radius:10px; max-width:800px; width:98vw; max-height:90vh; overflow:auto; padding:24px; position:relative; }
        .gmp-close-modal { position:absolute; top:10px; right:18px; font-size:1.6rem; cursor:pointer; color:#888; }
        .rating-btn { padding: 6px 12px; margin: 0 2px; border-radius: 6px; border: 1px solid #bbb; background: #f8f8f8; cursor: pointer; }
        .rating-btn.active, .rating-btn:focus { background: #1976d2; color: #fff; border-color: #1976d2; }
        .section-box { border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 18px; padding: 12px 18px; background: #fcfcfc; }
        .section-title { font-weight: 600; margin-bottom: 6px; }
        .evidence-input { width: 100%; padding: 6px; border-radius: 6px; border: 1px solid #ccc; margin-top: 6px; }
        .save-btn { background: #27ae60; color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: 1.1em; cursor: pointer; margin-top: 18px; }
        .view-img { max-width:80px; max-height:80px; border-radius:6px; }
        .action-btn { background:#3498db; color:#fff; border:none; border-radius:6px; padding:6px 16px; cursor:pointer; margin-right:4px; }
        .action-btn.view { background:#27ae60; }
        .action-btn.add { background:#1976d2; }
        .canteen-table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        .canteen-table th, .canteen-table td { border:1px solid #e0e0e0; padding:8px 10px; }
        .canteen-table th { background:#f7f7f7; }
        .canteen-graph-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto 32px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px #1976d210;
            padding: 24px 18px 18px 18px;
        }
        @media print {
    .no-print {
        display: none !important;
    }
}
    </style>
</head>
<body>
    <?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<h2>Food Safety Ratings</h2>
<!-- GMP Grades Graph -->

<div style="display:flex;gap:18px;align-items:flex-start;justify-content:center;">
    <!-- Best (>=80%) box -->
    <div style="min-width:260px;max-width:320px;background:#eafbe7;border-radius:14px;box-shadow:0 2px 12px #1976d210;padding:18px 14px 14px 14px;">
        <div style="font-weight:bold;font-size:1.1em;color:#388e3c;margin-bottom:8px;">Best Ratings (≥80%)</div>
        <?php
        // Sort best_ratings by final_grade DESC, then take top 3
        usort($best_ratings, function($a, $b) { return $b['final_grade'] <=> $a['final_grade']; });
        $top_best = array_slice($best_ratings, 0, 3);
        ?>
        <?php if (count($top_best) === 0): ?>
            <div style="color:#888;">No canteen with ≥80% rating yet.</div>
        <?php else: ?>
            <?php foreach ($top_best as $r): ?>
                <div style="margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #d0e6d6;">
                    <div style="font-weight:bold;font-size:1.05em;">Canteen: <?= htmlspecialchars($r['canteen_name']) ?></div>
                    <div>Code: <b><?= htmlspecialchars($r['group_code']) ?></b></div>
                    <div>Date: <?= date('F j, Y', strtotime($r['rated_at'])) ?></div>
                    <div>Note: <span style="color:#1976d2;"><?= htmlspecialchars($r['note']) ?></span></div>
                    <div style="font-weight:bold;color:#388e3c;">GMP: <?= $r['final_grade'] ?>%</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- Graph -->
    <div class="canteen-graph-container">
        <div style="font-size:1.2em;font-weight:bold;margin-bottom:10px;">
            Total GMP this <?= $currentYear ?>
        </div>
        <canvas id="canteenGmpGraph" height="110"></canvas>
    </div>
    <!-- Below 80% box -->
    <div style="min-width:260px;max-width:320px;background:#fff3e0;border-radius:14px;box-shadow:0 2px 12px #1976d210;padding:18px 14px 14px 14px;">
        <div style="font-weight:bold;font-size:1.1em;color:#d32f2f;margin-bottom:8px;">Below 80% Ratings</div>
        <?php
        // Sort below_ratings by final_grade ASC, then take top 3
        usort($below_ratings, function($a, $b) { return $a['final_grade'] <=> $b['final_grade']; });
        $top_below = array_slice($below_ratings, 0, 3);
        ?>
        <?php if (count($top_below) === 0): ?>
            <div style="color:#888;">No canteen below 80% rating.</div>
        <?php else: ?>
            <?php foreach ($top_below as $r): ?>
                <div style="margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f7c59f;">
                    <div style="font-weight:bold;font-size:1.05em;">Canteen: <?= htmlspecialchars($r['canteen_name']) ?></div>
                    <div>Code: <b><?= htmlspecialchars($r['group_code']) ?></b></div>
                    <div>Date: <?= date('F j, Y', strtotime($r['rated_at'])) ?></div>
                    <div>Note: <span style="color:#1976d2;"><?= htmlspecialchars($r['note']) ?></span></div>
                    <div style="font-weight:bold;color:#d32f2f;">GMP: <?= $r['final_grade'] ?>%</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<div style="display:flex;flex-wrap:wrap;gap:18px 14px;margin-bottom:24px;">
    <?php
    $canteenIdx = 0;
    mysqli_data_seek($canteens, 0);
    while($c = $canteens->fetch_assoc()): ?>
        <div style="
            background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
            border-radius: 18px;
            box-shadow: 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
            padding: 22px 18px 18px 18px;
            min-width: 260px;
            max-width: 320px;
            flex: 1 1 260px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
        ">
            <div style="font-size:1.18em;font-weight:bold;color:#1976d2;margin-bottom:2px;">
                <?= htmlspecialchars($c['name']) ?>
            </div>
            <div style="font-size:1em;color:#444;">
                <span><strong>Stall No:</strong> <?= htmlspecialchars($c['stall_no']) ?></span><br>
                <span><strong>Owner:</strong> <?= htmlspecialchars($c['owner']) ?></span><br>
                <span><strong>Email:</strong> <?= htmlspecialchars($c['email']) ?></span><br>
                <span><strong>Phone:</strong> <?= htmlspecialchars($c['phone']) ?></span>
            </div>
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button class="action-btn add" onclick="openAddModal(<?= $c['id'] ?>)">Add GMP Rating</button>
                <button class="action-btn view" onclick="openViewModal(<?= $c['id'] ?>)">View</button>
                <button class="action-btn view" onclick="openSectionGmpModal(<?= $canteenIdx ?>, '<?= htmlspecialchars($c['name']) ?>')">View Title GMP Rating</button>
            </div>
        </div>
    <?php $canteenIdx++; endwhile; ?>
</div>

<!-- Add GMP Rating Modal -->
<div class="gmp-modal" id="addModal">
    <div class="gmp-modal-content">
        <span class="gmp-close-modal" onclick="closeAddModal()">&times;</span>
        <h3>Add GMP Rating</h3>
        <form id="addRatingForm" enctype="multipart/form-data">
            <input type="hidden" name="cantine_id" id="modalCantineId">
            <div class="section-box">
                <label for="overallNote"><b>Note Name for Overall GMP Rating:</b></label>
                <input type="text" name="overall_note" id="overallNote" class="evidence-input" placeholder="Enter note name for this GMP rating" required>
            </div>
            <?php foreach($sections as $i => $section): ?>
                <div class="section-box">
                    <div class="section-title"><?= ($i+1) ?>. <?= htmlspecialchars($section['title']) ?>:</div>
                    <div style="margin-bottom:8px; color:#444; font-size:0.98em;">
                        <?= $section['desc'] ?>
                    </div>
                    <div style="margin-bottom:8px;">
                        <span>Rate:</span>
                        <span class="rating-group" data-section="<?= $i ?>">
                            <?php for($r=1;$r<=5;$r++): ?>
                                <button type="button" class="rating-btn" id="rating-<?= $i ?>-<?= $r ?>"
                                    onclick="selectRating(<?= $i ?>,<?= $r ?>)"><?= $r ?> = <?= ['Very poor','Poor','Improve','Good','Excellent'][$r-1] ?></button>
                            <?php endfor; ?>
                        </span>
                        <input type="hidden" name="rating[]" id="input-rating-<?= $i ?>" required>
                    </div>
                    <div>
                        <label>Section <?= ($i+1) ?>: photo evidence / notes here:</label><br>
                        <input type="text" name="evidence[]" class="evidence-input" placeholder="Notes">
                        <input type="file" name="img[]" accept="image/*" style="margin-top:6px;">
                    </div>
                    <input type="hidden" name="section[]" value="<?= htmlspecialchars($section['title']) ?>">
                </div>
            <?php endforeach; ?>
            <button type="submit" class="save-btn">Save Ratings</button>
        </form>
    </div>
</div>

<!-- View Ratings Modal -->
<div class="gmp-modal" id="viewModal">
    <div class="gmp-modal-content" id="viewModalContent">
        <!-- Content loaded by JS -->
    </div>
</div>

<div id="sectionGmpModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:400px;width:98vw;max-height:95vh;overflow-y:auto;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:28px 18px 18px 18px;position:relative;">
        <span onclick="closeSectionGmpModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="sectionGmpModalContent"></div>
    </div>
</div>

<form method="get" style="margin-bottom:18px;">
    <label for="yearSelect" style="font-weight:600;">Select Year:</label>
    <select name="year" id="yearSelect" onchange="this.form.submit()" style="margin-left:8px;padding:4px 10px;border-radius:6px;">
        <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
    </select>
</form>

<script>
    // Modal logic
    function openAddModal(cantineId) {
        document.getElementById('modalCantineId').value = cantineId;
        document.getElementById('addModal').style.display = 'flex';
    }
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('addRatingForm').reset();
        document.querySelectorAll('.rating-btn.active').forEach(btn => btn.classList.remove('active'));
    }
    function openViewModal(cantineId) {
        var year = document.getElementById('yearSelect').value;
        fetch('fetch_gmp_ratings.php?cantine_id=' + cantineId + '&year=' + year)
            .then(res => res.text())
            .then(html => {
                document.getElementById('viewModalContent').innerHTML = html;
                document.getElementById('viewModal').style.display = 'flex';
            });
    }
    function closeViewModal() {
        document.getElementById('viewModal').style.display = 'none';
    }
    function selectRating(sectionIdx, rating) {
        document.querySelectorAll('.rating-group[data-section="'+sectionIdx+'"] .rating-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('rating-'+sectionIdx+'-'+rating).classList.add('active');
        document.getElementById('input-rating-'+sectionIdx).value = rating;
    }
    document.getElementById('addRatingForm').onsubmit = function(e) {
        e.preventDefault();
        // Ensure all 10 ratings are selected
        var ratings = document.querySelectorAll('input[name="rating[]"]');
        var missing = [];
        ratings.forEach(function(input, idx) {
            if (!input.value || isNaN(parseInt(input.value))) {
                missing.push(idx + 1);
            }
        });
        if (missing.length > 0) {
            alert('Please select a rating for all 10 sections. Missing: ' + missing.join(", "));
            return;
        }
        var formData = new FormData(this);
        fetch('save_gmp_rating.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('Ratings saved! Group Code: ' + data.group_code);
                closeAddModal();
                location.reload();
            } else {
                alert('Failed to save ratings.');
            }
        });
    };
    window.onclick = function(event) {
        if (event.target.classList && event.target.classList.contains('gmp-modal')) {
            event.target.style.display = 'none';
        }
    }
    function printGmpReport(groupId, groupCode, date) {
        var groupContent = document.getElementById(groupId).innerHTML;
        var contractImg = '<img src="PATH_TO_YOUR_CONTRACT_IMAGE.png" style="max-width:100%;margin-bottom:18px;">';
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
        printWindow.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;} th{background:#1976d2;color:#fff;} img{max-width:180px;}</style>');
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
            if (arrow) arrow.innerHTML = '&#9650;';
        } else {
            content.style.display = 'none';
            if (arrow) arrow.innerHTML = '&#9660;';
        }
    }

    // Data from PHP (make these global)
    const canteenLabels = <?php echo json_encode(array_column($canteen_grades, 'name')); ?>;
    const canteenGrades = <?php echo json_encode(array_column($canteen_grades, 'avg_grade')); ?>;
    const canteenSectionAverages = <?php echo json_encode(array_column($canteen_grades, 'section_averages')); ?>;
    const sectionTitles = <?php echo json_encode(array_column($sections, 'title')); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        // Draw Chart.js bar graph
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
                        title: { display: true, text: 'Canteen' }
                    }
                },
                animation: {
                    duration: 800
                }
            },
        });
    });

    function openSectionGmpModal(idx, canteenName) {
        const averages = canteenSectionAverages[idx];
        let html = `<div style="font-weight:bold;font-size:1.1em;margin-bottom:8px;">${canteenName} - Title GMP Ratings (<?= $selectedYear ?>)</div>`;
        html += '<table style="width:100%;font-size:0.98em;border-collapse:collapse;">';
        html += '<tr><th style="text-align:left;">Title</th><th style="text-align:right;">Average Grade</th></tr>';
        sectionTitles.forEach(title => {
            html += `<tr>
                <td>${title}</td>
                <td style="text-align:right;font-weight:600;color:#1976d2;">${averages[title] !== undefined ? averages[title] + '%' : '-'}</td>
            </tr>`;
        });
        html += '</table>';
        document.getElementById('sectionGmpModalContent').innerHTML = html;
        document.getElementById('sectionGmpModal').style.display = 'flex';
    }
    function closeSectionGmpModal() {
        document.getElementById('sectionGmpModal').style.display = 'none';
    }
</script>
</body>
</html>