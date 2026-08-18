<?php
// ============================================================
//  INDEX.PHP - MULTI ID CONFIG FORM
//  5-6 IDs ka data leke session mein save karega
// ============================================================

// Render pe session path fix
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

// Save form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = [];
    for ($i = 1; $i <= 6; $i++) {
        $name = trim($_POST['name_' . $i] ?? '');
        $token = trim($_POST['token_' . $i] ?? '');
        if (!empty($name) && !empty($token)) {
            $ids[$name] = $token;
        }
    }
    
    $_SESSION['multi_ids'] = $ids;
    $_SESSION['channelId'] = trim($_POST['channelId'] ?? '');
    $_SESSION['xSuperProperties'] = trim($_POST['xSuperProperties'] ?? '');
    $_SESSION['installationId'] = trim($_POST['installationId'] ?? '');
    
    // Redirect to multi_sender.php
    header('Location: multi_sender.php');
    exit;
}

$ids = $_SESSION['multi_ids'] ?? [];
$channelId = $_SESSION['channelId'] ?? '';
$xSuper = $_SESSION['xSuperProperties'] ?? '';
$installId = $_SESSION['installationId'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Multi ID Config</title>
<style>
    * { box-sizing: border-box; }
    body { 
        background: #1e1f22; color: #dbdee1; font-family: Arial; 
        padding: 20px; display: flex; justify-content: center; 
        min-height: 100vh;
    }
    .container { max-width: 700px; width: 100%; }
    .box { 
        background: #2b2d31; padding: 30px; border-radius: 16px; 
        width: 100%;
    }
    h1 { color: #57f287; font-size: 22px; margin-top: 0; }
    label { display: block; font-size: 13px; color: #b5bac1; margin: 12px 0 4px; }
    input, textarea { 
        width: 100%; padding: 10px; border-radius: 8px; 
        background: #111214; border: 1px solid #3f4147; 
        color: #dbdee1; font-size: 14px;
    }
    textarea { resize: vertical; min-height: 50px; }
    .id-row { 
        display: flex; gap: 10px; margin-bottom: 8px; 
        background: #1e1f22; padding: 10px; border-radius: 8px;
    }
    .id-row input { flex: 1; }
    .btn { 
        width: 100%; padding: 14px; margin-top: 16px; 
        background: #5865f2; border: none; border-radius: 10px; 
        color: #fff; font-size: 16px; font-weight: 700; cursor: pointer;
    }
    .btn:hover { background: #4752c4; }
    .status { color: #57f287; margin-top: 10px; }
    .sub { color: #b5bac1; font-size: 13px; margin-bottom: 16px; }
    .remove-btn { 
        background: #ed4245; border: none; color: #fff; 
        padding: 0 12px; border-radius: 6px; cursor: pointer;
        font-size: 18px;
    }
    .remove-btn:hover { background: #c0353a; }
    .add-btn {
        background: #23a55a; border: none; color: #fff;
        padding: 10px; border-radius: 8px; cursor: pointer;
        font-size: 14px; font-weight: 600; width: 100%;
    }
    .add-btn:hover { background: #1a8a47; }
</style>
</head>
<body>
<div class="container">
    <div class="box">
        <h1>⚡ Multi ID Config</h1>
        <p class="sub">5-6 IDs ka data daalo, phir multi-sender run karo.</p>

        <form method="POST" id="configForm">
            <label>Channel ID</label>
            <input name="channelId" value="<?= htmlspecialchars($channelId) ?>" placeholder="Channel ID" required>

            <label>x-super-properties</label>
            <textarea name="xSuperProperties" placeholder="Base64 x-super-properties"><?= htmlspecialchars($xSuper) ?></textarea>

            <label>Installation ID</label>
            <input name="installationId" value="<?= htmlspecialchars($installId) ?>" placeholder="Installation ID">

            <div style="margin: 16px 0 8px; display: flex; justify-content: space-between; align-items: center;">
                <label style="margin:0; font-weight:700;">IDs & Tokens</label>
            </div>

            <div id="idContainer">
                <?php 
                $count = max(1, count($ids));
                $i = 0;
                foreach ($ids as $name => $token): $i++; ?>
                <div class="id-row" data-index="<?= $i ?>">
                    <input name="name_<?= $i ?>" placeholder="Name" value="<?= htmlspecialchars($name) ?>" required>
                    <input name="token_<?= $i ?>" placeholder="Token" value="<?= htmlspecialchars($token) ?>" required>
                    <button type="button" class="remove-btn" onclick="removeRow(this)">✕</button>
                </div>
                <?php endforeach; ?>
                <?php if ($i == 0): ?>
                <div class="id-row" data-index="1">
                    <input name="name_1" placeholder="Name (e.g. beenuhacker)" required>
                    <input name="token_1" placeholder="Token" required>
                    <button type="button" class="remove-btn" onclick="removeRow(this)">✕</button>
                </div>
                <?php endif; ?>
            </div>

            <button type="button" class="add-btn" onclick="addRow()">➕ Add ID</button>

            <button type="submit" class="btn">💾 Save & Start Multi-Sender</button>
        </form>
    </div>
</div>

<script>
let idCounter = <?= max(1, count($ids)) ?>;

function addRow() {
    idCounter++;
    const container = document.getElementById('idContainer');
    const row = document.createElement('div');
    row.className = 'id-row';
    row.dataset.index = idCounter;
    row.innerHTML = `
        <input name="name_${idCounter}" placeholder="Name" required>
        <input name="token_${idCounter}" placeholder="Token" required>
        <button type="button" class="remove-btn" onclick="removeRow(this)">✕</button>
    `;
    container.appendChild(row);
}

function removeRow(btn) {
    const row = btn.parentElement;
    if (document.querySelectorAll('.id-row').length > 1) {
        row.remove();
    } else {
        alert('At least 1 ID required');
    }
}
</script>
</body>
</html>
