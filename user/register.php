<?php
include("../libraries/libraries.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'C:/wwwroot/120.27.134.77/PHPMailer/src/Exception.php';
require 'C:/wwwroot/120.27.134.77/PHPMailer/src/PHPMailer.php';
require 'C:/wwwroot/120.27.134.77/PHPMailer/src/SMTP.php';


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
        $reg_email = $json->email;
        $reg_password = $json->password;
        $reg_key = $json->key;
        
        if ($reg_key == "" || $reg_password == "" || $reg_email == "") {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息完整无误"
            )));
            exit;
        }
        
        if (!checkStringFormat($reg_email) || !checkStringFormat($reg_password) || !checkStringFormat($reg_key)) {
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
        $reg_email = strtolower($reg_email);
            if (!preg_match('/^[a-z0-9_-]+@(qq\.com|163\.com)$/i',$reg_email)){
                echo(json_encode(array(
                "code" => -2,
                "msg" => "邮箱错误，仅支持QQ邮箱和163网易免费邮"
                )));
                exit;
            }
        
        $result = $sql->query("SELECT * FROM mykeys WHERE card_key = '$reg_key'");
        if ($result->num_rows == 1){
            
            
            
            
            
            
            $result = $sql->query("SELECT * FROM email_checking WHERE email = '$reg_email'");
            if ($result->num_rows == 1) {
                
                echo(json_encode(array(
                    "code" => -2,
                    "msg" => "该邮箱已经提交过一次注册申请，但是未从邮箱里进行验证。请输入该邮箱第一次申请时的卡密以重新发送邮件"
                    )));
                exit;


            }
            
            $result = $sql->query("SELECT * FROM users WHERE email = '$reg_email'");
            if ($result->num_rows == 1) {
                
                echo(json_encode(array(
                    "code" => -2,
                    "msg" => "该邮箱已经拥有splish账号，请直接登录"
                    )));
                exit;


            }
            $sql->query("DELETE FROM mykeys WHERE card_key = '$reg_key'");
            
            $random_token = hash("sha512",$ip.$reg_key.$reg_email.strval(time()).strval(rand(100000,999999)).strval(rand(100000,999999)));
            $password_hash = hash("sha512",$reg_password);
            $sql->query("INSERT INTO email_checking (email, time, ip, card_key, token, password) VALUES ('$reg_email', NOW(), '$ip', '$reg_key', '$random_token', '$password_hash')");
            
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
                $mail->Subject = 'splish账号注册确认邮件';
                $mail->Body = '<!DOCTYPE html>
<html>
<body>
  <h1>恭喜您！</h1>
  <p>您现在离成功注册splish账号只差一步了，点击下方的链接即可完成注册！</p>
  <a href="https://splish.js.mcdds.cn/user/register_check.php?email='.$reg_email.'&password='.$password_hash.'&key='.$reg_key.'&token='.$random_token.'">https://splish.js.mcdds.cn/user/register_check.php?email='.$reg_email.'&password='.$password_hash.'&key='.$reg_key.'&token='.$random_token.'</a>
</body>
</html>
';
                $mail->send();
            } catch (Exception $e) {
                
            }
            echo(json_encode(array(
            "code" => 1,
            "msg" => "一封验证邮件已经发送到了您的邮箱".$reg_email."中，请前往查收并验证。"
            )));
            exit;
            
            
        } else {
            $result = $sql->query("SELECT * FROM email_checking WHERE email = '$reg_email' AND card_key = '$reg_key'");
            if ($result->num_rows == 1){
                $row = $result->fetch_assoc();
                if (time() - strtotime($row["time"]) <= 300){
                    echo(json_encode(array(
                    "code" => -2,
                    "msg" => "该邮箱已经注册过splish账号，但是还未进行验证。如果您的邮箱没有收到验证邮件，请在发送邮件5分钟后再尝试发送新的邮件。"
                    )));
                    exit;
                }
                $sql->query("DELETE FROM email_checking WHERE email = '$reg_email'");
                
                $random_token = hash("sha512",$ip.$reg_key.$reg_email.strval(time()).strval(rand(100000,999999)));
                $password_hash = hash("sha512",$reg_password);
                $sql->query("INSERT INTO email_checking (email, time, ip, card_key, token, password) VALUES ('$reg_email', NOW(), '$ip', '$reg_key', '$random_token', '$password_hash')");
            
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
                    $mail->Subject = 'splish账号注册确认邮件';
                    $mail->Body = '<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>注册即将成功</title>
</head>
<body>
  <h1>恭喜您！</h1>
  <p>您现在离成功注册splish账号只差一步了，点击下方的链接即可完成注册！</p>
  <a href="http://check.nl.splish.js.mcdds.cn/user/register_check.php?email='.$reg_email.'&password='.$password_hash.'&key='.$reg_key.'&token='.$random_token.'">http://check.nl.splish.js.mcdds.cn/user/register_check.php?email='.$reg_email.'&password='.$password_hash.'&key='.$reg_key.'&token='.$random_token.'</a>
</body>
</html>
';
                    $mail->send();
                } catch (Exception $e) {
                
                }
                echo(json_encode(array(
                "code" => 1,
                "msg" => "一封验证邮件已经发送到了您的邮箱".$reg_email."中，请前往查收并验证。"
                )));
                exit;
                
                
            } else {
                echo(json_encode(array(
                "code" => -2,
                "msg" => "key".$reg_key."输入错误"
                )));
                exit;
            }
            
            
            
        }
    }
    
} else {
    exit;
}