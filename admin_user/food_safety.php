<?php
require_once 'db.php';
session_start();

// Handle add/update obligation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_obligation'])) {
        $obligation = $conn->real_escape_string($_POST['obligation']);
        $date_added = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO obligation_category (obligation, date_added, del_status) VALUES ('$obligation', '$date_added', 0)");
        header("Location: food_safety.php");
        exit;
    }
    if (isset($_POST['update_id'])) {
        $id = intval($_POST['update_id']);
        $obligation = $conn->real_escape_string($_POST['update_obligation']);
        $conn->query("UPDATE obligation_category SET obligation='$obligation' WHERE id=$id");
        header("Location: food_safety.php");
        exit;
    }
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $conn->query("UPDATE obligation_category SET del_status=1 WHERE id=$id");
        header("Location: food_safety.php");
        exit;
    }
}

// Fetch obligations
$obligations = $conn->query("SELECT * FROM obligation_category WHERE del_status=0 ORDER BY date_added DESC");

// Fetch canteens for selection
$canteens = $conn->query("SELECT id, name FROM cantines WHERE active=0 AND del_status=0");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Obligations Status</title>
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <style>
        .modal-bg {
            display: none;
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3); z-index: 1000;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff; padding: 20px; border-radius: 6px; min-width: 320px; max-width: 95vw;
            box-shadow: 0 2px 8px #0002;
        }
        .modal-header { font-weight: bold; margin-bottom: 10px; }
        .modal-footer { margin-top: 10px; text-align: right; }
        .btn { padding: 6px 14px; border: 1px solid #888; background: #eee; cursor: pointer; border-radius: 4px; }
        .btn-primary { background: #1976d2; color: #fff; border: none; }
        .btn-danger { background: #d32f2f; color: #fff; border: none; }
        .btn-secondary { background: #888; color: #fff; border: none; }
        .btn-sm { font-size: 0.9em; padding: 4px 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px;}
        th, td { border: 1px solid #aaa; padding: 6px 10px; }
        th { background: #f0f0f0; }
        .update-form { display: none; }
        .canteen-box {
            flex: 0 0 260px;
            cursor: pointer;
            background: #f8f8f8;
            border: 2px solid #1976d2;
            border-radius: 10px;
            padding: 18px 14px 14px 14px;
            text-align: left;
            box-shadow: 0 2px 8px #0001;
            font-weight: normal;
            transition: box-shadow 0.2s, border 0.2s;
            margin-bottom: 12px;
            margin-right: 8px;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .canteen-box:hover {
            box-shadow: 0 4px 16px #1976d233;
            border-color: #004ba0;
        }
        .canteen-rating-row span {
            font-size: 1em;
            margin-right: 6px;
        }
        .canteen-dot {
            display:inline-block;
            width:12px;
            height:12px;
            border-radius:50%;
            margin-right:6px;
            vertical-align:middle;
        }
        .canteen-dot.active { background:#388e3c; }
        .canteen-dot.deactive { background:#d32f2f; }
        .canteen-tooltip {
            visibility:hidden;
            opacity:0;
            position:absolute;
            left:50%;
            top:100%;
            transform:translateX(-50%);
            background:#333;
            color:#fff;
            padding:10px 16px;
            border-radius:8px;
            box-shadow:0 2px 8px #0003;
            min-width:180px;
            z-index:10;
            font-size:0.98em;
            text-align:left;
            transition:opacity 0.2s;
            margin-top:8px;
            white-space:pre-line;
        }
        .canteen-box:hover .canteen-tooltip {
            visibility:visible;
            opacity:1;
        }
        /* Modern Canteen Box Styles */
        .modern-canteen-box {
            background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px #1976d220, 0 1.5px 4px #1976d210;
            padding: 22px 18px 18px 18px;
            margin-bottom: 18px;
            margin-right: 10px;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
            position: relative;
        }
        .modern-canteen-box:hover {
            box-shadow: 0 8px 32px #1976d240, 0 2px 8px #1976d220;
            transform: translateY(-2px) scale(1.02);
        }
        .canteen-title {
            font-size: 1.25em;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 2px;
        }
        .canteen-meta {
            font-size: 1em;
            color: #444;
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 6px;
        }
        .rating-label {
            font-weight: 500;
            color: #333;
            margin-top: 8px;
            margin-bottom: 2px;
            font-size: 1.05em;
        }
        .rating-date {
            color: #1976d2;
            font-size: 0.98em;
            font-weight: normal;
        }
        .modern-rating-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 1em;
            margin-bottom: 4px;
            color: #333;
        }
        .modern-rating-row span {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 2px 10px;
            font-weight: 500;
        }
        .canteen-compliance {
            margin: 6px 0 0 0;
            font-size: 1.04em;
        }
        .canteen-billing {
            margin: 2px 0 0 0;
            font-size: 1.04em;
        }
    </style>
</head>
<body style="padding: 24px;">
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<div>
    <h2>Obligations Status</h2>
    <button class="btn btn-primary" onclick="showModal('viewModal')">View Obligations</button>
    <br><br>

    <!-- View Obligations Modal -->
    <div class="modal-bg" id="viewModal">
      <div class="modal-content">
        <div class="modal-header">Obligations List</div>
        <!-- Add Obligation Inline Form -->
        <form method="post" style="margin-bottom:10px;display:flex;gap:8px;align-items:center;">
            <input type="text" name="obligation" placeholder="New obligation..." required style="flex:1;">
            <button type="submit" name="add_obligation" class="btn btn-primary btn-sm">Add</button>
        </form>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Obligation</th>
              <th>Date Added</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($obligations as $ob): ?>
            <tr>
              <td><?= $ob['id'] ?></td>
              <td>
                <span id="obligation-text-<?= $ob['id'] ?>" style="display:inline;"><?= htmlspecialchars($ob['obligation']) ?></span>
                <form method="post" class="update-form" id="update-form-<?= $ob['id'] ?>" style="margin:0;display:none;">
                    <input type="hidden" name="update_id" value="<?= $ob['id'] ?>">
                    <input type="text" name="update_obligation" value="<?= htmlspecialchars($ob['obligation']) ?>" required style="width:140px;">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelUpdate(<?= $ob['id'] ?>)">Cancel</button>
                </form>
              </td>
              <td><?= $ob['date_added'] ?></td>
              <td>
                <button class="btn btn-primary btn-sm" onclick="showUpdate(<?= $ob['id'] ?>)">Update</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this obligation?');">
                  <input type="hidden" name="delete_id" value="<?= $ob['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="hideModal('viewModal')">Close</button>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <?php
        $total_obligations = $conn->query("SELECT COUNT(*) as cnt FROM obligation_category WHERE del_status=0")->fetch_assoc()['cnt'];
        $canteen_query = $conn->query("SELECT id, name, stall_no, owner, active, del_status FROM cantines WHERE del_status=0");
        foreach ($canteen_query as $c):
            $canteen_id = $c['id'];
            $complied = 0;

            // Get latest ACTIVE obligations row for this canteen (status=1)
            $ob_row = $conn->query("SELECT id, obligation_and_status FROM obligations WHERE cantine_id=$canteen_id AND status=1 ORDER BY id DESC LIMIT 1")->fetch_assoc();
            if ($ob_row && $ob_row['obligation_and_status']) {
                $ob_array = json_decode($ob_row['obligation_and_status'], true);
                if (is_array($ob_array)) {
                    foreach ($ob_array as $ob) {
                        if (isset($ob['status']) && $ob['status'] === 'Complied') $complied++;
                    }
                }
            }

            // Get all inspection IDs for this canteen (for ratings)
            $inspection_ids = [];
            $inspection_res = $conn->query("SELECT id FROM obligations WHERE cantine_id=$canteen_id ORDER BY id DESC");
            while ($row = $inspection_res->fetch_assoc()) {
                $inspection_ids[] = $row['id'];
            }
            $rating = null;
            if ($inspection_ids) {
                $ids_str = implode(',', $inspection_ids);
                $rating = $conn->query("SELECT food_quality, food_safety, hygiene, service_quality, date_rated FROM canteen_inspection_ratings WHERE inspection_id IN ($ids_str) ORDER BY date_rated DESC, id DESC LIMIT 1")->fetch_assoc();
            }

            // Get the most future bill date for this canteen in the current year
            $bill = $conn->query("SELECT MAX(date) as max_date FROM bills WHERE cantine_id=$canteen_id AND YEAR(date) = YEAR(CURDATE())")->fetch_assoc();
            $bill_status = "No Payment";
            if ($bill && $bill['max_date']) {
                $bill_month = date('Y-m', strtotime($bill['max_date']));
                $now_month = date('Y-m');
                $next_month = date('Y-m', strtotime('+1 month'));
                if ($bill_month == $now_month) {
                    $bill_status = "On-time";
                } elseif ($bill_month > $now_month) {
                    $bill_status = "Advance Payment";
                } elseif ($bill_month < $now_month) {
                    $bill_status = "Not On-time";
                }
            }

            // Format latest rating date
            $latest_rating_date = $rating && isset($rating['date_rated']) && $rating['date_rated']
                ? date('F d, Y', strtotime($rating['date_rated']))
                : 'N/A';

            // Compliance color
            $compliance_color = ($total_obligations && $complied == $total_obligations) ? '#43a047' : '#d32f2f';

        ?>
            <div class="canteen-box modern-canteen-box" onclick="window.location.href='edit_canteen_obligations.php?cantine_id=<?= $canteen_id ?>'">
                <div class="canteen-title"><?= htmlspecialchars($c['name']) ?></div>
                <div class="canteen-meta">
                    <span><strong>Stall No:</strong> <?= htmlspecialchars($c['stall_no']) ?></span>
                    <span><strong>Owner:</strong> <?= htmlspecialchars($c['owner']) ?></span>
                </div>
                <?php if ($rating): ?>
                    <div class="rating-label">Latest Rating <span class="rating-date">(<?= $latest_rating_date ?>)</span>:</div>
                    <div class="canteen-rating-row modern-rating-row">
                        <span title="Food Quality"><b>Food Quality:</b> <?= intval($rating['food_quality']) ?>⭐</span>
                        <span title="Food Safety"><b>Food Safety:</b> <?= intval($rating['food_safety']) ?>⭐</span>
                        <span title="Hygiene"><b>Hygiene:</b> <?= intval($rating['hygiene']) ?>⭐</span>
                        <span title="Service Quality"><b>Service Quality:</b> <?= intval($rating['service_quality']) ?>⭐</span>
                    </div>
                <?php else: ?>
                    <div class="rating-label">Latest Rating: <span style="color:#d32f2f;">No rating yet</span></div>
                <?php endif; ?>
                <div class="canteen-compliance">
                    <span title="Obligations Complied">
                        Compliance: <span style="color:<?= $compliance_color ?>;font-weight:bold;">
                            <?= $total_obligations ? "$complied/$total_obligations" : '-' ?>
                        </span>
                    </span>
                </div>
                <div class="canteen-billing">
                    <span title="Billing Status">Billing: <b><?= $bill_status ?></b></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}
function hideModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    // Hide all update forms if modal is closed
    document.querySelectorAll('.update-form').forEach(f=>f.style.display='none');
    document.querySelectorAll('[id^="obligation-text-"]').forEach(s=>s.style.display='inline');
}
function showUpdate(id) {
    document.getElementById('update-form-' + id).style.display = 'inline';
    document.getElementById('obligation-text-' + id).style.display = 'none';
}
function cancelUpdate(id) {
    document.getElementById('update-form-' + id).style.display = 'none';
    document.getElementById('obligation-text-' + id).style.display = 'inline';
}
// Hide modal when clicking outside content
document.querySelectorAll('.modal-bg').forEach(function(bg){
    bg.addEventListener('click', function(e){
        if(e.target === bg) hideModal(bg.id);
    });
});
function canteenClick(id, deactive) {
    if (deactive) {
        if (!confirm('Warning: This canteen is deactive. Do you want to continue?')) return;
    }
    window.location.href = 'edit_canteen_obligations.php?cantine_id=' + id;
}
</script>
</body>
</html>