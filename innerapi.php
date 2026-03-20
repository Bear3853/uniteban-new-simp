<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

$serversFile = "servers.json";
$bansFile = "bans.json";

if (!file_exists($serversFile)) {
    file_put_contents($serversFile, json_encode([]));
}
if (!file_exists($bansFile)) {
    file_put_contents($bansFile, json_encode([]));
}

$allowedServers = json_decode(file_get_contents($serversFile), true) ?: [];

function isServerAllowed($mac, $token, $list) {
    foreach ($list as $item) {
        if (($item['mac'] ?? '') === $mac) {
            if (isset($item['token_hash']) && password_verify($token, $item['token_hash'])) {
                return true;
            }
        }
    }
    return false;
}

// 服务器心跳
if (isset($data['mac']) && isset($data['token']) && !isset($data['name']) && !isset($data['reason'])) {
    if (!isServerAllowed($data['mac'], $data['token'], $allowedServers)) {
        echo json_encode(["status" => "error", "message" => "服务器未授权"]);
        exit;
    }

    foreach ($allowedServers as &$s) {
        if ($s['mac'] === $data['mac']) {
            $s['last_update'] = date('Y-m-d H:i:s');
            break;
        }
    }

    $ret = file_put_contents($serversFile, json_encode($allowedServers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($ret === false) {
        echo json_encode(["status" => "error", "message" => "服务器文件写入失败"]);
        exit;
    }

    echo json_encode(["status" => "success"]);
    exit;
}

// 上传封禁
if (isset($data['name']) && isset($data['reason']) && isset($data['mac']) && isset($data['token'])) {
    if (!isServerAllowed($data['mac'], $data['token'], $allowedServers)) {
        echo json_encode(["status" => "error", "message" => "服务器未授权"]);
        exit;
    }

    $bans = json_decode(file_get_contents($bansFile), true) ?: [];

    $newBan = [
        "name" => $data["name"] ?? "",
        "xuid" => $data["xuid"] ?? "",
        "uuid" => $data["uuid"] ?? "",
        "clientid" => $data["clientid"] ?? "",
        "ip" => $data["ip"] ?? "",
        "mac" => $data["mac"] ?? "",
        "reason" => $data["reason"] ?? "",
        "time" => date('Y-m-d H:i:s')
    ];

    $exist = false;
    foreach ($bans as $b) {
        if (
            (!empty($b['uuid']) && !empty($newBan['uuid']) && $b['uuid'] === $newBan['uuid']) ||
            (!empty($b['xuid']) && !empty($newBan['xuid']) && $b['xuid'] === $newBan['xuid']) ||
            (!empty($b['name']) && !empty($newBan['name']) && $b['name'] === $newBan['name']) ||
            (!empty($b['clientid']) && !empty($newBan['clientid']) && $b['clientid'] === $newBan['clientid']) ||
            (!empty($b['ip']) && !empty($newBan['ip']) && $b['ip'] === $newBan['ip'])
        ) {
            $exist = true;
            break;
        }
    }

    if (!$exist) {
        $bans[] = $newBan;
        $ret = file_put_contents($bansFile, json_encode($bans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($ret === false) {
            echo json_encode(["status" => "error", "message" => "封禁数据写入失败，请检查文件权限"]);
            exit;
        }
    }

    echo json_encode(["status" => "success"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "参数错误"]);
?>
