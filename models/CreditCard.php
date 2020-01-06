<?php
class CreditCard extends Model{
    private $credit_card_id;
    private $user_id;
    private $credit_card_type_id;
    private $name_on_card;
    private $card_number;
    private $expiration;
    private $cvv;

    public function __construct(Array $props=array()){
        $this->credit_card_id = abs($props['credit_card_id']??0);
        $this->user_id = abs($props['user_id']??0);
        $this->credit_card_type_id = abs($props['credit_card_type_id']??0);
        $this->name_on_card = $props['name_on_card']??'';
        $this->card_number = $props['card_number']??'';
        $this->expiration = $props['expiration']??'';
        $this->cvv = $props['cvv']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT c.credit_card_id, c.user_id, c.credit_card_type_id, ct.credit_card_type, c.name_on_card, c.card_number, DATE_FORMAT(c.expiration, '%Y-%m') AS expiration, c.cvv
            FROM credit_cards AS c
            INNER JOIN credit_card_types AS ct ON ct.credit_card_type_id = c.credit_card_type_id
            WHERE c.user_id = ?";   
             
        $args=[GetUserID()];

        if(!empty($params)){
            $sql .= " AND credit_card_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY expiration DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read credit cards',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadValid (Array $params=array()){
        $sql="SELECT c.credit_card_id, ct.credit_card_type, c.name_on_card, CONCAT(REPEAT('*', CHAR_LENGTH(c.card_number) - 4), SUBSTRING(c.card_number, -4)) AS card_number, DATE_FORMAT(c.expiration, '%Y-%m') AS expiration
            FROM credit_cards AS c
            INNER JOIN credit_card_types AS ct ON ct.credit_card_type_id = c.credit_card_type_id
            WHERE c.user_id = ? AND c.expiration > NOW()
            ORDER BY expiration DESC";
             
        $rows = $this->Query($sql, [GetUserID()]);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read credit cards',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $this->user_id = GetUserID();

        // Modifying month input from yyyy-mm to yyyy-mm-dd 
        if(!empty($this->expiration)){
            $this->expiration .= '-01';
        }

        $sql="INSERT INTO credit_cards(user_id, credit_card_type_id, name_on_card, card_number, expiration, cvv) VALUES(?, ?, ?, ?, ?, ?)";

        if($credit_card_id = $this->Query($sql, [
                $this->user_id,
                $this->credit_card_type_id,
                $this->name_on_card,
                $this->card_number,
                $this->expiration,
                $this->cvv
            ])
        ){
            // Return created record
            $res = $this->Read([$credit_card_id]);

            return new \Result(
                $res->data,
                "Credit Card added",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create credit card',
            'error',
            ''
        );
    }

    // Insert or Update based on card number
    public function Save(Array $params=array()){
        $sql = "SELECT credit_card_id FROM credit_cards WHERE card_number = ? LIMIT 1";
        
        $rows = $this->Query($sql, [$this->card_number]);

        if(!empty($rows)){
            $this->credit_card_id = $rows[0]['credit_card_id'];
            
            return $this->Update($params);
        }else{
            return $this->Create($params);
        }
    }

    public function Update(Array $params=array()){
        // Modifying month input from yyyy-mm to yyyy-mm-dd 
        if(!empty($this->expiration)){
            $this->expiration .= '-01';
        }

        $sql="UPDATE credit_cards
            SET
                credit_card_type_id = ?,
                name_on_card = ?,
                card_number = ?,
                expiration = ?,
                cvv = ?
            WHERE credit_card_id = ?";

        if($this->Query($sql, [
            $this->credit_card_type_id,
            $this->name_on_card,
            $this->card_number,
            $this->expiration,
            $this->cvv,
            $this->credit_card_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->credit_card_id]);

            return new \Result(
                $res->data,
                "Credit Card is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update credit card',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM credit_cards WHERE credit_card_id = ?";

        if($this->Query($sql, [
                $this->credit_card_id
            ])
        ){
            return new \Result(
                null,
                "Credit Card deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete credit card',
            'error',
            ''
        );
    }
}
?>