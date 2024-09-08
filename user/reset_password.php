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
        $reset_email = $json->email;
        
        if ($reset_email == "") {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息完整无误"
            )));
            exit;
        }
        if (!checkStringFormat($reset_email)) {
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
        
        $result = $sql->query("SELECT * FROM users WHERE email = '$reset_email'");
        if ($result->num_rows != 1){
            echo(json_encode(array(
            "code" => -2,
            "msg" => "该邮箱暂未注册splish账号"
            )));
            exit;
        }
        
        $row = $result->fetch_assoc();
        
        if (time() - strtotime($row["latest_password_reset_time"]) <= 300) {
            echo(json_encode(array(
            "code" => -2,
            "msg" => "您重置密码的速度过快，至少在上次重置密码后的5分钟后才能再次重置密码"
            )));
            exit;
        }
        
        $check_time = time();
        $password_twice_hash = hash("sha512",$row["password"]."dfgvsdfsed4wtetf5e5twg54tybrybte5etsrrgvtrhbyrteber5ertb54yeb45e654y56b5656nytgrtghbtryhbetyerbytretyreyrteytrneytrn".strval($check_time));
        
        $sql->query("UPDATE users SET latest_password_reset_time = FROM_UNIXTIME($check_time) WHERE email = '$reset_email'");
        
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
            $mail->Subject = 'splish账号重置密码确认邮件';
            $mail->Body = '<!DOCTYPE html>
<html>
<body>
  <h1>恭喜您！</h1>
  <p>您现在离成功重置splish账号的密码只差一步了，点击下方的链接即可完成重置！</p>
  <a href="https://splish.js.mcdds.cn/user/reset_password_check.php?email='.$reset_email.'&old_password_twice_hash='.$password_twice_hash.'">https://splish.js.mcdds.cn/user/reset_password_check.php?email='.$reset_email.'&old_password_twice_hash='.$password_twice_hash.'</a>
  <p>如果您没有对您的splish账号进行重置密码操作，请忽略此邮件</p>
</body>
</html>
';
            $mail->send();
            } catch (Exception $e) {
                
            }
        
        echo(json_encode(array(
            "code" => 1,
            "msg" => "一封带有重置密码地址的邮件已经发送到了您的邮箱".$reset_email."，请前往邮箱进行重置操作"
        )));
        
        
        
        
        
        
        
        
        
        
        
        
    }
}