<?php
class Question extends Model{
    private $question_id;
    private $product_id;
    private $user_id;
    private $question;
    private $description;
    private $status;
    private $data_added;

    public function __construct(Array $props=array()){
        $this->question_id = $props['question_id']??0;
        $this->product_id = abs($props['product_id']??0);
        $this->user_id = abs($props['user_id']??0);
        $this->question = $props['question']??'';
        $this->description = $props['description']??'';
        $this->status = $props['status']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT q.question_id, q.product_id, p.product, q.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, u.email, u.photo, q.question, q.description, q.status, q.date_added
            FROM questions AS q
            INNER JOIN products AS p ON p.product_id = q.product_id
            INNER JOIN users AS u ON u.user_id = q.user_id";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE q.question_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY q.date_added DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read questions',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadApproved(Array $params=array()){
        $sql="SELECT q.question_id, q.product_id, p.product, q.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, u.email, u.photo, q.question, q.description, q.status, q.date_added,
                (SELECT COUNT(*) FROM answers WHERE question_id = q.question_id) AS answers
            FROM questions AS q
            INNER JOIN products AS p ON p.product_id = q.product_id
            INNER JOIN users AS u ON u.user_id = q.user_id
            WHERE q.product_id = ? AND q.status = 'Approved'
            ORDER BY q.date_added DESC";
        
        if($this->product_id == 0 && !empty($params)){
            $this->product_id = (int)$params[0];
        }

        $rows = $this->Query($sql, [$this->product_id]);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read questions',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadAnswers(Array $params=array()){
        $sql="SELECT a.answer_id, a.question_id, a.user_id, CONCAT(u.first_name, ' ', u.last_name) AS user, u.email, u.photo, a.answer, a.date_added
            FROM answers AS a
            INNER JOIN users AS u ON u.user_id = a.user_id
            WHERE a.question_id = ?
            ORDER BY a.date_added DESC";

        if($this->question_id == 0 && !empty($params)){
            $this->question_id = (int)$params[0];
        }

        $rows = $this->Query($sql, [$this->question_id]);

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
        $sql="INSERT INTO questions(product_id, user_id, question, description, status, date_added) VALUES(?, ?, ?, ?, ?, ?)";

        $this->user_id = GetUserID();
        $this->status = 'Pending';

        if($question_id = $this->Query($sql, [
                $this->product_id,
                $this->user_id,
                $this->question,
                $this->description,
                $this->status,
                date('Y-m-d H:i'),
            ])
        ){
            // Return created record
            $res = $this->Read([$question_id]);

            return new \Result(
                $res->data,
                "Question added\nIt will show up once approved by admin\nThanks",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create question',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE questions
            SET
                question = ?,
                description = ?,
                status = ?
            WHERE question_id = ?";

        if($this->Query($sql, [
            $this->question,
            $this->description,
            $this->status,
            $this->question_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->question_id]);

            return new \Result(
                $res->data,
                "Question is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update question',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM questions WHERE question_id = ?";

        if($this->Query($sql, [
                $this->question_id
            ])
        ){
            return new \Result(
                null,
                "Question deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete question',
            'error',
            ''
        );
    }
}
?>