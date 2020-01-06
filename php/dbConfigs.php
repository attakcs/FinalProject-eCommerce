<?php

try{
    DB::Connect('localhost', 'root', '', 'ecommerce');
}catch(Exception $ex){
    $result = New \Result(null, $ex->getMessage(), 'exception');
    \SendResponse($result);
}
