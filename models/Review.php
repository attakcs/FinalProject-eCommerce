<?php
class Review extends Model{
    private $review_id;
    private $product_id;
    private $user_id;
    private $review;
    private $stars;
    private $status;
    private $data_added;

    public function __construct(Array $props=array()){
        $this->review_id = $props['review_id']??0;
        $this->product_id = abs($props['product_id']??0);
        $this->user_id = abs($props['user_id']??0);
        $this->review = $props['review']??'';
        $this->stars = abs($props['stars']??0);
        $this->status = $props['status']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT r.review_id, r.product_id, p.product, r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, u.email, u.photo, r.review, r.stars, r.status, r.date_added
            FROM reviews AS r
            INNER JOIN products AS p ON p.product_id = r.product_id
            INNER JOIN users AS u ON u.user_id = r.user_id";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE r.review_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY r.date_added DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read reviews',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadApproved(Array $params=array()){
        $sql="SELECT r.review_id, r.product_id, p.product, r.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, u.email, u.photo, r.review, r.stars, r.status, r.date_added
            FROM reviews AS r
            INNER JOIN products AS p ON p.product_id = r.product_id
            INNER JOIN users AS u ON u.user_id = r.user_id
            WHERE r.product_id = ? AND r.status = 'Approved'
            ORDER BY r.date_added DESC";
        
        if($this->product_id == 0 && !empty($params)){
            $this->product_id = (int)$params[0];
        }

        $rows = $this->Query($sql, [$this->product_id]);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read reviews',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO reviews(product_id, user_id, review, stars, status, date_added) VALUES(?, ?, ?, ?, ?, ?)";

        $this->user_id = GetUserID();
        $this->status = 'Pending';

        if($review_id = $this->Query($sql, [
                $this->product_id,
                $this->user_id,
                $this->review,
                $this->stars,
                $this->status,
                date('Y-m-d H:i'),
            ])
        ){
            // Return created record
            $res = $this->Read([$review_id]);

            return new \Result(
                $res->data,
                "Review added\nIt will show up once approved by admin\nThanks",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create review',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE reviews
            SET
                review = ?,
                stars = ?,
                status = ?
            WHERE review_id = ?";

        if($this->Query($sql, [
            $this->review,
            $this->stars,
            $this->status,
            $this->review_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->review_id]);

            return new \Result(
                $res->data,
                "Review is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update review',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM reviews WHERE review_id = ?";

        if($this->Query($sql, [
                $this->review_id
            ])
        ){
            return new \Result(
                null,
                "Review deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete review',
            'error',
            ''
        );
    }
}
?>