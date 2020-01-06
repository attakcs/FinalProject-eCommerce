<?php
class Result{
    public $data;
    public $message;
    public $messageType;
    public $redirect;

    public function __construct($data = null, $message = '', $messageType = 'info', $redirect = ''){
        $this->data = $data;
        $this->message = $message;
        $this->messageType = $messageType;
        $this->redirect = $redirect;
    }
}
