<?php
class Mailer extends Model{
    public function __construct(Array $props=[]){
        $this->subject = $props['subject']??'No Subject';
        $this->message = $props['message']??'';
        $this->params = json_decode($props['params']??'[]', true);
    }

    public function Send(){
        $this->params = array_merge($this->params, ['message' => $this->message]);
        $isSent = SendTemplateEmail(
            SUPPORT_EMAIL,
            $this->subject,
            'general',
            $this->params
        );

        if($isSent){
            return new \Result(
                null,
                "Thank you for contacting us\nWe'll contact you back soon",
                'success',
                'home'
            );
        }else{
            return new \Result(
                null,
                "Sorry,\nIt seems our mail server is down right now,\n\nThank you for contacting us anyways",
                'error',
                'home'
            );
        }
    }
}
?>