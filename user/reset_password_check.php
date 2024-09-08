<?php
include("../libraries/libraries.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/Exception.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/PHPMailer.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/SMTP.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $reset_email = $_GET["email"];
    $reset_old_password_twice_hash_check = $_GET["old_password_twice_hash"];
    
    if ($reset_email == "" || $reset_old_password_twice_hash_check == "") {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置失败</title>
</head>
<body>
<h1>抱歉，您的splish账号密码重置失败</h1>
<p>您没有向此页面提交完整的参数，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
        exit;
    }
    
    if (!checkStringFormat($reset_email) || !checkStringFormat($reset_old_password_twice_hash_check)) {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置失败</title>
</head>
<body>
<h1>抱歉，您的splish账号密码重置失败</h1>
<p>您向此页面提交的参数不符合安全规定，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
        exit;
    }
    
    $sql = get_sql();
    $result = $sql->query("SELECT * FROM users WHERE email = '$reset_email'");
    if($result->num_rows != 1) {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置失败</title>
</head>
<body>
<h1>抱歉，您的splish账号密码重置失败</h1>
<p>您的邮箱'.$reg_email.'还未注册splish账号</p>
</body>
</html>');
        exit;
    }
    
    $row = $result->fetch_assoc();
    
    $change_time = strtotime($row["latest_password_reset_time"]);
    
    if(time() - $change_time <= 300) {
        $password_twice_hash = hash("sha512",$row["password"]."dfgvsdfsed4wtetf5e5twg54tybrybte5etsrrgvtrhbyrteber5ertb54yeb45e654y56b5656nytgrtghbtryhbetyerbytretyreyrteytrneytrn".strval($change_time));
        if($password_twice_hash == $reset_old_password_twice_hash_check){
            $new_password = hash("sha512",strval(rand(10000,100000)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval($change_time));
            $new_password_hash = hash("sha512",$new_password);
            $sql->query("UPDATE users SET password = '$new_password_hash' WHERE email = '$reset_email'");
            
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
                    $mail->addAddress($reset_email);
                
                    $mail->isHTML(true);
                    $mail->Subject = '您的splish账号密码重置成功';
                    $mail->Body = '<!DOCTYPE html>
<html>
<body>
  <h1>恭喜，您的splish账号密码重置成功</h1>
  <p>您现在可以新的密码登录或修改密码了</p>
</body>
</html>
';
                    $mail->send();
                    } catch (Exception $e) {
                
                    }
            
            
            echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置成功</title>
</head>
<body>
<h1>恭喜，您的splish账号密码重置成功</h1>
<p>您的新splish密码如下</p>
<p>'.$new_password.'</p>
<p>该密码仅适用于'.$reset_email.'的splish账号</p>
<p>您可以使用此密码修改自己的splish账号的密码</p>
<p>在您修改splish账号的密码之前，请勿将此密码告诉他人</p>
</body>
</html>');
            exit;
            
        } else {
            echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置失败</title>
</head>
<body>
<h1>抱歉，您的splish账号密码重置失败</h1>
<p>您向此地址提交的信息可能被篡改，请确保您访问的是邮箱中收到的完整的验证地址</p>
</body>
</html>');
            exit;
        }
        
        
    } else {
        echo('<!DOCTYPE html>
<html>
<head>
<title>splish账号密码重置失败</title>
</head>
<body>
<h1>抱歉，您的splish账号密码重置失败</h1>
<p>该重置邮件已经超时，请重新输入您的邮箱进行重置，在一封新的重置邮件发出后，请在5分钟内访问邮箱中的验证地址进行重置</p>
</body>
</html>');
        exit;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}