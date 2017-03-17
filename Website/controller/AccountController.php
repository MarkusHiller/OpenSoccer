<?php

class AccountController {
    
    public function login() {
        $resultModel = new LoginResultModel();
        
        if(!empty($_POST) && isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = md5('1'.$_POST['password'].'29');
            
            $sql = "SELECT ids, team FROM ".CONFIG_TABLE_PREFIX."users WHERE username = '$username' AND password = '$password' LIMIT 1";
            $result = DB::query($sql, true);
            $count = mysql_num_rows($result);
            
            if($count == 1) {
                $userdata = mysql_fetch_object($result);
                $teamIds = $userdata->team;
                $_SESSION['IsLoggedin'] = true;
                $_SESSION['UserIds'] = $userdata->ids;
                $_SESSION['TeamIds'] = $teamIds;
                $resultModel->err = false;
                $resultModel->hasTeam = $teamIds != "" ? true : false;
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
    
    public function changePassword() {
        $resultModel = new ResultModel();
        $userIds = $_SESSION['UserIds'];
        
        if($userIds == CONFIG_DEMO_USER) {
            $resultModel->err = true;
            $resultModel->msg = 'Der Demo-Account kann sein Passwort nicht ändern';
            echo json_encode($resultModel);
            return;
        }
        
        if(!isset($_POST['oldPw']) || !isset($_POST['newPw']) || !isset($_POST['newPwConfirm'])) {
            $resultModel->err = true;
            $resultModel->msg = 'Die Date sind unvollständig';
            echo json_encode($resultModel);
            return;
        }
        
        $oldPw = $_POST['oldPw'];
        $newPw = $_POST['newPw'];
        $newPwConfirm = $_POST['newPwConfirm'];
        
        if($newPw != $newPwConfirm) {
            $resultModel->err = true;
            $resultModel->msg = 'Die Passwörter stimmen nicht überein'; //TODO:: Handle with error code
            echo json_encode($resultModel);
            return;
        }
        
        if (mb_strlen($newPw) < 6) {
            $resultModel->err = true;
            $resultModel->msg = 'Das neue Passwort muss mindestens 6 Zeichen lang sein';
            echo json_encode($resultModel);
            return;
        }
        
        $pw_alt = md5('1'.$oldPw.'29');
        $pw_neu = md5('1'.$newPw.'29');
        $sql = "UPDATE ".CONFIG_TABLE_PREFIX."users SET password = '$pw_neu' WHERE password = '$pw_alt' AND ids = '$userIds'";
        $result = DB::query($sql, false);

        if ($result != FALSE) {
            if (mysql_affected_rows() > 0) {
                //setTaskDone('change_pw');
                $resultModel->err = false;
                $resultModel->msg = 'Dein Passwort wurde erfolgreich geändert.';
                echo json_encode($resultModel);
                return;
            } else {
                $resultModel->err = true;
                $resultModel->msg = 'Dein altes Passwort stimmt nicht.';
                echo json_encode($resultModel);
                return;
            }
        }
        
    }
}