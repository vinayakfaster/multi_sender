<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

$cfg = $_SESSION['sender_config'] ?? null;

if (!$cfg) {
    echo json_encode(['error' => 'Config not found.']);
    exit;
}

$CHANNEL_ID = $cfg['channelId'] ?? '';
$INSTALLATION_ID = $cfg['installationId'] ?? '';
$TOKENS = $cfg['tokens'] ?? [];
$X_SUPER_MAP = $cfg['xSuperMap'] ?? [];
$NAMES = $cfg['names'] ?? [];

if (empty($TOKENS) || empty($CHANNEL_ID)) {
    echo json_encode(['error' => 'Missing config.']);
    exit;
}

$MY_IDS = array_keys($TOKENS);
$idKeys = array_keys($TOKENS);

// ============================================================
//  🔥 SMART FUNCTIONS
// ============================================================

function pick($arr) {
    return $arr[array_rand($arr)];
}

function getRandomDelay() {
    $base = rand(8, 20);
    if (rand(1, 10) == 1) $base += rand(15, 45);
    if (rand(1, 20) == 1) $base += rand(30, 40);
    return $base;
}

function simulateTyping() {
    $typingTime = rand(2, 8);
    if (rand(1, 3) == 1) $typingTime = rand(1, 4);
    if (rand(1, 5) == 1) $typingTime = rand(5, 12);
    sleep($typingTime);
}

// 🔥 HAR ID KA APNA X-SUPER-PROPERTIES USE HOTA HAI
function buildHeaders($token, $channelId, $xSuperProperties, $installationId) {
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
    ];
    
    $secChUa = [
        '"Not=A?Brand";v="99", "Chromium";v="130", "Google Chrome";v="130"',
        '"Not=A?Brand";v="99", "Chromium";v="129", "Google Chrome";v="129"',
    ];
    
    return [
        'Accept: */*',
        'Accept-Encoding: */*',
        'Accept-Language: en-US,en;q=0.9',
        'Authorization: ' . $token,
        'Content-Type: application/json',
        'Origin: https://discord.com',
        'Referer: https://discord.com/channels/' . $channelId . '/',
        'Sec-Ch-Ua: ' . $secChUa[array_rand($secChUa)],
        'Sec-Ch-Ua-Mobile: ?0',
        'Sec-Ch-Ua-Platform: "Windows"',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'User-Agent: ' . $userAgents[array_rand($userAgents)],
        'X-Context-Properties: eyJsb2NhdGlvbiI6ImNoYXRfaW5wdXQifQ==',
        'X-Debug-Options: bugReporterEnabled',
        'X-Discord-Locale: en-US',
        'X-Discord-Timezone: America/New_York',
        'x-super-properties: ' . $xSuperProperties,  // 🔥 HAR ID KA ALAG
        'x-installation-id: ' . $installationId,
    ];
}

function getChannelMessages($token, $channelId, $xSuperProperties, $installationId, $limit = 30) {
    $url = "https://discord.com/api/v9/channels/{$channelId}/messages?limit={$limit}";
    $headers = buildHeaders($token, $channelId, $xSuperProperties, $installationId);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    return [];
}

function findGenuineUsers($messages, $myIds) {
    $genuineMessages = [];
    if (!is_array($messages)) return $genuineMessages;
    
    foreach ($messages as $msg) {
        if (!isset($msg['author'])) continue;
        
        $authorId = $msg['author']['id'] ?? '';
        $authorName = $msg['author']['username'] ?? 'Unknown';
        $content = $msg['content'] ?? '';
        
        if (empty($content) || strpos($content, '!') === 0 || strpos($content, '/') === 0) {
            continue;
        }
        if (in_array($authorId, $myIds)) {
            continue;
        }
        $genuineMessages[] = [
            'id' => $authorId,
            'name' => $authorName,
            'message_id' => $msg['id'],
            'content' => $content,
        ];
    }
    return $genuineMessages;
}

