<?php

class OfficeController {
    
    public function GetCentralData() {
        $resultModel = new ResultModel();
        
        $teamIds = $_SESSION['TeamIds'];
        
        $sql = "SELECT aufstellung, staerke, konto, punkte, rank, elo, pokalrunde, cuprunde, posToSearch, wantTests, name FROM ". CONFIG_TABLE_PREFIX ."teams WHERE ids = '$teamIds'";
        $result = DB::query($sql, false);
        
        if(mysql_num_rows($result) == 0) {
            $resultModel->err = false;
            $resultModel->msg = "Es wurden keine Team Daten gefunden";
            echo json_encode($resultModel);
            return;
        }
        
        if (GameTime::getMatchDay() < 22) {
            $daysUntilNextYouth = intval(GameTime::getMatchDay() % 3);
            switch ($daysUntilNextYouth) {
                case 2:
                    $nextYouth = _('morgen');
                    $nextYouthDay = GameTime::getMatchDay()+1;
                    break;
                case 1:
                    $nextYouth = _('übermorgen');
                    $nextYouthDay = GameTime::getMatchDay()+2;
                    break;
                case 0:
                    $nextYouth = _('heute');
                    $nextYouthDay = GameTime::getMatchDay();
                    break;
                default:
                    throw new Exception('Invalid next youth index: '.$daysUntilNextYouth);
            }
        }
        else {
            $nextYouth = _('in drei Tagen');
            $nextYouthDay = 3;
        }
        
        $resultModel->err = false;
        $resultModel->data = mysql_fetch_object($result);
        $resultModel->data->nextYouth = $nextYouth;
        $resultModel->data->nextYouthDay = $nextYouthDay;
        $resultModel->data->serverTime = sprintf('Saison %d', GameTime::getSeason()).' · '.sprintf('Spieltag %d', GameTime::getMatchDay()).' · '.sprintf('%s Uhr', date('d.m.Y, H:i'));
        $resultModel->data->einsatzInAuktionen = Utils::einsatz_in_auktionen($teamIds);
        $resultModel->data->shortContracts = $this->GetShortContracts($teamIds);
        
        echo json_encode($resultModel);
        return;
    }
    
    public function GetProtocolData() {
        $resultModel = new PagingResultModel();
        
        //use ids from other team for administration
        if(isset($_GET['ids'])) {
            
        }
        
        $teamIds = $_SESSION['TeamIds'];
        $type = $_GET['type'];
        $page = intval($_GET['page']);
        $resultsFrom = ($page - 1) * 25;
        $resultsTo = $page * 25;
        $sql = "SELECT text, typ, zeit FROM ". CONFIG_TABLE_PREFIX ."protokoll WHERE team = '$teamIds' ORDER BY zeit DESC LIMIT $resultsFrom, $resultsTo";
        $result = DB::query($sql, true);
        
        $resultModel->err = false;
        $resultModel->currentPage = $page;
        
        while($pro = mysql_fetch_object($result)) {
            $pro->zeit = date('d.m.Y', $pro->zeit);
            $resultModel->data[] = $pro;
        }
        $sql = "SELECT COUNT(*) FROM ". CONFIG_TABLE_PREFIX ."protokoll WHERE team = '$teamIds'";
        $result = DB::query($sql, true);
        $resultModel->pages = ceil(mysql_result($result, 0) / 25);
        echo json_encode($resultModel);
        return;
    }
    
    public function GetNotes() {
        $resultModel = new ResultModel();
        $userIds = $_SESSION['UserIds'];
        
        $sql = "SELECT id, text FROM " . CONFIG_TABLE_PREFIX . "users_notizen WHERE user = '$userIds' LIMIT 0, 25";
        $result = DB::query($sql, false);
        while($note = mysql_fetch_object($result)) {
            $resultModel->data[] = $note;
        }
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
    public function SearchForTeamOrManager() {
        $resultModel = new ResultModel();
        
        $searchInput = $_GET['searchInput'];
        
        $sql = "SELECT ids, name FROM " . CONFIG_TABLE_PREFIX . "teams WHERE name LIKE '%$searchInput%' LIMIT 0, 10";
        $result = DB::query($sql, false);
        while ($team = mysql_fetch_object($result)) {
            $resultModel->data->matchedTeams[] = $team;
        }
        $sql = "SELECT ids, username FROM " . CONFIG_TABLE_PREFIX . "users WHERE username LIKE '%$searchInput%' LIMIT 0, 10";
        $result = DB::query($sql, false);
        while ($manager = mysql_fetch_object($result)) {
            $resultModel->data->matchedManager[] = $manager;
        }
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
    public function SaveNote() {
        $resultModel = new ResultModel();
        $userIds = $_SESSION['UserIds'];
        $note = $_POST['note'];
        
        $sql = "INSERT INTO ". CONFIG_TABLE_PREFIX ."users_notizen (user, text, textColor, backgroundColor) VALUES ('".$userIds."', '".$note."', '', '')";
        $result = DB::query($sql, false);
        
        $resultModel->err = false;
        $resultModel->data = mysql_insert_id();
        echo json_encode($resultModel);
        return;
    }
    
    public function DelNote() {
        $resultModel = new ResultModel();
        $userIds = $_SESSION['UserIds'];
        $noteId = $_GET['noteId'];
        
        $sql = "DELETE FROM ". CONFIG_TABLE_PREFIX ."users_notizen WHERE user = '$userIds' AND id = $noteId";
        $result = DB::query($sql, false);
        
        if(mysql_affected_rows() > 0) {
            $resultModel->err = false;
        } else {
            $resultModel->err = true;
        }  
        
        echo json_encode($resultModel);
        return;
    }
    
    public function GetSettingsData() {
        $resultModel = new ResultModel();
        
        $userIds = $_SESSION['UserIds'];
        $sql = "SELECT email FROM ". CONFIG_TABLE_PREFIX ."users WHERE ids = '$userIds'";
        $result = DB::query($sql, true);
        
        if(mysql_num_rows($result) == 0) {
            $resultModel->err = true;
            $resultModel->msg = "No data found";
            echo json_encode($resultModel);
            return;
        }
        
        $resultModel->err = false;
        $resultModel->data = mysql_result($result, 0);
        
        echo json_encode($resultModel);
        return;
    }
    
    private function GetShortContracts($teamIds) {
        $player = array();
        //$in_14_tagen = getTimestamp('+14 days');
        $sql = "SELECT ids, vorname, nachname, vertrag, gehalt, wiealt FROM " . CONFIG_TABLE_PREFIX . "spieler WHERE team = '$teamIds' ORDER BY vertrag ASC LIMIT 0, 10";
        $result = DB::query($sql, false);
        while($playerContract = mysql_fetch_object($result)) {
            $playerContract->vertrag = date('d.m.Y', $playerContract->vertrag);
            $player[] = $playerContract;
            
        }
        return $player;
    }
}