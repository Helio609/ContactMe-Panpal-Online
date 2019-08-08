<?php
require_once('../cron/dbs.class.php');
$msg = array(
    'code' => 0,
    'msg' => ''
);
function getExtension($file)
{
    return substr($file, strrpos($file, '.') + 1);
}
function userExist($id, $password)
{
    global $msg;
    $dbs = new DBS();
    $sql = "SELECT * FROM `person` WHERE `id` = ".$id." AND `password` = '".$password."'";
    $response = $dbs->query($sql);
    if ($response) {
        if ($response->num_rows == 1) {
            $dbs->close();
            return true;
        } else {
            $dbs->close();
            $msg['code'] = 112;
            $msg['msg'] = 'Wrong id or password';
            exit(json_encode($msg));
        }
    } else {
        $dbs->close();
        $msg['code'] = 111;
        $msg['msg'] = 'Database error';
        exit(json_encode($msg));
    }
}
function updateUserInfo($id,$password,$picPath)
{
    global $msg;
    $dbs = new DBS();
    $sql = "UPDATE `person` SET `pic` = '".$picPath."' WHERE `id` = ".$id." AND `password` = '".$password."'";
    $response = $dbs->query($sql);
    if($response){
        $msg['code'] = 200;
        $msg['msg'] = "success";
        exit(json_encode($msg));
    }else{
        $msg['code'] = 113;
        $msg['msg'] = 'Update error';
        exit(json_encode($msg));
    }
}
function main()
{
    global $msg;
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    if($action != 'upload'){
        $msg['code'] = 114;
        $msg['msg'] = 'No such action';
        exit(json_encode($msg));
    }

    if (!($action && $id & $password)) {
        $msg['code'] = 107;
        $msg['msg'] = 'Please check your form';
        exit(json_encode($msg));
    }

    $allowedExts = array("gif", "jpeg", "jpg", "png", "bmp");
    $temp = explode(".", $_FILES["file"]["name"]);
    $extension = end($temp);     // 获取文件后缀名
    if ((($_FILES["file"]["type"] == "image/gif")
            || ($_FILES["file"]["type"] == "image/jpeg")
            || ($_FILES["file"]["type"] == "image/jpg")
            || ($_FILES["file"]["type"] == "image/pjpeg")
            || ($_FILES["file"]["type"] == "image/x-png")
            || ($_FILES["file"]["type"] == "image/png"))
        && in_array($extension, $allowedExts)
    ) {
        if ($_FILES["file"]["error"] > 0) {
            $msg['code'] = 108;
            $msg['msg'] =  $_FILES["file"]["error"];
            exit(json_encode($msg));
        } else {
            if (file_exists("pic/" . $id . '.' . getExtension($_FILES['file']['name']))) {
                $msg['code'] = 110;
                $msg['msg'] = 'File already exist,Delete your page and upload photo again';
                exit(json_encode($msg));
            } else {
                if(userExist($id,$password)){
                    move_uploaded_file($_FILES["file"]["tmp_name"],  $_SERVER['DOCUMENT_ROOT'].'/pic/' . $id . '.' . getExtension($_FILES['file']['name']));
                    updateUserInfo($id,$password,'/pic/'. $id . '.' . getExtension($_FILES['file']['name']));
                    exit(json_encode($msg));
                }
            }
        }
    } else {
        $msg['code'] = 109;
        $msg['msg'] = 'Disallow file type';
        exit(json_encode($msg));
    }
}
main();