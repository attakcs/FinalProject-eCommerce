<?php

class DB {
    private static $conn = null;

    public static function Connect($host, $username, $password, $database)
    {
        self::$conn = new mysqli($host, $username, $password, $database);

        if (self::$conn->connect_errno) {
            throw new \RuntimeException('Failed to connect to Database: '. self::$conn->connect_error);
        }
    }

    public static function Query($sql, $params=[]){
        if(self::$conn === null){
            throw new \RuntimeException('DB connection not initialized');
        }

        $stmt=self::$conn->prepare($sql);
        $err=self::$conn->error;

        if($stmt===false){
            throw new \RuntimeException('Failed to prepare sql query: '. $err);
        }

        $types='';
        $refParams=[];
        foreach($params as &$v){
            switch(true){
                case is_int($v):
                    $types.='i';
                    $refParams[]=&$v;
                    //$stmt->bind_param('i', $v);
                break;
    
                case is_float($v):
                    $types.='d';
                    $refParams[]=&$v;
                    //$stmt->bind_param('d', $v);
                break;
               
                case is_string($v):
                    $types.='s';
                    $refParams[]=&$v;
                    //$stmt->bind_param('s', $v);
                break;
                
                case strlen($v) > 65535:
                    $types.='b';
                    $refParams[]=&$v;
                    //$stmt->bind_param('b', $v);
                break;
            }
        }

        if(!empty($types)){
            //$stmt->bind_param($types, $v);
           
            call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $refParams));
        }
    
        $isOk=$stmt->execute();

        // Get last inserted id if any
        $insertId=0;

        if($isOk){
            $insertId=self::$conn->insert_id;
        }
        
        if($isOk && $stmt->field_count>0){
            $result = $stmt->get_result();

            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            $stmt->close();

            return $rows;
        }
  
        $stmt->close();

        // Return last inserted id if any, otherwise return isOk
        return ($insertId > 0)?$insertId:$isOk;
    }

    public static function SanitizeInParam($csv){
        if(is_string($csv)){
            $csv = explode(',', $csv);
        }
    
        $sanitaizedCSV = array_map(function($item){
            $item=trim($item);
            
            return is_numeric($item)?$item:self::$conn->Connection->quote($item);
        }, $csv);
    
        return implode(',', $sanitaizedCSV);
    }
}

require_once __DIR__ . '/dbConfigs.php';
