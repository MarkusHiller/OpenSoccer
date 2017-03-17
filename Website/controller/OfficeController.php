<?php

class OfficeController {
    
    public function GetCentralData() {
        $resultModel = new ResultModel();
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
    public function GetProtocolData() {
        $resultModel = new PagingResultModel();
        
        //use ids from other team for administration
        if(isset($_GET['ids'])) {
            
        }
        
        $teamIds = $_SESSION['TeamIds'];
        
        $sql = "SELECT text, typ, zeit FROM ". CONFIG_TABLE_PREFIX ."protokoll WHERE team = '". $teamIds ."' ORDER BY zeit DESC LIMIT 0, 25";
        $result = DB::query($sql, true);
        
        $resultModel->err = false;
        $resultModel->currentPage = 1;
        $resultModel->pages = 5;
        while($pro = mysql_fetch_object($result)) {
            $pro->zeit = date('d.m.Y', $pro->zeit);
            $resultModel->data[] = $pro;
        }
        echo json_encode($resultModel);
        return;
    }
    
    public function GetNotes() {
        $resultModel = new ResultModel();
        
        $resultModel->err = false;
        
        echo json_encode($resultModel);
        return;
    }
    
    public function SaveNote() {
        $resultModel = new ResultModel();
        $userIds = $_SESSION['UserIds'];
        $note = $_POST['note'];
        
        $sql = "INSERT INTO ". CONFIG_TABLE_PREFIX ."users_notizen (user, text, textColor, backgroundColor) VALUES ('".$userIds."', '".$note."', 'black', '#FFFFFF')";
        $result = DB::query($sql, true);
        
        $resultModel->err = false;
        
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
}