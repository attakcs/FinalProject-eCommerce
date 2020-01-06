<?php
class State extends Model{
    private $state_id;
    private $country_id;
    private $state;

    public function __construct(Array $props=array()){
        $this->state_id = abs($props['state_id']??0);
        $this->country_id = abs($props['country_id']??0);
        $this->state = $props['state']??'';
    }

    public function Read (Array $params=array()){
        $sql="SELECT state_id, country_id, state
            FROM states
            WHERE country_id = ?";
        
        $args=[$this->country_id];
        if(!empty($params)){
            $sql .= " AND state_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY state";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read states',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Create(Array $params=array()){
        $sql="INSERT INTO states(country_id, state) VALUES(?, ?)";

        if($state_id = $this->Query($sql, [
                $this->country_id,
                $this->state
            ])
        ){
            // Return created record
            $res = $this->Read([$state_id]);

            return new \Result(
                $res->data,
                "State created",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to create country_id',
            'error',
            ''
        );
    }

    public function Update(Array $params=array()){
        $sql="UPDATE states
            SET
                country_id = ?,
                state = ?
            WHERE state_id = ?";

        if($this->Query($sql, [
            $this->country_id,
            $this->state,
            $this->state_id
            ])
        ){
            // Return created record
            $res = $this->Read([$this->state_id]);

            return new \Result(
                $res->data,
                "State is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update country_id',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        $sql="DELETE FROM states WHERE state_id = ?";

        if($this->Query($sql, [
                $this->state_id
            ])
        ){
            return new \Result(
                null,
                "State deleted",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete country_id',
            'error',
            ''
        );
    }
}
?>