function generateReply($userMessage, $userName) {
    $userMessageLower = strtolower($userMessage);
    
    $questionWords = ['?', 'how', 'what', 'why', 'when', 'who', 'which', 'is', 'are', 'do', 'does', 'can', 'will'];
    $isQuestion = false;
    foreach ($questionWords as $word) {
        if (strpos($userMessageLower, $word) !== false) {
            $isQuestion = true;
            break;
        }
    }
    
    $replies = [];
    
    if ($isQuestion) {
        $replies = [
            "Bhai @$userName, good quesn! Platform actually solid h.",
            "Arey @$userName, ladder system thoda confusng h pehle but samaj aa jayga.",
            "Dekho @$userName, withdrawal min ab $50 h.",
            "Mujhe bhi same doubt tha @$userName, but founders actually sunte h community ki.",
            "Baat toh shi h @$userName, CRSH me crypto, sports, sab h.",
            "Mne bhi same socha tha @$userName, referral program kafi generous h.",
            "Thnx @$userName! UI bhot clean h navigate krna easy h.",
            "Acha sawaal h @$userName! Community actually bhot helpful h.",
            "Shi pakda @$userName! Platform constntly improve ho rha h.",
            "Bhai @$userName, pehle mujhe bhi confusion thi, but ab samaj aya.",
            "Arre @$userName, main bhi CRSH use kr rha hu weeks se, acha h.",
            "@$userName yaar, mera bhi yahi quesn tha!",
            "Mujhe bhi pura sure nhi h @$userName, but I think aise hi kaam krta h.",
            "Check krta hu @$userName, min $50 h shayd ab.",
            "Hn bhai @$userName, shi kha! Platform actually solid h.",
            "@$userName shi baat h, community kafi helpful h.",
            "Thnx yaar @$userName!",
            "Mujhe bhi aisa hi lgta h @$userName, utility overlooked h.",
            "Shi h @$userName, future bright lg rha h.",
            "Same feelng @$userName, team dedicated h.",
            "Intrestng @$userName, CRSH mainstream ho rha h dheere dheere.",
            "Samaj gya @$userName, rewards actually worth it h.",
            "Point toh shi h @$userName!",
            "Acha observatn h @$userName, main bhi bullish hu long-term.",
            "Support krta hu @$userName, founders actually sunte h.",
            "Chinta mt kar @$userName, time k sath sab samaj aa jayga.",
            "Keep going @$userName, jitna predict kroge utna better hoge.",
            "Yahi spirit chahiye @$userName, CRSH bhot bda banega.",
            "Acha kr rhe ho @$userName, community h help krne k liye.",
            "Positive rho @$userName, future promisng h.",
            "Hey @$userName! CRSH actually solid h ngl.",
            "Yo @$userName! Ladder system try kiya?",
            "Kya haal h @$userName! Live prediction addctv h.",
            "Hi @$userName! Mne months se use kr rha hu CRSH.",
            "Waah @$userName! Mne bhi friends ko bta rha hu CRSH k bare me.",
            "Mujhe bhi pasnd aaya @$userName, platform user-friendly h.",
            "Acha lga sunke @$userName, team continuously improve kr rhi h.",
            "Fantastc @$userName! Shi choice h.",
            "Khushi hui sunke @$userName, community hi heart h CRSH ki.",
            "Bhai @$userName, shi pakda 🔥 platform actually solid h!",
            "Arey @$userName, ladder system thoda confusng h but worth it h 💪",
            "Dekh @$userName, withdrawal min $50 h ab 🤑",
            "Mera bhi yahi quesn tha @$userName, but founders sunte h 🙌",
            "Shi h @$userName, CRSH me sab kuch h! 🚀",
            "Mne bhi yahi socha @$userName, referral program 🔥 h",
            "Thnx @$userName! UI bhot clean h ✨",
            "Acha sawaal @$userName! Community 🤝 bhot helpful h.",
            "Shi pakda @$userName! Platform 📈 improve ho rha h.",
            "Bhai @$userName, pehle mujhe bhi confusion thi 😅",
            "Arre @$userName, main weeks se use kr rha hu 💯",
            "@$userName yaar, mera bhi yahi sawaal tha 🤔",
            "Mujhe bhi pura sure nhi @$userName, but aise hi kaam krta h shayd 🤷",
            "Check krta hu @$userName, min $50 h lagta h ✅",
            "Hn @$userName, shi kha! 💯",
            "@$userName shi baat h 👍",
            "Thnx yaar @$userName 🙏",
            "Mujhe bhi aisa lgta h @$userName 💪",
            "Shi h @$userName 🔥",
            "Same feelng @$userName ✨",
            "Intrestng @$userName 🤔",
            "Samaj gya @$userName ✅",
            "Shi pakda @$userName 🎯",
            "Acha observatn @$userName 👀",
            "Support @$userName 🤝",
            "Chinta mt kar @$userName 💪",
            "Keep going @$userName 🚀",
            "Yahi spirit @$userName 🔥",
            "Acha kr rhe ho @$userName 👏",
            "Positive rho @$userName 🌟",
            "Hey @$userName! 👋",
            "Yo @$userName! 🙌",
            "Kya haal @$userName! 😎",
            "Hi @$userName! ✌️",
            "Waah @$userName! 🔥",
            "Pasnd aaya @$userName ❤️",
            "Acha lga @$userName 😊",
            "Fantastc @$userName! 🎉",
            "Khushi hui @$userName 🤗",
        ];
    }
    
    if (empty($replies) || rand(1, 3) == 1) {
        $shortReplies = [
            "Shi kha @$userName! CRSH actually solid h.",
            "Shi baat @$userName, community helpful h.",
            "Thnx yaar @$userName!",
            "Mujhe bhi aisa lgta h @$userName, utility overlooked h.",
            "Shi h @$userName, future bright lg rha h.",
            "Same feelng @$userName, team dedicated h.",
            "Intrestng @$userName, CRSH mainstream ho rha h.",
            "Samaj gya @$userName, rewards worth it h.",
            "Point shi h @$userName!",
            "Acha observatn @$userName, main bullish hu.",
            "Support @$userName, founders sunte h.",
            "Chinta mt kar @$userName, time k sath samaj aa jayga.",
            "Keep going @$userName, better hoge.",
            "Yahi spirit @$userName, CRSH bda banega.",
            "Acha kr rhe ho @$userName, community h help k liye.",
            "Positive rho @$userName, future promisng h.",
            "Shi kha @$userName! 💯",
            "Shi baat @$userName 👍",
            "Thnx yaar @$userName 🙏",
            "Mujhe bhi aisa lgta h @$userName 💪",
            "Shi h @$userName 🔥",
            "Same feelng @$userName ✨",
            "Intrestng @$userName 🤔",
            "Samaj gya @$userName ✅",
            "Shi pakda @$userName 🎯",
            "Acha observatn @$userName 👀",
            "Support @$userName 🤝",
            "Chinta mt kar @$userName 💪",
            "Keep going @$userName 🚀",
            "Yahi spirit @$userName 🔥",
            "Acha kr rhe ho @$userName 👏",
            "Positive rho @$userName 🌟",
            "Hey @$userName! CRSH actually solid h ngl.",
            "Yo @$userName! Ladder system try kiya?",
            "Kya haal @$userName! Live prediction addctv h.",
            "Hi @$userName! Months se use kr rha hu.",
            "Waah @$userName! Friends ko bhi bta rha hu.",
            "Pasnd aaya @$userName, user-friendly h.",
            "Acha lga @$userName, team improve kr rhi h.",
            "Fantastc @$userName! Shi choice.",
            "Khushi hui @$userName, community heart h CRSH ki.",
            "Shi kha bhai @$userName 💯",
            "Sahi baat h @$userName 👍",
            "Thnx yaar @$userName 🙏",
            "Mujhe bhi aisa lgta h 💪",
            "Bilkul shi @$userName 🔥",
            "Same here @$userName ✨",
            "Intrestng point @$userName 🤔",
            "Samaj gya bhai ✅",
            "Shi pakda @$userName 🎯",
            "Acha observation h 👀",
            "Support krta hu @$userName 🤝",
            "Chinta mt kar bhai 💪",
            "Keep going yaar 🚀",
            "Yahi toh baat h 🔥",
            "Acha kr rhe ho 👏",
            "Positive rho bhai 🌟",
        ];
        $replies = array_merge($replies, $shortReplies);
    }
    
    return pick($replies);
}

