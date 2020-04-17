<?php

try{
    DB::Connect('localhost', 'root', '', 'ecommerce_db');
}catch(Exception $ex){
    $result = New \Result(null, $ex->getMessage(), 'exception');
    \SendResponse($result);
}
