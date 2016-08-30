<?php
require_once '/../config.php';

class DB {
    
    private static $connected = false;
    
    public static function query($query, $closeConnection) {
        if (static::$connected == false) {
            static::connect();
        }

        $result = mysql_query($query);
        
        if($closeConnection) {
            static::close();
        }
        
        return $result;
    }
    
    private static function connect() {
        mysql_connect(CONFIG_DATABASE_HOST, CONFIG_DATABASE_USERNAME, CONFIG_DATABASE_PASSWORD) or die ('Falsche MySQL-Daten!');
        mysql_select_db(CONFIG_DATABASE_NAME) or die ('Datenbank existiert nicht!');
        static::$connected = true;
    }
    
    private static function close() {
        mysql_close();
        static::$connected = false;
    }
    
}