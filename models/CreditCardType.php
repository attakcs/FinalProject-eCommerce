<?php
class CreditCardType extends Model{
    private $credit_card_type_id;
    private $credit_card_type;
    private $display_order;
    private $status;

    public function __construct(Array $props=array()){
        $this->credit_card_type_id = $props['credit_card_type_id']??0;
        $this->credit_card_type = $props['credit_card_type']??'';
        $this->display_order = abs($props['display_order']??0);
        $this->status = $props['status']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT credit_card_type_id, credit_card_type, display_order, status
            FROM credit_card_types";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE credit_card_type_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY display_order, credit_card_type";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read credit card types',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadAvailable (Array $params=array()){
        $sql="SELECT credit_card_type_id, credit_card_type, display_order, status
            FROM credit_card_types
            WHERE status = 'Enabled'
            ORDER BY display_order, credit_card_type";

        $rows = $this->Query($sql, []);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read credit card types',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO credit_card_types(credit_card_type, display_order, status) VALUES(?, ?, ?)";

        if($credit_card_type_id = $this->Query($sql, [
                $this->credit_card_type,
                $this->display_order,
                $this->status
            ])
        ){
            // Return created record
            $res = $this->Read([$credit_card_type_id]);

            return new \Result(
                $res->data,
                "Credit Card Type created",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create credit_card_type',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE credit_card_types
            SET
                credit_card_type = ?,
                display_order = ?,
                status = ?
            WHERE credit_card_type_id = ?";

        if($this->Query($sql, [
            $this->credit_card_type,
            $this->display_order,
            $this->status,
            $this->credit_card_type_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->credit_card_type_id]);

            return new \Result(
                $res->data,
                "Credit Card Type is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update credit_card_type',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM credit_card_types WHERE credit_card_type_id = ?";

        if($this->Query($sql, [
                $this->credit_card_type_id
            ])
        ){
            return new \Result(
                null,
                "Credit Card Type deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete credit card type',
            'error',
            ''
        );
    }
}
?>