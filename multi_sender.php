<?php
// ============================================================
//  MULTI-SENDER - RENDER VERSION
//  Session se IDs lega (index.php se)
// ============================================================

set_time_limit(0);
ignore_user_abort(true);

if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

// 🔥 Session se config lo
$CHANNEL_ID = $_SESSION['channelId'] ?? '';
$X_SUPER_PROPERTIES = $_SESSION['xSuperProperties'] ?? '';
$INSTALLATION_ID = $_SESSION['installationId'] ?? '';
$TOKENS = $_SESSION['multi_ids'] ?? [];

if (empty($TOKENS) || empty($CHANNEL_ID)) {
    die("<h2>❌ Pehle <a href='index.php'>index.php</a> mein IDs daalo aur Save karo!</h2>");
}

$MY_IDS = array_keys($TOKENS);
$idKeys = array_keys($TOKENS);

$NAMES = [];
foreach ($TOKENS as $name => $token) {
    $NAMES[$name] = $name;
}

// 🔥 Session mein save karo for API
$_SESSION['sender_config'] = [
    'channelId' => $CHANNEL_ID,
    'xSuperProperties' => $X_SUPER_PROPERTIES,
    'installationId' => $INSTALLATION_ID,
    'tokens' => $TOKENS,
    'names' => $NAMES,
];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Multi-Sender</title>
<style>
    * { box-sizing: border-box; }
    body { 
        margin: 0; padding: 20px; 
        font-family: 'Segoe UI', system-ui, sans-serif;
        background: #1e1f22; color: #dbdee1;
        min-height: 100vh;
    }
    .container { max-width: 900px; margin: 0 auto; }
    .header { 
        background: #2b2d31; border-radius: 12px; 
        padding: 16px 20px; margin-bottom: 16px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .header h1 { margin: 0; font-size: 20px; }
    .header .badge { 
        padding: 6px 14px; border-radius: 999px; 
        font-weight: 700; font-size: 13px;
    }
    .badge-running { background: #57f287; color: #1e1f22; }
    .badge-stopped { background: #ed4245; color: #fff; }
    .badge-waiting { background: #f0b232; color: #1e1f22; }
    .log {
        background: #111214; border-radius: 12px;
        padding: 16px; height: 55vh; overflow-y: auto;
        font-family: Consolas, monospace; font-size: 13px;
        white-space: pre-wrap; margin-bottom: 16px;
        border: 1px solid #3f4147;
        line-height: 1.6;
    }
    .log .time { color: #b5bac1; }
    .log .reply { color: #57f287; }
    .log .normal { color: #f0b232; }
    .log .error { color: #ed4245; font-weight: bold; }
    .log .info { color: #5865f2; }
    .log .success { color: #57f287; font-weight: bold; }
    .log .wait { color: #f0b232; font-weight: bold; }
    .footer {
        display: flex; justify-content: space-between; align-items: center;
        background: #2b2d31; border-radius: 12px; padding: 12px 16px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .footer .stats { color: #b5bac1; font-size: 14px; }
    .footer .stats b { color: #dbdee1; }
    .btn {
        padding: 10px 24px; border: none; border-radius: 8px;
        font-weight: 700; font-size: 14px; cursor: pointer;
        color: #fff; transition: .2s;
    }
    .btn-stop { background: #ed4245; }
    .btn-stop:hover { background: #c0353a; }
    .btn-stop:disabled { background: #3f4147; cursor: not-allowed; }
    .btn-back { background: #5865f2; text-decoration: none; display: inline-block; }
    .btn-back:hover { background: #4752c4; }
    .stats-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;
    }
    .stat-card {
        background: #2b2d31; border-radius: 10px; padding: 12px 16px; text-align: center;
    }
    .stat-card .num { font-size: 24px; font-weight: 700; }
    .stat-card .lbl { font-size: 12px; color: #b5bac1; }
    .stat-card.sent .num { color: #57f287; }
    .stat-card.reply .num { color: #f0b232; }
    .stat-card.users .num { color: #5865f2; }
    .stat-card.fail .num { color: #ed4245; }
    .stat-card.waiting .num { color: #f0b232; }
    .header-info {
        display: flex; gap: 20px; flex-wrap: wrap;
        background: #111214; border-radius: 10px; padding: 10px 16px;
        margin-bottom: 16px; font-size: 13px;
    }
    .header-info .label { color: #b5bac1; }
    .header-info .value { color: #57f287; font-weight: 600; }
    .header-info .value.error { color: #ed4245; }
    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .footer { flex-direction: column; align-items: stretch; text-align: center; }
        .header-info { flex-direction: column; gap: 5px; }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔄 Multi-Sender</h1>
        <div class="badge badge-running" id="statusBadge">🟢 Running</div>
    </div>

    <div class="header-info">
        <div><span class="label">Channel:</span> <span class="value"><?= htmlspecialchars($CHANNEL_ID) ?></span></div>
        <div><span class="label">x-super-properties:</span> <span class="value <?= empty($X_SUPER_PROPERTIES) ? 'error' : '' ?>"><?= empty($X_SUPER_PROPERTIES) ? '❌ MISSING' : '✅ Loaded' ?></span></div>
        <div><span class="label">x-installation-id:</span> <span class="value <?= empty($INSTALLATION_ID) ? 'error' : '' ?>"><?= empty($INSTALLATION_ID) ? '❌ MISSING' : '✅ Loaded' ?></span></div>
        <div><span class="label">IDs:</span> <span class="value"><?= count($TOKENS) ?> loaded</span></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card sent"><div class="num" id="statSent">0</div><div class="lbl">Total Replies</div></div>
        <div class="stat-card reply"><div class="num" id="statReply">0</div><div class="lbl">Replies Sent</div></div>
        <div class="stat-card users"><div class="num" id="statUsers">0</div><div class="lbl">Users Replied</div></div>
        <div class="stat-card fail"><div class="num" id="statFail">0</div><div class="lbl">Failed</div></div>
    </div>

    <div class="log" id="log">
        <div class="info">🚀 Starting Multi-Sender...</div>
        <div class="info">📡 Channel: <?= htmlspecialchars($CHANNEL_ID) ?></div>
        <div class="info">🆔 IDs: <?= implode(', ', array_keys($TOKENS)) ?></div>
        <div class="info">⏳ Waiting for genuine users...</div>
    </div>

    <div class="footer">
        <div class="stats">
            <b id="msgCount">0</b> sent | 
            <b id="userCount">0</b> users | 
            <b id="failCount">0</b> failed |
            Status: <span id="statusText" style="color: #57f287;">Running</span>
        </div>
        <div>
            <a href="index.php" class="btn btn-back" style="color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; margin-right:10px;">⬅ Back to Config</a>
            <button class="btn btn-stop" id="stopBtn">⏹ Stop</button>
        </div>
    </div>
</div>

<script>
let totalSent = 0;
let totalReplies = 0;
let totalUsers = 0;
let totalFails = 0;
let isRunning = true;
let sentUserIds = [];
let loopRunning = false;
let isWaiting = false;

const logEl = document.getElementById('log');
const statSent = document.getElementById('statSent');
const statReply = document.getElementById('statReply');
const statUsers = document.getElementById('statUsers');
const statFail = document.getElementById('statFail');
const msgCount = document.getElementById('msgCount');
const userCount = document.getElementById('userCount');
const failCount = document.getElementById('failCount');
const statusText = document.getElementById('statusText');
const statusBadge = document.getElementById('statusBadge');
const stopBtn = document.getElementById('stopBtn');

function addLog(text, cls = '') {
    const time = new Date().toLocaleTimeString();
    const div = document.createElement('div');
    div.className = cls || 'info';
    div.innerHTML = `<span class="time">[${time}]</span> ${text}`;
    logEl.appendChild(div);
    logEl.scrollTop = logEl.scrollHeight;
}

function updateStats(sent, replies, users, fails) {
    statSent.textContent = sent;
    statReply.textContent = replies;
    statUsers.textContent = users;
    statFail.textContent = fails;
    msgCount.textContent = sent;
    userCount.textContent = users;
    failCount.textContent = fails;
}

function stopSending() {
    isRunning = false;
    loopRunning = false;
    isWaiting = false;
    statusText.textContent = 'Stopped';
    statusText.style.color = '#ed4245';
    statusBadge.textContent = '⏹ Stopped';
    statusBadge.className = 'badge badge-stopped';
    stopBtn.disabled = true;
    stopBtn.textContent = '⏹ Stopped';
    addLog('⏹ <b>STOPPED by user!</b>', 'error');
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function runLoop() {
    if (loopRunning) return;
    loopRunning = true;
    
    addLog('🔄 Starting main loop...', 'info');
    
    while (isRunning && loopRunning) {
        try {
            if (isWaiting) {
                await sleep(1000);
                continue;
            }
            
            const response = await fetch('multi_sender_api.php?_=' + Date.now());
            const data = await response.json();
            
            if (data.error) {
                addLog('❌ ' + data.error, 'error');
                await sleep(30000);
                continue;
            }
            
            if (data.success) {
                totalSent++;
                totalReplies++;
                
                if (!sentUserIds.includes(data.userName)) {
                    sentUserIds.push(data.userName);
                    totalUsers = sentUserIds.length;
                }
                
                addLog(`💬 <span class="reply">REPLY</span> from ${data.speakerName} to @${data.userName}: "${data.userMessage.substring(0, 40)}..."`, 'reply');
                addLog(`   → ${data.reply}`, 'normal');
                
                const delay = data.delay || 15;
                updateStats(totalSent, totalReplies, totalUsers, totalFails);
                addLog(`⏳ Waiting ${delay}s...`, 'info');
                
                await sleep(delay * 1000);
            } else {
                totalFails++;
                updateStats(totalSent, totalReplies, totalUsers, totalFails);
                
                if (data.wait && data.wait === 300) {
                    isWaiting = true;
                    statusBadge.textContent = '⏳ Waiting 5 min';
                    statusBadge.className = 'badge badge-waiting';
                    statusText.textContent = 'Waiting 5 min...';
                    statusText.style.color = '#f0b232';
                    
                    addLog(`⏳ <span class="wait">WAITING 5 MINUTES</span> due to: ${data.error}`, 'wait');
                    
                    let remaining = 300;
                    const countdownInterval = setInterval(() => {
                        if (!isRunning) {
                            clearInterval(countdownInterval);
                            return;
                        }
                        remaining--;
                        if (remaining <= 0) {
                            clearInterval(countdownInterval);
                            isWaiting = false;
                            statusBadge.textContent = '🟢 Running';
                            statusBadge.className = 'badge badge-running';
                            statusText.textContent = 'Running';
                            statusText.style.color = '#57f287';
                            addLog('✅ <span class="success">Wait over! Resuming...</span>', 'success');
                        } else if (remaining % 10 === 0) {
                            addLog(`⏳ Still waiting... ${remaining}s remaining`, 'wait');
                        }
                    }, 1000);
                    
                    await sleep(300000);
                    
                    if (isWaiting) {
                        isWaiting = false;
                        statusBadge.textContent = '🟢 Running';
                        statusBadge.className = 'badge badge-running';
                        statusText.textContent = 'Running';
                        statusText.style.color = '#57f287';
                        addLog('✅ <span class="success">Wait over! Resuming...</span>', 'success');
                    }
                } else {
                    addLog(`❌ ${data.error || 'Failed to send reply'}`, 'error');
                    await sleep(30000);
                }
            }
            
        } catch (e) {
            totalFails++;
            updateStats(totalSent, totalReplies, totalUsers, totalFails);
            addLog('❌ Error: ' + e.message, 'error');
            await sleep(5000);
        }
    }
    
    loopRunning = false;
}

stopBtn.addEventListener('click', stopSending);
runLoop();
</script>
</body>
</html>
