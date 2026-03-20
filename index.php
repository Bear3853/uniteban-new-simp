<?php
$bansFile = "bans.json";
$serversFile = "servers.json";

if (!file_exists($bansFile)) file_put_contents($bansFile, json_encode([]));
if (!file_exists($serversFile)) file_put_contents($serversFile, json_encode([]));

$bans = json_decode(file_get_contents($bansFile), true) ?? [];
$servers = json_decode(file_get_contents($serversFile), true) ?? [];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>CloudBan 联合封禁系统</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI,Roboto,sans-serif}
body{background:#121212;color:#e0e0e0;padding:20px}
.container{max-width:1200px;margin:0 auto}
.card{background:#1e1e1e;border-radius:12px;padding:20px;margin-bottom:20px}
h1{font-size:22px;margin-bottom:15px;color:#fff}
.table{width:100%;border-collapse:collapse}
th,td{padding:12px;text-align:left;border-bottom:1px solid #333}
th{color:#7cb3ff}
.btn{display:inline-block;padding:10px 16px;background:#426bff;color:#fff;border-radius:8px;text-decoration:none;margin-top:10px}
.text-green{color:#50fa7b}
</style>
</head>
<body>
<div class="container">

<div class="card">
    <h1>🖥️ 已接入服务器</h1>
    <table class="table">
        <tr><th>服务器名称</th></tr>
        <?php foreach($servers as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['name'] ?? '未知服务器') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h1>🚫 已封禁玩家</h1>
    <table class="table">
        <tr><th>玩家名</th><th>封禁原因</th><th>时间</th></tr>
        <?php foreach($bans as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['reason'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['time'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="appeal.php" class="btn">我要申诉</a>
</div>

</div>
</body>
</html>