function sendDiscordMessage($token, $channelId, $xSuperProperties, $installationId, $message, $replyTo = null) {
    $url = "https://discord.com/api/v9/channels/{$channelId}/messages";
    
    $payload = ['content' => $message];
    if ($replyTo) {
        $payload['message_reference'] = ['message_id' => $replyTo];
    }
    
    $headers = buildHeaders($token, $channelId, $xSuperProperties, $installationId);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'code' => $httpCode];
    }
    
    $errorMsg = '';
    if ($httpCode === 401) $errorMsg = 'Token expired. Please refresh.';
    elseif ($httpCode === 403) $errorMsg = 'Permission denied.';
    elseif ($httpCode === 404) $errorMsg = 'Channel not found.';
    elseif ($httpCode === 429) $errorMsg = 'Rate limited. Waiting...';
    else $errorMsg = "HTTP $httpCode";
    
    return ['success' => false, 'error' => $errorMsg, 'code' => $httpCode];
}

// ============================================================
//  MAIN EXECUTION
// ============================================================

if (!isset($_SESSION['speaker_index'])) {
    $_SESSION['speaker_index'] = 0;
}

$speakerIndex = $_SESSION['speaker_index'] % count($idKeys);
$speakerId = $idKeys[$speakerIndex];
$_SESSION['speaker_index']++;

