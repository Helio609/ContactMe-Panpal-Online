<?php
require_once('../cron/dbs.class.php');
require_once('../cron/nation.class.php');
$msg = array(
    'code' => 0,
    'msg' => '',
);
$action = isset($_POST['action']) ? $_POST['action'] : NULL;
$name = isset($_POST['name']) ? $_POST['name'] : NULL;
$id = isset($_POST['id']) ? $_POST['id'] : NULL;
$birth = isset($_POST['birth']) ? $_POST['birth'] : NULL;
$gender = isset($_POST['gender']) ? $_POST['gender'] : NULL;
$password = isset($_POST['password']) ? $_POST['password'] : NULL;
$profile = isset($_POST['profile']) ? $_POST['profile'] : NULL;
$email = isset($_POST['email']) ? $_POST['email'] : NULL;
$qq = isset($_POST['qq']) ? $_POST['qq'] : NULL;
$wechat = isset($_POST['wechat']) ? $_POST['wechat'] : NULL;
$phone = isset($_POST['phone']) ? $_POST['phone'] : NULL;
$nation = isset($_POST['nation']) ? $_POST['nation'] : NULL;
$offset = isset($_POST['offset']) ? $_POST['offset'] : 1;
$parameterList = array(
    'nation' => $nation,
    'name' => $name,
    'password' => $password,
    'birth' => $birth,
    'gender' => $gender,
    'profile' => $profile,
    'email' => $email,
    'qq' => $qq,
    'wechat' => $wechat,
    'phone' => $phone,
    'id' => $id,
    'offset' => $offset
);
handle($action);

