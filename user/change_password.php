<?php
include("../libraries/libraries.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/Exception.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/PHPMailer.php';
require '/www/wwwroot/splish.js.mcdds.cn/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = file_get_contents("php://input");
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $json = json_decode($data);
    if($json === null) {
        echo(json_encode(array(
            "code" => -1,
            "msg" => "提交信息不正确，无法解析"
            )));
    } else {
        $change_email = $json->email;
        $change_old_password_hash = $json->password_hash;
        $change_new_password = $json->new_password;
        
        if ($change_email == "" || $change_new_password == "" || $change_old_password_hash == "") {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息完整无误"
            )));
            exit;
        }
        if (!checkStringFormat($change_email) || !checkStringFormat($change_new_password) || !checkStringFormat($change_old_password_hash)) {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息中只存在字母、数字、下划线、减号、@和英文句号"
            )));
            exit;
        }
        
        $sql = get_sql();
        if ($sql->connect_error) {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "服务器内部发生错误"
            )));
            exit;
        }
        
        $result = $sql->query("SELECT * FROM users WHERE email = '$change_email'");
        if ($result->num_rows != 1){
            echo(json_encode(array(
            "code" => -2,
            "msg" => "该邮箱暂未注册splish账号"
            )));
            exit;
        }
        
        $row = $result->fetch_assoc();
        
        if ($change_old_password_hash != $row['password']) {
            echo(json_encode(array(
            "code" => -2,
            "msg" => "密码输入错误"
            )));
            exit;
        }
        
        if (time() - strtotime($row["latest_password_change_time"]) <= 300) {
            echo(json_encode(array(
            "code" => -2,
            "msg" => "您修改密码的速度过快，至少在上次修改密码后的5分钟后才能再次修改密码"
            )));
            exit;
        }
        
        $new_password_hash = hash("sha512",$change_new_password);
        $sql->query("UPDATE users SET password = '$new_password_hash', latest_password_change_time = NOW() WHERE email = '$change_email'");
        
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
            $mail->addAddress($change_email);
                
            $mail->isHTML(true);
            $mail->Subject = '您的splish账号密码成功修改';
            $mail->Body = '<!DOCTYPE html>
<html>
<body>
  <h1>恭喜您！您的splish账号密码成功修改</h1>
  <p>您现在可以使用新密码登录了</p>
  <p>请注意，如果您没有进行此操作，可能是您的splish密码已经泄露，请您尽快重置您的splish密码</p>
</body>
</html>
';
            $mail->send();
            } catch (Exception $e) {
                
            }
        
        echo(json_encode(array(
            "code" => 1,
            "msg" => "密码修改成功，您现在可以使用新密码登录了"
        )));

    }
}