// 🔥 HAR ID KA APNA TOKEN AUR X-SUPER-PROPERTIES
$token = $TOKENS[$speakerId];
$xSuperProperties = $X_SUPER_MAP[$speakerId] ?? '';
$name = $NAMES[$speakerId] ?? $speakerId;

// Agar kisi ID ka xSuper nahi hai toh error
if (empty($xSuperProperties)) {
    echo json_encode(['error' => "x-super-properties missing for $speakerId"]);
    exit;
}

simulateTyping();

$messages = getChannelMessages($token, $CHANNEL_ID, $xSuperProperties, $INSTALLATION_ID, 30);
$users = findGenuineUsers($messages, $MY_IDS);

if (empty($messages)) {
    echo json_encode(['error' => 'No messages fetched. Check token/permissions.']);
    exit;
}

if (empty($users)) {
    $totalMessages = count($messages);
    $myMessages = 0;
    foreach ($messages as $msg) {
        if (in_array($msg['author']['id'], $MY_IDS)) {
            $myMessages++;
        }
    }
    
    echo json_encode([
        'error' => 'No genuine users found',
        'debug' => ['total' => $totalMessages, 'my' => $myMessages, 'genuine' => $totalMessages - $myMessages]
    ]);
    exit;
}

$target = $users[array_rand($users)];
$replyTo = $target['message_id'];
$userName = $target['name'];
$userMessage = $target['content'];

$readTime = rand(4, 12);
sleep($readTime);

$reply = generateReply($userMessage, $userName);
simulateTyping();

$result = sendDiscordMessage($token, $CHANNEL_ID, $xSuperProperties, $INSTALLATION_ID, $reply, $replyTo);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'userName' => $userName,
        'userMessage' => $userMessage,
        'speakerName' => $name,
        'delay' => getRandomDelay()
    ]);
} else {
    $isRateLimit = ($result['code'] === 429);
    $isFatal = in_array($result['code'], [401, 403, 404]);
    
    if ($isRateLimit || $isFatal) {
        echo json_encode([
            'success' => false,
            'error' => $result['error'],
            'userName' => $userName,
            'wait' => 600,
            'isFatal' => $isFatal
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'],
            'userName' => $userName,
            'wait' => 60
        ]);
    }
}
?>
