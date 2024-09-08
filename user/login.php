<?php
include("../libraries/libraries.php");

echo(json_encode(array(
            "code" => -1,
            "msg" => "服务暂停，请查看官方群\r\nhttp://splish.zone/"
            )));
exit;


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
        $log_email = $json->email;
        $log_password_hash = $json->password_hash;
        $log_check_token = $json->data->login_check_token;
        $log_check_token2 = $json->data->login_check_token2;
        $timestamp = $json->time;
        if ($log_email == "" || $log_password_hash == "" || $timestamp <= 0) {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息完整无误"
            )));
            exit;
        }
        if (!checkStringFormat($log_email) || !checkStringFormat($log_password_hash)) {
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
        
        $result = $sql->query("SELECT * FROM users WHERE email = '$log_email'");
        if ($result->num_rows != 1){
            echo(json_encode(array(
            "code" => -2,
            "msg" => "该邮箱暂未注册splish账号"
            )));
            exit;
        }
        $row = $result->fetch_assoc();
        
        if ($log_password_hash != $row['password']) {
            echo(json_encode(array(
            "code" => -2,
            "msg" => "密码输入错误"
            )));
            exit;
        }
        $token = hash("sha512",strval(time()).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)).strval(rand(100000,999999)));
        $request_token = hash("sha512",$token.strval($timestamp)."dbggddfgtythrjtgrvtrvytb7u655r6yt5tfghuykmdssiop");
        $result = $sql->query("UPDATE users SET latest_login_ip = '$ip', latest_login_time = NOW(), request_token = '$request_token', latest_request_time = $timestamp WHERE email = '$log_email'");
        
        $result_token = hash("sha512",$log_check_token.md5($log_password_hash.$log_check_token2).'1dsgyhj3erdfsdsd345dsdh4rwsdvzdcmkluylpyu67tygrfnvgrt54rrtfghfgfhjddfcmfgxdttyddvsdsdghghvxsdhutyere457656uukkliftgdraescnhgnvbsderret5e6trdgd4ewr5tsrfgjmnfdbder3r6565ydfghh6').md5($log_check_token.'fdrt34gwe35576hdsdyty5e6789oyrfwjksdfbjnkdfghirdyuiyuiertyui'.$log_check_token2);
        $result_token = strtolower($result_token);
        
        
        
        echo(json_encode(array(
            "code" => 1,
            "msg" => "登录成功",
            "data" => array(
                "result_token" => $result_token,
                "request_token" => $token
                )
            )));
        
    }
    
    
}