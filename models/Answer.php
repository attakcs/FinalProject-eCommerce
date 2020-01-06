<?php
class Answer extends Model{
    private $answer_id;
    private $question_id;
    private $user_id;
    private $answer;
    private $status;
    private $data_added;

    public function __construct(Array $props=array()){
        $this->answer_id = $props['answer_id']??0;
        $this->question_id = abs($props['question_id']??0);
        $this->user_id = abs($props['user_id']??0);
        $this->answer = $props['answer']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT a.answer_id, a.question_id, a.user_id, CONCAT(u.first_name, ' ', u.last_name) AS user, u.email, u.photo, a.answer, a.date_added
            FROM answers AS a
            INNER JOIN questions AS q ON q.question_id = a.question_id
            INNER JOIN users AS u ON u.user_id = a.user_id";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE a.answer_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY a.date_added DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read answers',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO answers(question_id, user_id, answer, date_added) VALUES(?, ?, ?, ?)";

        $this->user_id = GetUserID();
        $this->status = 'Pending';

        if($answer_id = $this->Query($sql, [
                $this->question_id,
                $this->user_id,
                $this->answer,
                date('Y-m-d H:i'),
            ])
        ){
            // Return created record
            $res = $this->Read([$answer_id]);

            return new \Result(
                $res->data,
                "Answer added",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create answer',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE answers
            SET
                answer = ?
            WHERE answer_id = ?";

        if($this->Query($sql, [
            $this->answer,
            $this->answer_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->answer_id]);

            return new \Result(
                $res->data,
                "Answer is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update answer',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM answers WHERE answer_id = ?";

        if($this->Query($sql, [
                $this->answer_id
            ])
        ){
            return new \Result(
                null,
                "Answer deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete answer',
            'error',
            ''
        );
    }
}
?>