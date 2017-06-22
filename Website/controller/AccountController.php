<?php

class AccountController {
    
    public function login() {
        $resultModel = new LoginResultModel();
        
        if(!empty($_POST) && isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = md5('1'.$_POST['password'].'29');
            
            $sql = "SELECT id, ids, email, username, status, liga, team, regdate, last_login, readSticky, multiSperre, acceptedRules, hasLicense FROM " . CONFIG_TABLE_PREFIX . "users WHERE username = '$username' AND password = '$password' LIMIT 1";
            $result = DB::query($sql, true);
            $count = mysql_num_rows($result);
            
            if($count == 1) {
                $userdata = mysql_fetch_object($result);
                $teamIds = $userdata->team;
                $ligaIds = $userdata->liga;
                $_SESSION['IsLoggedin'] = true;
                $_SESSION['UserIds'] = $userdata->ids;
                $_SESSION['TeamIds'] = $teamIds;
                $_SESSION['LigaIds'] = $ligaIds;
                
                $this->setOldLoginData($userdata);
                
                $resultModel->err = false;
                $resultModel->hasTeam = $teamIds !== "" && substr($teamIds, 0, 2) !== "__" ? true : false;
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
                Utils::setTaskDone('change_pw');
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
    
    public function checkDataForRegistration() {
        $resultModel = new ResultModel();
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        if(!$this->isUsernameUnique($username)) {
            $resultModel->err = true;
            $resultModel->msg = 'Der Username ist bereits vergeben.';
            echo json_encode($resultModel);
            return;
        }
        
        if(!$this->isEmailUnique($email)) {
            $resultModel->err = true;
            $resultModel->msg = 'Die E-Mail-Adresse wird bereits verwendet.';
            echo json_encode($resultModel);
            return;
        }
        
        $resultModel->err = false;
        echo json_encode($resultModel);
        return;
    }
    
    public function changeTeam() {
        $resultModel = new ResultModel();
        $newTeamIds = $_POST['teamId'];
        $newLigaIds = $_POST['ligaId'];
        $userIds = $_SESSION['UserIds'];
        $oldTeamIds = $_SESSION['TeamIds'];
        
        $sql = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."users WHERE team = '$newTeamIds'";
        $result = DB::query($sql, false);
        
        if(mysql_result($result, 0) > 0) {
            $resultModel->err = true;
            $resultModel->msg = "Das Team ist bereits vergeben.";
            echo json_encode($resultModel);
            return;
        }
        
        if(isset($_SESSION['TeamIds']) && substr($_SESSION['TeamIds'], 0, 2) != "__") {
            TeamController::resetTeam($_SESSION['TeamIds'], $_SESSION['LigaIds']);
        }

        $sql = "UPDATE ". CONFIG_TABLE_PREFIX . "users SET team = '$newTeamIds', liga = '$newLigaIds' WHERE ids = '$userIds'";
        $result = DB::query($sql, false);

        if(mysql_affected_rows() != 1) {
            $resultModel->err = true;
            $resultModel->msg = "Es ist ein Fehler aufgetreten.";
            echo json_encode($resultModel);
            return;
        }

        $resultModel->err = false;
        echo json_encode($resultModel);
        return;
    }
    
    public function registerUser() {
        $resultModel = new ResultModel();
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        if(!$this->isUsernameUnique($username)) {
            $resultModel->err = true;
            echo json_encode($resultModel);
            return;
        }
        
        if(!$this->isEmailUnique($email)) {
            $resultModel->err = true;
            echo json_encode($resultModel);
            return;
        }
        
        $last_ip = Utils::getUserIP();
        $username = mysql_real_escape_string(trim(strip_tags($username)));
        $username = str_replace('_', '', $username);
        $password = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);
        $password_db = md5('1'.$password.'29');
        $blackList1 = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."blacklist WHERE email = '".md5($email)."' AND until > ".time();
        $blackList2 = DB::query($blackList1, false);
        $blackList3 = mysql_result($blackList2, 0);
        $schon_vorhandene_user = $blackList3;
        if ($schon_vorhandene_user == 0) {
            $uniqueIDHash = md5($email.time());
            $sql4 = "INSERT INTO ".CONFIG_TABLE_PREFIX."users (email, username, password, regdate, last_login, last_ip, ids, liga, team, last_uniqueHash, last_uagent, last_provider, infotext) VALUES ('".$email."', '".$username."', '".$password_db."', ".time().", ".Utils::bigintval(Utils::getTimestamp('-14 days')).", '".$last_ip."', '".$uniqueIDHash."', '', '__".$uniqueIDHash."', '$last_ip', '', '', '')";
            $sql5 = DB::query($sql4, false);
            if ($sql5 != FALSE) {
                if (isset($_SESSION['referralID'])) {
                    $refID = mysql_real_escape_string(trim($_SESSION['referralID']));
                    if (mb_strlen($refID) == 32) {
                        $addReferral1 = "INSERT INTO ".CONFIG_TABLE_PREFIX."referrals (werber, geworben, zeit) VALUES ('".$refID."', '".$uniqueIDHash."', ".time().")";
                        $addReferral2 = DB::query($addReferral1, false);
                    }
                }
                
                if (CONFIG_IS_LOCAL_INSTALLATION) {
                    // echo '<p><strong>'._('Dein Passwort lautet:').'</strong> '.htmlspecialchars($password).'</p>';
                    // echo '<p>'._('Du brauchst dieses Passwort unbedingt für den ersten Login. Danach kannst Du es in den Einstellungen ändern, wenn Du möchtest.').'</p>';
                } else {
                    EmailService::send($email, $username, $password);
                }
                $resultModel->err = false;
                echo json_encode($resultModel);
                return;
            }
        }
        
        
        $resultModel->err = true;
        $resultModel->msg = "Es ist ein Fehler aufgetreten. Bitte versuche es später noch einmal.";
        echo json_encode($resultModel);
        return;
    }
    
    private function setOldLoginData($userdata) {
        
        if (substr($userdata->username, 0, 9) == 'GELOESCHT') {
            $_SESSION['loggedin'] = 0;
            $hadresse = 'Location: /geloeschterAccount.php';
        } else {
            if ($userdata->team == '__'.$userdata->ids) {
                $_SESSION['multiAccountList'] = '';
                $_SESSION['liga'] = '';
                $_SESSION['teamname'] = '';
                $_SESSION['scout'] = 1;
                $_SESSION['multiSperre'] = 0;
            } else {
                $teamname1 = "SELECT name, scout FROM ".CONFIG_TABLE_PREFIX."teams WHERE ids = '".$userdata->team."'";
                $teamname2 = DB::query($teamname1, false);
                $teamname3 = mysql_fetch_assoc($teamname2);
                // WERT last_managed AUF 0 SETZEN ANFANG
                $tu1 = "UPDATE ".CONFIG_TABLE_PREFIX."teams SET last_managed = 0 WHERE ids = '".$userdata->team."'";
                $tu2 = DB::query($tu1, false);
                // WERT last_managed AUF 0 SETZEN ENDE
                // MULTIS TEAMBEZOGEN LADEN ANFANG
                $ma1 = "SELECT a.user2, b.team FROM ".CONFIG_TABLE_PREFIX."users_multis AS a JOIN ".CONFIG_TABLE_PREFIX."users AS b ON a.user2 = b.ids WHERE a.user1 = '".$userdata->ids."'";
                $ma2 = DB::query($ma1, FALSE);
                $aktiveMultiAccounts = 0;
                $multiAccountList = array();
                while ($ma3 = mysql_fetch_assoc($ma2)) {
                    if (strlen($ma3['team']) == 32) { $aktiveMultiAccounts++; }
                    $multiAccountList[] = $ma3['team'];
                }
                $_SESSION['multiSperre'] = 0; // entfernen wenn Multi-Sperre wieder aktiviert werden soll
                
                $multiAccountList = implode('-', $multiAccountList);
                // MULTIS TEAMBEZOGEN LADEN ENDE
                $_SESSION['multiAccountList'] = $multiAccountList;
                $_SESSION['liga'] = $userdata->liga;
                $_SESSION['teamname'] = $teamname3['name'];
                $_SESSION['scout'] = $teamname3['scout'];
            }
            $_SESSION['loggedin'] = 1;
            $_SESSION['userid'] = $userdata->ids;
            $_SESSION['username'] = $userdata->username;
            $_SESSION['status'] = $userdata->status;
            $_SESSION['team'] = $userdata->team;
            $_SESSION['readSticky'] = $userdata->readSticky;
            $_SESSION['acceptedRules'] = $userdata->acceptedRules;
            $_SESSION['hasLicense'] = $userdata->hasLicense;
            $_SESSION['transferGesperrt'] = FALSE;
            $_SESSION['last_forumneu_anzahl'] = 0;
            // LOGIN-LOG ANFANG
            if (isset($_SERVER['HTTP_USER_AGENT'])) { $loginLog_userAgent = mysql_real_escape_string(trim(strip_tags($_SERVER['HTTP_USER_AGENT']))); } else { $loginLog_userAgent = ''; }
            $loginLog_ip = Utils::getUserIP();
            if (isset($_COOKIE['uniqueHash'])) { $loginLog_uniqueHash = mysql_real_escape_string(trim(strip_tags($_COOKIE['uniqueHash']))); } else { $loginLog_uniqueHash = $loginLog_ip; setcookie('uniqueHash', $loginLog_ip, Utils::getTimestamp('+30 days'), '/', str_replace('www.', '.', CONFIG_SITE_DOMAIN), FALSE, TRUE); }
            if (!in_array($userdata->ids, unserialize(CONFIG_PROTECTED_USERS))) {
                $loginLog1 = "INSERT INTO ".CONFIG_TABLE_PREFIX."loginLog (user, zeit, ip, userAgent, uniqueHash) VALUES ('".$userdata->ids."', ".time().", '".$loginLog_ip."', '".$loginLog_userAgent."', '".$loginLog_uniqueHash."')";
                $loginLog2 = DB::query($loginLog1, false);
            }
            // LOGIN-LOG ENDE
            // MAXIMALGEBOT ANFANG
            $tageHier = (time()-$userdata->regdate)/86400;
            if ($tageHier < 0.08) {
                $_SESSION['pMaxGebot'] = 0;
            }
            elseif ($tageHier < 7) {
                $_SESSION['pMaxGebot'] = 1.25;
            }
            elseif ($tageHier < 21) {
                $_SESSION['pMaxGebot'] = 2.5;
            }
            elseif ($tageHier < 42) {
                $_SESSION['pMaxGebot'] = 4;
            }
            elseif ($tageHier < 84) {
                $_SESSION['pMaxGebot'] = 8;
            }
            else {
                $_SESSION['pMaxGebot'] = 16;
            }
            // MAXIMALGEBOT ENDE
            $hadresse = 'Location: /index.php';
            // MANAGER DER SAISON ANFANG
            $_SESSION['mds_abgestimmt'] = TRUE; // vielleicht nicht moeglich
            GameTime::init();
            if (GameTime::getMatchDay() <= 3) {
                $timeout = Utils::getTimestamp('-22 days');
                if ($userdata->regdate < $timeout) {
                    // WENN WAHLBERECHTIGT ANFANG
                    $mds4 = "SELECT COUNT(*) FROM ".CONFIG_TABLE_PREFIX."users_mds WHERE voter = '".$userdata->ids."'";
                    $mds5 = DB::query($mds4, false);
                    $mds6 = mysql_result($mds5, 0);
                    if ($mds6 == 0) { $_SESSION['mds_abgestimmt'] = FALSE; } // wenn wahlberechtigt und trotzdem keine Stimme gefunden
                    // WENN WAHLBERECHTIGT ENDE
                }
            }
            // MANAGER DER SAISON ENDE
            // TRANSFER-SPERREN ANFANG
            $_SESSION['transferGesperrt'] = FALSE;
            if ($userdata->team != '__'.$userdata->ids) {
                $sperrQL1 = "SELECT MAX(transferSperre) FROM ".CONFIG_TABLE_PREFIX."helferLog WHERE managerBestrafen = '".$userdata->ids."'";
                $sperrQL2 = DB::query($sperrQL1, false);
                if (mysql_num_rows($sperrQL2) > 0) {
                    $sperrQL3 = mysql_fetch_assoc($sperrQL2);
                    $transferSperreBis = $sperrQL3['MAX(transferSperre)'];
                    if ($transferSperreBis > time()) {
                        $_SESSION['transferGesperrt'] = TRUE;
                    }
                }
            }
            // TRANSFER-SPERREN ENDE
            if (isset($_POST['returnURL'])) {
                if (strpos($_POST['returnURL'], 'registrier') === FALSE) {
                    $hadresse = 'Location: '.trim(strip_tags($_POST['returnURL']));
                }
            }
        }
    }
    
    private function isUsernameUnique($username) {
        $sql = "SELECT Count(*) FROM ".CONFIG_TABLE_PREFIX."users WHERE username = '$username'";
        $result = DB::query($sql, false);
        $rows = mysql_result($result, 0);
        
        return $rows == 0;
    }
    
    private function isEmailUnique($email) {
        $sql = "SELECT Count(*) FROM ".CONFIG_TABLE_PREFIX."users WHERE email = '$email'";
        $result = DB::query($sql, false);
        $rows = mysql_result($result, 0);
        
        return $rows == 0;
    }
}