<?php
include("../libraries/libraries.php");

/*
echo(json_encode(array(
            "code" => -101,
            "msg" => "服务暂停，恢复时间待定，Splish停止售卖、使用\r\nhttp://splish.zone/"
            )));
exit;
*/


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
        $email = $json->email;
        $request_token = $json->token;
        $timestamp = $json->time;
        
        if ($email == "" || $request_token == "" || $timestamp <= 0) {
            echo(json_encode(array(
            "code" => -1,
            "msg" => "请确保提交信息完整无误"
            )));
            exit;
        }
        if (!checkStringFormat($email) || !checkStringFormat($request_token)) {
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
        $result = $sql->query("SELECT * FROM users WHERE email = '$email'");
        if ($result->num_rows != 1){
            echo(json_encode(array(
            "code" => -2,
            "msg" => "该邮箱暂未注册splish账号"
            )));
            exit;
        }
        $row = $result->fetch_assoc();
        if ($timestamp <= $row["latest_request_time"]){
            echo(json_encode(array(
            "code" => -101,
            "msg" => "数据发生变化，您即将退出登录"
            )));
            exit;
        }
        $token = hash("sha512",$row["request_token"].strval($timestamp)."rfgefe8terfw8769terft789tyhrefg8h9yiuyhdgcfsgyhuidcfugiyh");
        if ($token != $request_token){
            echo(json_encode(array(
            "code" => -101,
            "msg" => "数据发生变化，您即将退出登录"
            )));
            exit;
        }
        $result = $sql->query("UPDATE users SET latest_request_time = $timestamp WHERE email = '$email'");
        $result_data = '{"CanLaunch":{"Mpay_Init":"MPaySDKHelper.Class","Request_URL":"https://x19apigatewayobt.nie.netease.com","Authentication_otp":["x19_HttpEncrypt","x19_ParseLoginResponse"]},"Download_URL":{"Netease_Client":"http://94.131.110.189/file/Client/Splish/NeteaseClient.zip","Netease_Java":"http://94.131.110.189/file/Client/Splish/NeteaseJava.zip","Client":"http://94.131.110.189/file/Client/Splish/Client.zip","Client_Java":"http://94.131.110.189/file/Client/jre1.8.0_361.zip"},"AutoUpdate":{"Latest_version":"1.10","Latest_version_download_URL":"http://94.131.110.189/file/Client/Splish/Splish%20NL%201.10_protected.exe","Latest_version_md5":"f2b8b63451e90ec70c89f71f5f37f862","Latest_version_file_name":"Splish NL 1.10_protected.exe"}}';
        
        $result_data_encode = openssl_encrypt($result_data,"AES-128-CBC",hex2bin(md5($token.$email.strval($timestamp)."fgfgdtyrtyrytrhb")),0,hex2bin("efd5dd272ccbbdfc600c2f4cb343eb20"));
        echo(json_encode(array(
            "code" => 1,
            "msg" => "正常返回",
            "data" => $result_data_encode
            )));
        
    }

}