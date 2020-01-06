<?php
class Coupon extends Model{
    private $coupon_id;
    private $coupon;
    private $description;
    private $discount;
    private $start_date;
    private $end_date;

    public function __construct(Array $props=array()){
        $this->coupon_id = abs($props['coupon_id']??0);
        $this->coupon = $props['coupon']??'';
        $this->description = $props['description']??'';
        $this->discount = abs($props['discount']??0);
        $this->start_date = $props['start_date']??'';
        $this->end_date = $props['end_date']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT c.coupon_id, c.coupon, c.description, c.discount, c.start_date, c.end_date, (SELECT COUNT(*) FROM invoices WHERE coupon_id = c.coupon_id) AS uses
            FROM coupons AS c";   
             
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE coupon_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY end_date DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read coupons',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Redeem(Array $params=array()){
        $sql="SELECT coupon_id, coupon, description, discount
            FROM coupons
            WHERE coupon = ? AND NOW() BETWEEN start_date AND end_date LIMIT 1";   
        
        if(empty($this->coupon) && !empty($params)){
            $this->coupon = $params[0];
        }

        $rows = $this->Query($sql, [$this->coupon]);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read coupons',
                'error',
                ''
            );
        }

        if(empty($rows)){
            return new \Result(
                [],
                'Coupon code is not valid',
                'error',
                ''
            );
        }

        $this->coupon_id = $rows[0]['coupon_id'];

        // Make sure coupon is not used before by the same user
        $sql = "SELECT invoice_id FROM invoices WHERE user_id = ? AND coupon_id = ? LIMIT 1";
        $checkRows = $this->Query($sql, [GetUserID(), $this->coupon_id]);

        if($checkRows === false){
            return new \Result(
                [],
                'Failed to check coupon validity',
                'error',
                ''
            );
        }

        if(!empty($checkRows)){
            return new \Result(
                [],
                "This coupon code is already used by invoice #{$checkRows[0]['invoice_id']}",
                'error',
                ''
            );
        }

        return new \Result(
            $rows,
            "Congratulations you got a %{$rows[0]['discount']} off",
            'success'
        );
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO coupons(coupon, description, discount, start_date, end_date) VALUES(?, ?, ?, ?, ?)";

        if($coupon_id = $this->Query($sql, [
                $this->coupon,
                $this->description,
                $this->discount,
                $this->start_date,
                $this->end_date
            ])
        ){
            // Return created record
            $res = $this->Read([$coupon_id]);

            return new \Result(
                $res->data,
                "Coupon added",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create coupon',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE coupons
            SET
                coupon = ?,
                description = ?,
                discount = ?,
                start_date = ?,
                end_date = ?
            WHERE coupon_id = ?";

        if($this->Query($sql, [
            $this->coupon,
            $this->description,
            $this->discount,
            $this->start_date,
            $this->end_date,
            $this->coupon_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->coupon_id]);

            return new \Result(
                $res->data,
                "Coupon is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update coupon',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM coupons WHERE coupon_id = ?";

        if($this->Query($sql, [
                $this->coupon_id
            ])
        ){
            return new \Result(
                null,
                "Coupon deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete coupon',
            'error',
            ''
        );
    }
}
?>