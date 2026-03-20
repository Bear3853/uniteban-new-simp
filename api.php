<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

$bansFile = "bans.json";
if (!file_exists($bansFile)) {
    file_put_contents($bansFile, json_encode([]));
}
$bans = json_decode(file_get_contents($bansFile), true) ?: [];

$name = $data['name'] ?? '';
$xuid = $data['xuid'] ?? '';
$uuid = $data['uuid'] ?? '';
$clientid = $data['clientid'] ?? '';
$ip = $data['ip'] ?? '';

foreach ($bans as $item) {
    $match = false;
    if (!empty($name) && isset($item['name']) && $item['name'] === $name) $match = true;
    if (!$match && !empty($xuid) && isset($item['xuid']) && $item['xuid'] === $xuid) $match = true;
    if (!$match && !empty($uuid) && isset($item['uuid']) && $item['uuid'] === $uuid) $match = true;
    if (!$match && !empty($clientid) && isset($item['clientid']) && $item['clientid'] === $clientid) $match = true;
    if (!$match && !empty($ip) && isset($item['ip']) && $item['ip'] === $ip) $match = true;

    if ($match) {
        echo json_encode([
            'exists' => true,
            'reason' => $item['reason'] ?? '违规封禁'
        ]);
        exit;
    }
}

echo json_encode(['exists' => false]);
?>
