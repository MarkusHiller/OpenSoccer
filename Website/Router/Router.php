<?php

class Router {
    
    protected static $_routes = array();
    
    public static function add($url, $action, $ensureAuth) {
        $route = new Route($action, $ensureAuth);
        self::$_routes[$url] = $route;
    }
    
    public static function dispatch() {
        if(isset($_SERVER['REQUEST_URI'])) {
            $split = explode('/', $_SERVER['REQUEST_URI']);
            $url = '/'.end($split);
        } else {
            $url = '/';
        }
        
        if(array_key_exists($url, self::$_routes)) {
            //fix for json data
            $rest_json = file_get_contents("php://input");
            $_POST = json_decode($rest_json, true);

            self::$_routes[$url]->run();
            return;
        } else {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
    
}