function handle($action)
{
    global $msg;
    global $parameterList;
    if (!isset($action)) {
        $msg['code'] = 100;
        $msg['msg'] = 'No such action';
        exit(json_encode($msg));
    }
    if (!checkParameter($action)) {
        $msg['code'] = 101;
        $msg['msg'] = 'Please check your form';
        exit(json_encode($msg));
    }
    switch ($action) {
        case 'add':
            $response = addOne($parameterList);
            if ($response == 200) {
                $msg['code'] = 200;
                $msg['msg'] = "success";
            } else {
                $msg['code'] = $response;
                $msg['msg'] = "error";
            }
            echo json_encode($msg);
            break;
        case 'delete':
            $response = deleteOne($parameterList);
            if ($response == 200) {
                $msg['code'] = 200;
                $msg['msg'] = "success";
            } else {
                $msg['code'] = $response;
                $msg['msg'] = "Wrong id or password";
            }
            echo json_encode($msg);
            break;
        case 'modify':
            break;
        case 'getinfo':
            $response = getInfo($parameterList);
            if ($response == 200) {
                $msg['code'] = 200;
                $msg['msg'] = "success";
            } else {
                $msg['code'] = $response;
                $msg['msg'] = "No such person";
            }
            echo json_encode($msg);
            break;
        case 'getinfolist':
            getInfoList($parameterList['offset']);
            echo json_encode($msg);
            break;
    }
}
function checkParameter($action)
{
    global $msg;
    global $parameterList;
    switch ($action) {
        case 'add':
            if (isset($parameterList['name']) && isset($parameterList['password']) && isset($parameterList['birth']) && isset($parameterList['gender']) && isset($parameterList['profile']) && isset($parameterList['nation']))
                return true;
            else
                return false;
        case 'delete':
            if (isset($parameterList['password']) && isset($parameterList['id']))
                return true;
            else
                return false;
            break;
        case 'modify':
            break;
        case 'getinfo':
            if (isset($parameterList['id'])) {
                return true;
            } else {
                return false;
            }
            break;
        case 'getinfolist':
            return true;
            break;
    }
}
function addOne($data)
{
    global $msg;
    $dbs = new DBS();
    //User existed 
    $sql = "SELECT * FROM `person` WHERE `name` = '" . $data['name'] . "' AND `password` = '" . $data['password'] . "'";
    if ($dbs->query($sql)->num_rows > 0) {
        $msg['code'] = 104;
        $msg['id'] = null;
        $msg['msg'] = 'User exist!';
        exit(json_encode($msg));
    }



    $ret = 103;
    $sql = "INSERT INTO `person`(`name`, `password`, `birth`, `gender`, `nation`, `profile`,`email`, `qq`, `wechat`, `phone`,`date`) VALUES ('" . $data['name'] . "','" . $data['password'] . "','" . $data['birth'] . "','" . $data['gender'] . "','" . $data['nation'] . "','" . $data['profile'] . "','" . $data['email'] . "','" . $data['qq'] . "','" . $data['wechat'] . "','" . $data['phone'] . "',now())";
    if ($dbs->query($sql)) {
        $ret = 200;
        $sql = "SELECT * FROM `person` WHERE `name` = '" . $data['name'] . "' AND `password` = '" . $data['password'] . "'";
        $id = $dbs->query($sql)->fetch_assoc()['ID'];
        $msg['id'] = $id;
    }
    $dbs->close();
    return $ret;
}
function deleteOne($data)
{
    $dbs = new DBS();
    $ret = 103;
    $sql = "SELECT * FROM `person` WHERE `id` = '" . $data['id'] . "'";
    $response = $dbs->query($sql);
    if ($response->num_rows > 0) {
        $sql = "DELETE FROM `person` WHERE `id` = '" . $data['id'] . "' AND `password`='" . $data['password'] . "'";
        if ($dbs->query($sql)) {
            $ret = 200;
        }
    }
    $dbs->close();
    return $ret;
}
function getInfo($data)
{
    global $msg;
    $dbs = new DBS();
    $sql = "SELECT * FROM `person` WHERE `id` = '" . $data['id'] . "'";
    $response = $dbs->query($sql);
    if ($response->num_rows > 0) {
        $result = $response->fetch_assoc();
        $msg['name'] = $result['name'];
        $msg['birth'] = $result['birth'];
        $msg['gender'] = $result['gender'];
        $msg['nation'] = abbreviationNationNameToFullName($result['nation']);
        $msg['profile'] = $result['profile'];
        $msg['pic'] = $result['pic'];
        $msg['email'] = $result['email'];
        $msg['qq'] = $result['qq'];
        $msg['wechat'] = $result['wechat'];
        $msg['phone'] = $result['phone'];
        return 200;
    } else {
        return 105;
    }
}
function getInfoList($offset){
    $dbs = new DBS();
    global $msg;
    $msg['offset'] = $offset;
    $pageSize = 15;
    $offset == 1 ? $msg['isBegin'] = true : $msg['isBegin'] = false;
    $totalRecordsSql = "SELECT COUNT(*) FROM `person`";
    $totalRecords = $dbs->query($totalRecordsSql)->fetch_assoc()['COUNT(*)'];

    $sql = "SELECT * FROM `person` ORDER BY date DESC LIMIT ".($offset - 1) * 15 ." , ".$pageSize;
    $response = $dbs->query($sql);
    $pages = ceil((($totalRecords + $pageSize - 1) / $pageSize) -1);//todo
    $offset >= $pages ? $msg['isEnd'] = true : $msg['isEnd'] = false;
    if ($response) {
        if ($response->num_rows > 0) {
            $msg['code'] = 200;
            $msg['msg'] = 'success';
            $msg['mem'] = array();
            $msg['totalPages'] =$pages;
            $user = 0;
            while ($row = $response->fetch_assoc()) {
                $msg['mem'][$user]['id'] = $row['ID'];
                $msg['mem'][$user]['name'] = $row['name'];
                $msg['mem'][$user]['birth'] = $row['birth'];
                $msg['mem'][$user]['profile'] = $row['profile'];
                $msg['mem'][$user]['gender'] = $row['gender'];
                $msg['mem'][$user]['date'] = $row['date'];
                $msg['mem'][$user]['pic'] = $row['pic'];
                $user = $user + 1;
            }
        }else{
            $msg['totalPages'] = 1;
            $msg['code'] = 106;
            $msg['msg'] = 'In the End';
        }
    } else {
        $msg['code'] = 300;
        $msg['msg'] = 'Database error';
    }
}
