<?php
function SendMail($to, $subject, $body, $from = '', $replyTo = '', $isHtml = true){
    // A new line character followed by a period represents the end of the message, so we add another period to it
    $body = str_replace("\n.", "\n..", $body);
    // Mime needs 70 character per line
    // $body = wordwrap($body,70, "\n", true);

    $headers = [];
    if(empty($from)){
        $from = ini_get('sendmail_from');
    }
    
    $headers[] = "FROM: $from";

    if(empty($replyTo)){
        $replyTo = $from;
    }

    $headers[] = "Reply-To: $replyTo";

    if($isHtml){
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type:text/html;charset=UTF-8";
    }  

    $isAccepted = @mail(
        $to,
        $subject,
        $body,
        implode("\r\n", $headers)
    );

    return $isAccepted;
}
?>