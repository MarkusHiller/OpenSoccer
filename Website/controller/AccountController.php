<?php

class AccountController {
    
    public function login() {
        $resultModel = new LoginResultModel();
         
        if(!empty($_POST) && isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = md5('1'.$_POST['password'].'29');

            $sql = "SELECT team FROM ".CONFIG_TABLE_PREFIX."users WHERE username = '$username' AND password = '$password' LIMIT 1";
            $result = DB::query($sql, true);
            $count = mysql_num_rows($result);
            
            if($count == 1) {
                // $res_obj = mysql_fetch_object($result);
                $_SESSION['IsLoggedin'] = true;
                $resultModel->err = false;
                $resultModel->hasTeam = mysql_result($result, 0) != "" ? true : false;
            } else {
                $resultModel->err = true;
            }
        } else {
            $resultModel->err = true;
        }
        
        echo json_encode($resultModel);
        return;
    }

    public function getLoginState() {
        echo json_encode($_SESSION['IsLoggedin']);
        return;
    }
    
    public function logout() {
        session_destroy();
        http_response_code(201);
    }
    
}