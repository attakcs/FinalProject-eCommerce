<?php
class Product extends Model{
    private $product_id;
    private $category_id;
    private $product;
    private $brief;
    private $description;
    private $price;
    private $quantity;
    private $status;
    private $image;
    private $data_added;

    public function __construct(Array $props=array()){
        $this->product_id = $props['product_id']??0;
        $this->category_id = abs($props['category_id']??0);
        $this->product = $props['product']??'';
        $this->brief = $props['brief']??'';
        $this->description = $props['description']??'';
        $this->price = abs($props['price']??0.0);
        $this->quantity = abs($props['quantity']??0.0);
        $this->status = $props['status']??'';
        $this->image = $props['image']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT p.product_id, p.category_id, c.category, p.product, p.brief, p.description, p.price, p.quantity, p.status, p.image, p.date_added
            FROM products AS p
            INNER JOIN categories AS c ON c.category_id = p.category_id";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE p.product_id = ?";
            $args[] = (int)$params[0];
        }
        
        // Order by status first, Shortage at top most then Available and finally Not_available
        $sql .= " ORDER BY FIND_IN_SET(p.status, 'Shortage,Available,Not_available'), c.display_order DESC, p.product";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read products',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadAvailable(Array $params=array()){
        $sql="SELECT p.product_id, p.category_id, c.category, p.product, p.brief, p.description, p.price, p.quantity, p.status, p.image, p.date_added
            FROM products AS p
            INNER JOIN categories AS c ON c.category_id = p.category_id
            WHERE status = 'Available'";

        $args=[];
        if(!empty($params) && !empty($params[0])){
            $sql .= " AND concat(c.category, ' ', p.product, ' ', p.brief) like ?";
            $args[]='%'.$params[0].'%';
        }

        $sql .=" ORDER BY c.display_order DESC, p.product";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read products',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadMulti(Array $params=array()){
        $productIDs = 0;
        if(!empty($params)){
            $productIDs = $this->SanitizeInParam($params[0]);
        }

        $sql="SELECT p.product_id, c.category, p.product, p.brief, p.price, p.quantity AS store_quantity, p.status, p.image
            FROM products AS p
            INNER JOIN categories AS c ON c.category_id = p.category_id
            WHERE p.product_id IN($productIDs)
            ORDER BY c.display_order DESC, p.product";

        $rows = $this->Query($sql, []);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read products',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        // Product image upload
        $msg = $this->HandleFileUpload();
        if($msg !== true){
            return new \Result(
                null,
                $msg,
                'warning',
                ''
            );
        }

        $sql="INSERT INTO products(category_id, product, brief, description, price, quantity, status, image, date_added) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($this->quantity <= 0 && $this->status != 'Not_Available'){
            $this->status = 'Shortage';
            
        }elseif($this->quantity > 0 && $this->status == 'Shortage'){
            $this->status = 'Available';
        }

        if($product_id = $this->Query($sql, [
                $this->category_id,
                $this->product,
                $this->brief,
                $this->description,
                $this->price,
                $this->quantity,
                $this->status,
                $this->image,
                date('Y-m-d H:i'),
            ])
        ){
            // Return created record
            $res = $this->Read([$product_id]);

            return new \Result(
                $res->data,
                "Product created",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create product',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        // Product image upload
        $msg = $this->HandleFileUpload();
        if($msg !== true){
            return new \Result(
                null,
                $msg,
                'warning',
                ''
            );
        }

        // Delete old image
        // Get product image file name in order to delete it
        if(!empty($this->image)){
            $sql= 'SELECT image FROM products WHERE product_id = ?';
            $rows = $this->Query($sql, [$this->product_id]);
            
            if(!empty($rows)){
                DeleteFile(PRODUCT_PHOTOS_DIR.'/'. $rows[0]['image']);
            }
        }

        $sql="UPDATE products
        SET
            category_id = ?,
            product = ?,
            brief = ?,
            description = ?,
            price = ?,
            quantity = ?,
            status = ?";

        if($this->quantity <= 0 && $this->status != 'Not_Available'){
            $this->status = 'Shortage';

        }elseif($this->quantity > 0 && $this->status == 'Shortage'){
            $this->status = 'Available';
        }

        $dbParams = [
            $this->category_id,
            $this->product,
            $this->brief,
            $this->description,
            $this->price,
            $this->quantity,
            $this->status
        ];

        // Update image if new one uploaded
        if(!empty($this->image)){
            $sql.=",
                image = ?";

            $dbParams[] = $this->image;
        }

        $sql.= " WHERE product_id = ?";
        $dbParams[] = $this->product_id;
        
        if($this->Query($sql, $dbParams)
        ){
            // Return created record
            $res = $this->Read([$this->product_id]);

            return new \Result(
                $res->data,
                "Product is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update product',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        // Get product image file name in order to delete it
        $sql= 'SELECT image FROM products WHERE product_id = ?';
        $rows = $this->Query($sql, [$this->product_id]);

        if(!empty($rows)){
            $this->image = $rows[0]['image'];
        }

        $sql="DELETE FROM products WHERE product_id = ?";

        if($this->Query($sql, [
                $this->product_id
            ])
        ){
            if(!empty($this->image)){
                DeleteFile(PRODUCT_PHOTOS_DIR.'/'.$this->image);
            }

            return new \Result(
                null,
                "Product deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete product',
            'error',
            ''
        );
    }

    public function Reviews(Array $params=array()){
        if(!empty($params) && $this->product_id == 0){
            $this->product_id = (int)$params[0];
        }

        $objReview = new \Review(['product_id'=>$this->product_id]);

        return $objReview->ReadApproved([]);
    }

    
    public function Questions(Array $params=array()){
        if(!empty($params) && $this->product_id == 0){
            $this->product_id = (int)$params[0];
        }

        $objQuestion = new \Question(['product_id'=>$this->product_id]);

        return $objQuestion->ReadApproved([]);
    }


    private function HandleFileUpload(){
        if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
            $fileName = time().''.mt_rand(100, 999);

            $u=uploadFile('image', PRODUCT_PHOTOS_DIR, $fileName, MAX_UPLOAD_SIZE, ['image/jpeg', 'image/gif', 'image/png']);
        
            if(!$u['success']){
                return $u['message'];
            }
            
            $this->image = $u['uploadName'];
        }

        return true;
    }

    
    public function Image(Array $params=[]){
        $this->image = $params[0];

        if(empty($this->image)){
            $this->image = 0;
        }
        
        if(!file_exists(PRODUCT_PHOTOS_DIR .'/'.$this->image)){
            $this->image = 'product-default.png';
        }

        header('Content-Type: image/png');
        readfile(PRODUCT_PHOTOS_DIR .'/'.$this->image);

        return null;
    }
}
?>