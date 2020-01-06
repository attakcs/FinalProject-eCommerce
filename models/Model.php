<?php
require_once __DIR__ .'/../php/db.php';

abstract class Model{
    protected function Query($sql, Array $params=[]){
        return \DB::Query($sql, $params);
    }

    protected function SanitizeInParam($csv){
        return \DB::SanitizeInParam($csv);
    }
}
