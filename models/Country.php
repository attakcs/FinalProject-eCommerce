<?php
class Country extends Model{
    private $country_id;
    private $country;

    public function __construct(Array $props=array()){
        $this->country_id = abs($props['country_id']??0);
        $this->country = $props['country']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT country_id, country
            FROM countries";
        
        $args=[];
        if(!empty($params)){
            $sql .= " WHERE country_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY country";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read countries',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO countries(country) VALUES(?)";

        if($country_id = $this->Query($sql, [
                $this->country
            ])
        ){
            // Return created record
            $res = $this->Read([$country_id]);

            return new \Result(
                $res->data,
                "Country created",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create country',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE countries
            SET
                country = ?
            WHERE country_id = ?";

        if($this->Query($sql, [
            $this->country,
            $this->country_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->country_id]);

            return new \Result(
                $res->data,
                "Country is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update country',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM countries WHERE country_id = ?";

        if($this->Query($sql, [
                $this->country_id
            ])
        ){
            return new \Result(
                null,
                "Country deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete country',
            'error',
            ''
        );
    }
}
?>