<?php

class Route {
    
    private $action;
    private $ensureAutentification;
    
    public function __construct($action, $ensureAutentification) {
        $this->action = $action;
        $this->ensureAuthentification = $ensureAutentification;
    }
    
    public function run() {
        if($this->ensure_Authentification()) {
            if(is_string($this->action)) {
                $split = explode('@', $this->action);
                $controller = new $split[0];
                $controller->$split[1]();
            } else {
                call_user_func($this->action);
            }
        }
    }
    
    private function ensure_Authentification() {
        if($this->ensureAuthentification && (!isset($_SESSION['IsLoggedin']) || !$_SESSION['IsLoggedin'])) {
            http_response_code(401);
            return false;
        } else {
            return true;
        }
    }
}