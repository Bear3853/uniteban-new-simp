<?php
$appealsFile = "appeals.json";
if (!file_exists($appealsFile)) file_put_contents($appealsFile, json_encode([]));

if($_POST){
    $name = $_POST['name'];
    $reason = $_POST['reason'];
    $contact = $_POST['contact'];
    $appeal = [
        'name'=>$name,
        'reason'=>$reason,
        'contact'=>$contact,
        'time'=>date('Y-m-d H:i:s'),
        'status'=>'待处理'
    ];
    $appeals = json_decode(file_get_contents($appealsFile), true) ?? [];
    $appeals[] = $appeal;
    file_put_contents($appealsFile, json_encode($appeals, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo "<script>alert('申诉提交成功！');location.href='index.php'</script>";
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>申诉解封</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI}
body{background:#121212;color:#e0e0e0;padding:30px}
.container{max-width:600px;margin:0 auto}
.card{background:#1e1e1e;padding:25px;border-radius:12px}
input,textarea{width:100%;padding:12px;margin:10px 0;background:#2a2a2a;border:none;border-radius:8px;color:#fff}
button{padding:12px 20px;background:#426bff;color:#fff;border:none;border-radius:8px;width:100%}
</style>
</head>
<body>
<div class="container">
<div class="card">
<h1>📩 解封申诉</h1>
<form method="post">
<input name="name" placeholder="游戏ID" required>
<input name="contact" placeholder="联系方式(QQ/微信)" required>
<textarea name="reason" rows="5" placeholder="申诉说明" required></textarea>
<button>提交申诉</button>
</form>
</div>
</div>
</body>
</html>
