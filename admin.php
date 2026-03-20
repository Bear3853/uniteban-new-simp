<?php
session_start();
$password = "123456";

$bansFile = "bans.json";
$serversFile = "servers.json";
$appealsFile = "appeals.json";

if (!file_exists($bansFile)) file_put_contents($bansFile, json_encode([]));
if (!file_exists($serversFile)) file_put_contents($serversFile, json_encode([]));
if (!file_exists($appealsFile)) file_put_contents($appealsFile, json_encode([]));

if (!isset($_SESSION['login'])) {
    if (isset($_POST['pwd'])) {
        if ($_POST['pwd'] === $password) $_SESSION['login'] = true;
        else $err = "密码错误";
    }
}

if (isset($_GET['unban']) && isset($_SESSION['login'])) {
    $bans = json_decode(file_get_contents($bansFile), true) ?? [];
    array_splice($bans, (int)$_GET['unban'], 1);
    file_put_contents($bansFile, json_encode($bans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

if (isset($_GET['delserver']) && isset($_SESSION['login'])) {
    $servers = json_decode(file_get_contents($serversFile), true) ?? [];
    array_splice($servers, (int)$_GET['delserver'], 1);
    file_put_contents($serversFile, json_encode($servers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

if (isset($_POST['add_server']) && isset($_SESSION['login'])) {
    $name = trim($_POST['name']);
    $mac = trim($_POST['mac']);
    $token = trim($_POST['token']);
    
    if (!empty($name) && !empty($mac) && !empty($token)) {
        $servers = json_decode(file_get_contents($serversFile), true) ?? [];
        $servers[] = [
            "name" => $name,
            "mac" => $mac,
            "token_hash" => password_hash($token, PASSWORD_DEFAULT),
            "last_update" => date('Y-m-d H:i:s')
        ];
        file_put_contents($serversFile, json_encode($servers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: admin.php");
        exit;
    }
}

if (isset($_GET['handle']) && isset($_SESSION['login'])) {
    $apps = json_decode(file_get_contents($appealsFile), true) ?? [];
    if (isset($apps[(int)$_GET['handle']])) {
        $apps[(int)$_GET['handle']]['status'] = "已处理";
        file_put_contents($appealsFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    header("Location: admin.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>CloudBan 管理后台</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI,Roboto,sans-serif}
body{background:#121212;color:#e0e0e0;padding:20px}
.container{max-width:1200px;margin:0 auto}
.login{max-width:400px;margin:50px auto;background:#1e1e1e;padding:30px;border-radius:12px}
input,button{width:100%;padding:12px;margin:10px 0;border-radius:8px;border:none;background:#2a2a2a;color:#fff}
button{background:#426bff;cursor:pointer}
.card{background:#1e1e1e;padding:20px;border-radius:12px;margin-bottom:20px}
h1{font-size:20px;margin-bottom:15px;color:#fff}
.table{width:100%;border-collapse:collapse}
th,td{padding:12px;text-align:left;border-bottom:1px solid #333;word-break:break-all}
th{color:#7cb3ff}
a{color:#426bff}
.danger{color:#ff4d4d}
.success{color:#50fa7b}
.add-form{display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap}
</style>
</head>
<body>
<div class="container">

<?php if(!isset($_SESSION['login'])): ?>
<div class="login">
    <h1>管理员登录</h1>
    <?php if(isset($err)): ?>
        <p style="color:#ff4d4d"><?= $err ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="pwd" placeholder="请输入密码" required>
        <button>登录</button>
    </form>
</div>
<?php else: ?>

<div class="card">
    <h1>🖥️ 已授权服务器</h1>
    <div class="add-form">
        <form method="post" class="add-form">
            <input name="name" placeholder="服务器名称" required>
            <input name="mac" placeholder="MAC 地址" required>
            <input name="token" placeholder="Token（添加后无法查看，请自行保存）" required>
            <button name="add_server">添加</button>
        </form>
    </div>
    <table class="table">
        <tr>
            <th>ID</th>
            <th>服务器名称</th>
            <th>MAC 地址</th>
            <th>Token 状态</th>
            <th>最后在线</th>
            <th>操作</th>
        </tr>
        <?php
        $servers = json_decode(file_get_contents($serversFile), true) ?? [];
        foreach($servers as $i => $s):
        ?>
        <tr>
            <td><?= $i ?></td>
            <td><?= htmlspecialchars($s['name'] ?? '未知') ?></td>
            <td><?= htmlspecialchars($s['mac'] ?? '') ?></td>
            <td>已加密（不可逆）</td>
            <td><?= htmlspecialchars($s['last_update'] ?? '') ?></td>
            <td><a href="?delserver=<?= $i ?>" class="danger">删除</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h1>🚫 封禁玩家列表 <a href="?logout">退出登录</a></h1>
    <table class="table">
        <tr>
            <th>ID</th>
            <th>玩家名</th>
            <th>XUID</th>
            <th>UUID</th>
            <th>IP</th>
            <th>封禁原因</th>
            <th>时间</th>
            <th>操作</th>
        </tr>
        <?php
        $bans = json_decode(file_get_contents($bansFile), true) ?? [];
        foreach($bans as $i => $b):
        ?>
        <tr>
            <td><?= $i ?></td>
            <td><?= htmlspecialchars($b['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['xuid'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['uuid'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['ip'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['reason'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['time'] ?? '') ?></td>
            <td><a href="?unban=<?= $i ?>" class="danger">解封</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h1>📩 玩家申诉</h1>
    <table class="table">
        <tr>
            <th>玩家ID</th>
            <th>联系方式</th>
            <th>申诉内容</th>
            <th>时间</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        <?php
        $appeals = json_decode(file_get_contents($appealsFile), true) ?? [];
        foreach($appeals as $i => $a):
        ?>
        <tr>
            <td><?= htmlspecialchars($a['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['contact'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['reason'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['time'] ?? '') ?></td>
            <td class="<?= $a['status'] == '已处理' ? 'success' : '' ?>">
                <?= $a['status'] ?? '待处理' ?>
            </td>
            <td><a href="?handle=<?= $i ?>">设为已处理</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php endif; ?>
</div>
</body>
</html>
