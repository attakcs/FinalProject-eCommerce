<?php
require_once __DIR__.'/configs.php';

session_start();

spl_autoload_register(static function($modelName){
    $modelName=str_replace('\\', DIRECTORY_SEPARATOR, $modelName);

    require_once __DIR__ .'/../models/'.$modelName.'.php';
});

function GetURISegments(){
    $rp = parse_url($_GET['uri']??'')['path'];
    return explode('/', $rp);
}

function GetPageCode(){
    $pageCode = strtolower(GetURISegments()[0]);

    if(empty($pageCode)){
        $pageCode = 'home';
    }

    return $pageCode;
}

function Routing(){
    $pageCode = GetPageCode();

    $accessibility = CanAccess($pageCode);
    $canAccess = $accessibility['canAccess']??false;
    $redirectCode = $accessibility['redirectCode']??'';

    // Access denied page
    if(!$canAccess){
        if(!empty($redirectCode)){
            $pageCode = $redirectCode;
        }else{
            return __DIR__ .'/../pages/access-denied.php';
        }
    }

    // All API calls
    if($pageCode == 'api'){
       require_once __DIR__ .'/api.php';
       return false;
    }else{
        // Regular page
        $pageUrl = __DIR__ .'/../pages/'.$pageCode . '.php';
    }

    if(file_exists($pageUrl)){
        return $pageUrl;
    }else{
        // Page not found
        return __DIR__ .'/../pages/page-not-found.php';
    }
}

function IsLoggedIn(){
    return !empty($_SESSION['user']??'');
}

function IsAdmin(){
    return IsLoggedIn() && $_SESSION['user']['user_group'] == 'Administrator';
}

function IsCustomer(){
    return IsLoggedIn() && $_SESSION['user']['user_group'] == 'Customer';
}

function CanAccess($pageCode){
    $adminOnly = [
        'admin-panel',
        'category-manager',
        'coupon-manager',
        'credit-card-type-manager',
        'invoice-manager',
        'product-manager',
        'question-manager',
        'review-manager',
        'statistics',
        'user-manager'
    ];

    $customerOnly = [];

    $loginRequired = [
        'checkout',
        'my-credit-cards',
        'my-invoices',
        'profile',
        'view-order'
    ];

    if(!IsAdmin() && in_array($pageCode, $adminOnly, true)){
        return [
            'canAccess'=> false,
            'redirectCode'=> ''
        ];
    }

    if(!IsCustomer() && in_array($pageCode, $customerOnly, true)){
        return [
            'canAccess'=> false,
            'redirectCode'=> ''
        ];
    }

    if(!IsLoggedIn() && in_array($pageCode, $loginRequired, true)){
        $_SESSION['redirect_url'] = '/'.$_GET['uri']??'';
        return [
            'canAccess'=> false,
            'redirectCode'=> '/Login'
        ];
    }

    return [
        'canAccess'=> true,
        'redirectCode'=> ''
    ];
}

function QueryAllowed($model, $method){
    $adminOnlyMethods = [
        'Answer|Create',
        'Answer|Update',
        'Answer|Delete',
        'Category|Create',
        'Category|Update',
        'Category|Delete',
        'Country|Create',
        'Country|Update',
        'Country|Delete',
        'Coupon|Read',
        'Coupon|Create',
        'Coupon|Update',
        'Coupon|Delete',
        'CreditCardType|Create',
        'CreditCardType|Update',
        'CreditCardType|Delete',
        'Invoice|Read',
        'Invoice|Update',
        'Invoice|UpdateStatus',
        'Invoice|Delete',
        'Product|Create',
        'Product|Update',
        'Product|Delete',
        'Question|Read',
        'Question|Update',
        'Question|Delete',
        'Review|Read',
        'Review|Update',
        'Review|Delete',
        'State|Create',
        'State|Update',
        'State|Delete',
        'Statistics|AdminPanel',
        'Statistics|LoyalCustomers',
        'Statistics|MonthlyIncome',
        'Statistics|MonthlyInvoices',
        'User|Read',
        'User|Create',
        'User|Update',
        'User|Delete',
    ];

    $loggedInOnlyMethods = [
        'Coupon|Redeem',
        'CreditCard|Read',
        'CreditCard|ReadValid',
        'CreditCard|Create',
        'CreditCard|Save',
        'CreditCard|Update',
        'CreditCard|Delete',
        'CreditCardType|Read',
        'CreditCardType|ReadAvailable',
        'Invoice|ReadMyInvoices',
        'Invoice|ViewOrder',
        'Invoice|Create',
        'Question|Create',
        'Review|Create',
        'State|Read',
        'User|Profile',
        'User|UpdateProfile',
        'User|DeleteProfile',
        'User|Logout',
    ];
    
    if(!IsAdmin() && in_array("$model|$method", $adminOnlyMethods, true)){
        return false;
    }

    if(!IsLoggedIn() && in_array("$model|$method", $loggedInOnlyMethods, true)){
        return false;
    }


    return true;
}

function GetRedirectURL(){
    $redirect = $_SESSION['redirect_url']??'';
    if(!empty($redirect)){
        unset($_SESSION['redirect_url']);
    }

    return $redirect;
}

