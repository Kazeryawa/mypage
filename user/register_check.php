<?php
include("../libraries/libraries.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/Exception.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/PHPMailer.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $reg_email = $_GET["email"];
    $reg_password = $_GET['password'];
    $reg_key = $_GET['key'];
    $reg_token = $_GET["token"];
    
    if ($reg_email == "" || $reg_password == "" || $reg_key == "" || $reg_token == "") {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>您没有向此页面提交完整的参数，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
        exit;
    }
    
    if (!checkStringFormat($reg_email) || !checkStringFormat($reg_password) || !checkStringFormat($reg_key) || !checkStringFormat($reg_token)) {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>您向此页面提交的参数不符合安全规定，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
        exit;
    }
    
    $sql = get_sql();
    $result = $sql->query("SELECT * FROM email_checking WHERE email = '$reg_email'");
    if($result->num_rows != 1) {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>我们没有收到过有关您提交的邮箱'.$reg_email.'的任何注册请求</p>
</body>
</html>');
        exit;
    }
    
    $row = $result->fetch_assoc();
    if (time() - strtotime($row["time"]) <= 300) {
        if ($row["card_key"] == $reg_key && $row["password"] == $reg_password && $row["token"] == $reg_token) {
            if ($row["ip"] == $ip) {
                echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册成功</title>
</head>
<body>
<h1>恭喜您！您的splish账号注册成功</h1>
<p>您现在可以使用该账号登录了</p>
</body>
</html>');
                $sql->query("INSERT INTO users (email, reg_key, reg_ip, reg_time, password, latest_login_ip, latest_login_time, latest_password_change_time, latest_password_reset_time, request_token, latest_request_time) VALUES ('$reg_email', '$reg_key', '$ip', NOW(), '$reg_password', '', 0, 0, 0, '', 0)");
                $sql->query("DELETE FROM email_checking WHERE email = '$reg_email'");
                
                $mail = new PHPMailer(true);
                try {
                    $mail->CharSet ="UTF-8";
                    //$mail->isSMTP();
                    $mail->Host = 'mail.splish.zone';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@splish.zone';
                    $mail->Password = '8tyPTj?]YDD8p.2';
                    $mail->Port = 25;
                
                    $mail->setFrom('noreply@splish.zone', 'EMailChecker');
                    $mail->addAddress($reg_email);
                
                    $mail->isHTML(true);
                    $mail->Subject = '您的splish账号注册成功';
                    $mail->Body = '<!DOCTYPE html>
<html>
<body>
  <h1>恭喜您！您的splish账号注册成功</h1>
  <p>您现在可以使用该账号登录了</p>
</body>
</html>
';
                    $mail->send();
                    } catch (Exception $e) {
                
                    }
                    
                    exit;
            } else {
                echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>您注册时的IP地址与验证时的IP地址不相同，请确保您没有在注册或验证时使用网络代理程序</p>
</body>
</html>');
                exit;
            }
        } else {
            echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>您向此地址提交的信息可能被篡改，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
            exit;
        }
    } else {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号注册失败</title>
</head>
<body>
<h1>抱歉，您的splish账号注册失败</h1>
<p>该验证邮件已经超时，请重新输入您的邮箱和key进行注册，在一封新的验证邮件发出后，请在5分钟内访问邮箱中的验证地址进行验证</p>
</body>
</html>');
        exit;
    }
    
    
    
}