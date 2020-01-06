<?php
$URISegments = GetURISegments();
$model = $URISegments[1]??'';
$method = $URISegments[2]??'';

if(!QueryAllowed($model, $method)){
    $result = new \Result(null, 'Query not allowed', 'error');
    sendResponse($result);
}

if(class_exists($model)){
    // Get json string from posted data variable, or fallback to all post data
    $data = $_POST['data']??'';
    if(!empty($data)){
        $data = json_decode($data, true);

        if(json_last_error()!==JSON_ERROR_NONE){
            $result = new \Result(null, 'Json decode error', 'error');
            sendResponse($result);
		}
    }else{
        $data = $_POST;
    }

    // Instantiate model with POST params
    $obj = new $model($data);
}else{
    $result = new \Result(null, "Model $model doesn't exist", 'error');
    sendResponse($result);
}

if(!method_exists($obj, $method)){
    $result = new \Result(null, "Method $model::$method doesn't exist", 'error');
    sendResponse($result);
}

// Call Model's method and pass all URI segments as params
try{
    $result = $obj->$method(array_slice($URISegments, 3));
    // Set server message / Redirect 
    if($result instanceof \Result){
        sendResponse($result);
    }else{
       echo $result;
    }
    
}catch(Exception $ex){
    $result = new \Result(null, $ex->getMessage(), 'exception');
    sendResponse($result);
}
