<?php
class Category extends Model{
    private $category_id;
    private $category;
    private $display_order;

    public function __construct(Array $props=array()){
        $this->category_id = $props['category_id']??0;
        $this->category = $props['category']??'';
        $this->display_order = abs($props['display_order']??0);
    }

    public function Read (Array $params=array()){
        $sql="SELECT category_id, category, display_order
            FROM categories";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE category_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY display_order";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read categories',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO categories(category, display_order) VALUES(?, ?)";

        if($category_id = $this->Query($sql, [
                $this->category,
                $this->display_order
            ])
        ){
            // Return created record
            $res = $this->Read([$category_id]);

            return new \Result(
                $res->data,
                "Category created",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create category',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE categories
            SET
                category = ?,
                display_order = ?
            WHERE category_id = ?";

        if($this->Query($sql, [
            $this->category,
            $this->display_order,
            $this->category_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->category_id]);

            return new \Result(
                $res->data,
                "Category is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update category',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM categories WHERE category_id = ?";

        if($this->Query($sql, [
                $this->category_id
            ])
        ){
            return new \Result(
                null,
                "Category deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete category',
            'error',
            ''
        );
    }
}
?>