function SetUser($userInfo){
    $_SESSION['user'] = $userInfo;
}

function GetUser($prop){
    if(!IsLoggedIn()){
        return '';
    }

    return $_SESSION['user'][$prop]??'';
}

function GetUserID(){
    if(!empty($_SESSION['user']??'')){
        return $_SESSION['user']['user_id'];
    }

    return 0;
}

function GetUserGroup(){
    if(!IsLoggedIn()){
        return 'Guest';
    }

    return $_SESSION['user']['user_group'];
}

function GetUserName(){
    if(!IsLoggedIn()){
        return '';
    }

    return "{$_SESSION['user']['first_name']} {$_SESSION['user']['last_name']}";
}

function GetUserPhoto(){
    if(!IsLoggedIn()){
        return 0;
    }

    return $_SESSION['user']['photo'];
}

function SendResponse($result){
    if(headers_sent()){
        echo '<pre>';
        if($result instanceof \Result){
            echo $result->messageType, '\n', $result->message, '\n', json_encode($result->data);
        }else{
            echo json_encode($result);
        }
        echo '</pre>';
    }else{
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    exit();
}

function SizeInBytes($val) {
	$val=trim($val);
	$unit=mb_strtolower($val[strlen($val) - 1]);
	$val= (int)$val;
	
	switch($unit){
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return $val;
}

function uploadFile($inputName, $uploadDir='.', $fileName='', $maxSize=null, $fileTypes=[]){
	if(is_null($maxSize)){
        $maxSize = ini_get('upload max filesize');
    }

	// When POST is Empty this is an indecation exceeding the upload_max_filesize
	if(empty($_POST) || $_FILES[$inputName]['size'] > SizeInBytes($maxSize)){
		return [
            'success'=> false,
            'message'=> 'File size exceeds upload limits'
        ];
	}
	
	$file=$_FILES[$inputName];

	if(empty($file) || !is_uploaded_file($file['tmp_name'])){
		return [
            'success'=> false,
            'message'=> 'File not recognized'
        ];
	}
	
	if($file['error']>0){
		return [
            'success'=> false,
            'message'=> $file['error']
        ];
	}
	
	if(!empty($fileTypes) && !in_array($file['type'], $fileTypes, true)){
 		return [
            'success'=> false,
            'message'=> 'Unsupported file type'
        ];
	}

    $uploadDir=str_replace('\\' , DIRECTORY_SEPARATOR, $uploadDir);
    
	if(substr($uploadDir, -1, 1) != DIRECTORY_SEPARATOR){
        $uploadDir .= DIRECTORY_SEPARATOR;
    }

	if(!file_exists($uploadDir)){
		return [
            'success'=> false,
            'message'=> 'Cant find upload directory'
        ];
	}
		
	if(empty($fileName)){
        $fileName = $file['name'];
    }

	$fileName = str_replace(' ', '_', $fileName);
	
    $isMoved = move_uploaded_file($file['tmp_name'], $uploadDir.$fileName);
    
	if($isMoved === false){
		return [
            'success'=> false,
            'message'=> 'Cant save uploaded file'
        ];
	}

	return [
        'success'=> true,
        'message'=> '',
        'uploadDir'=> $uploadDir,
        'uploadName'=> $fileName,
        'fileType'=> $file['type'],
        'fileSize'=> $file['size']
    ];
}

function DeleteFile($path){
    if(empty($path)){
        return [
            'success'=> false,
            'message'=> 'File path not set'
        ];
    }

    if(!file_exists($path)){
        return [
            'success'=> false,
            'message'=> 'File path not exists'
        ];
    }

    try{
        unlink($path);

        return [
            'success'=> true,
            'message'=> 'File deleted'
        ];
    }catch(Exception $ex){
        return [
            'success'=> false,
            'message'=> $ex->getMessage()
        ];
    }
}

function LoadTemplate($template, $params){
    $generalParams = [
        'WEBSITE_TITLE' =>  WEBSITE_TITLE,
        'WEBSITE_URL' =>    WEBSITE_URL,
        'CURRENCY' =>       CURRENCY,
        'VAT_PERCENTAGE' => VAT_PERCENTAGE,
        'SUPPORT_EMAIL' =>  SUPPORT_EMAIL,
        'SUPPORT_PHONE' =>  SUPPORT_PHONE,
        'COPYRIGHT' =>      COPYRIGHT
    ];

    // Merge template specific params with general params that might be used in the temlate
    $params = array_merge($generalParams, $params);

     require_once __DIR__ . '/template.php';

     $templatePath = __DIR__ . '/../templates/'.$template.'.html';

     return RenderTemplate($templatePath, $params);
}

function SendTemplateEmail($to, $subject, $template, $params){
    require_once __DIR__ . '/mailer.php';

    $params['EMAIL_SUBJECT'] = WEBSITE_TITLE . ' - '. $subject;
    $params['DATE_SENT'] = date('Y-m-d H:i');
    
    $body= LoadTemplate($template, $params);

    if($body === null){
        return false;
    }

    $subject = $params['EMAIL_SUBJECT'];
    $from = SUPPORT_EMAIL;
    $replyTo = SUPPORT_EMAIL;

    return SendMail($to, $subject, $body, $from, $replyTo);
}