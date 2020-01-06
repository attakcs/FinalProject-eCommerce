<?php
class User extends Model{
    private $user_id;
    private $first_name;
    private $last_name;
    private $email;
    private $password;
    private $user_group = 'Customer'; //Customer, Administrator
    private $photo;
    private $address = '';
    private $address2 = '';
    private $state_id  = 0;
    private $zip = '';
    private $status = 'Active'; //Active, Banned
    private $date_registered;

    public function __construct(Array $props=[]){
        if(!isAdmin()){
            $props['user_id'] = GetUserID();
        }

        if(!isAdmin()){
            $this->user_group = GetUserGroup();
        }

        $this->user_id = $props['user_id']??GetUserID();
        $this->first_name = $props['first_name']??'';
        $this->last_name = $props['last_name']??'';
        $this->email = $props['email']??'';
        $this->password = hash('sha256', $props['password']??'');
        $this->user_group = $props['user_group']??'Customer';
        $this->photo = $props['photo']??'';
        $this->address = $props['address']??'';
        $this->address2 = $props['address2']??'';
        $this->state_id = abs($props['state_id']??0);
        $this->zip = $props['zip']??'';
        $this->status = $props['status']??'Active';
    }

    public function Read (Array $params=[]){
        $sql= /** @lang text */
            "SELECT u.user_id, u.first_name, u.last_name, u.email, u.photo, u.address, u.address2, c.country_id, IFNULL(c.country, '') AS country, u.state_id, IFNULL(s.state, '') AS state, u.zip, u.user_group, u.date_registered, u.status
            FROM users AS u
            LEFT JOIN states AS s ON s.state_id = u.state_id
            LEFT JOIN countries AS c ON c.country_id = s.country_id";
        
        $args=[];
        if(!empty($params)){
            $sql .= ' WHERE user_id = ?';
            $args[] = (int)$params[0];
        }

        $sql .= " ORDER BY date_registered DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                null,
                'Failed to read users',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function Profile (Array $params=[]){
        return $this->Read([GetUserID()]);
    }

    public function Create(Array $params=[]){
        $sql= 'SELECT 1 FROM users WHERE email = ?';
        
        if($this->Query($sql, [
            $this->email
        ])){
            return new \Result(
                null,
                'User already exists',
                'error',
                ''
            );
        }

        $sql= 'INSERT INTO users(first_name, last_name, email, password, photo, address, address2, state_id, zip, user_group, date_registered, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        if($user_id = $this->Query($sql, [
            $this->first_name,
            $this->last_name,
            $this->email,
            $this->password,
            $this->photo,
            $this->address,
            $this->address2,
            $this->state_id,
            $this->zip,
            $this->user_group,
            date('Y-m-d H:i'),
            $this->status
        ])
        ){
            // Return created record
            $res = $this->Read([$user_id]);
            
            return new \Result(
                $res->data,
                'User created',
                'success',
                '/Login'
            );
        }
        
        return new \Result(
            null,
            'Failed to create user',
            'error',
            ''
        );
    }

    public function Register(Array $params=[]){
        $this->user_group = 'Customer';
        
        // return $this->Create($params);
        $result = $this->Create($params);
        if($result->messageType == 'error'){
            return $result;
        }

        return $this->Login($params);
    }

    public function Update(Array $params=[]){
        $sql= 'UPDATE users
            SET
                first_name = ?,
                last_name = ?,
                email = ?,';
        
        $dbParams = [
            $this->first_name,
            $this->last_name,
            $this->email
        ];

        // Don't update password if sent empty
        if($this->password != hash('sha256', '')){           
            $sql.= 'password = ?,';

            $dbParams[] = $this->password;
        }

        // Update photo if new one uploaded
        if(!empty($this->photo)){           
            $sql.= 'photo = ?,';

            $dbParams[] = $this->photo;
        }

        $sql .= '
                address = ?,
                address2 = ?,
                state_id = ?,
                zip = ?';

        $dbParams[] = $this->address;
        $dbParams[] = $this->address2;
        $dbParams[] = $this->state_id;
        $dbParams[] = $this->zip;

        if(IsAdmin()){
            $sql .= ',
                user_group = ?,
                status = ?';
            $dbParams[] = $this->user_group;
            $dbParams[] = $this->status;
        }

        $sql .=" WHERE user_id = ?";
        $dbParams[] = $this->user_id;

        if($this->Query($sql, $dbParams)
        ){
            // Return created record
            $res = $this->Read([$this->user_id]);

            return new \Result(
                $res->data,
                'User updated',
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update user',
            'error',
            ''
        );
    }

    public function UpdateProfile(Array $params=[]){
        $this->user_id = GetUserID();
        $this->user_group = GetUserGroup();

        // Profile photo upload
        $msg = $this->HandleFileUpload();
        if($msg !== true){
            return new \Result(
                null,
                $msg,
                'warning',
                ''
            );
        }

        $result = $this->Update($params);
        
        if($result->messageType != 'error'){
            // Delete old photo
            if(!empty($this->photo)){
                DeleteFile(PROFILE_PHOTOS_DIR.'/'.GetUserPhoto());
            }

            SetUser($result->data[0]);
        }
        
        return $result;
    }

    public function Delete(Array $params=[]){
        // Get user photo file name in order to delete it
        $sql= 'SELECT photo FROM users WHERE user_id = ?';
        $rows = $this->Query($sql, [$this->user_id]);

        if(!empty($rows)){
            $this->photo = $rows[0]['photo'];
        }

        // If user has related ionvoices, we'll keep the minimal amout of data for accounting purposes only
        $sql = "SELECT 1 FROM invoices WHERE user_id = ? LIMIT 1";
        $rows = $this->Query($sql, [$this->user_id]);

        // Crear user data instead of deleting the account
        if(!empty($rows)){
            $this->Query("DELETE FROM answers WHERE user_id = ?", [$this->user_id]);
            $this->Query("DELETE FROM questions WHERE user_id = ?", [$this->user_id]);
            $this->Query("DELETE FROM credit_cards WHERE user_id = ?", [$this->user_id]);
            $this->Query("DELETE FROM reviews WHERE user_id = ?", [$this->user_id]);
            $this->Query("UPDATE users
                    SET email = '',
                        password = '',
                        photo = '',
                        address = '',
                        address2 = '',
                        state_id = 0,
                        zip = '',
                        status = 'Deleted'
                WHERE user_id = ?" , [$this->user_id]);

            return new \Result(
                null,
                'User is cleared',
                'success',
                ''
            );
        }

        $sql= 'DELETE FROM users WHERE user_id = ?';

        if($this->Query($sql, [
            $this->user_id
        ])
        ){
            if(!empty($this->photo)){
                DeleteFile(PROFILE_PHOTOS_DIR.'/'.$this->photo);
            }
            
            return new \Result(
                null,
                'User is deleted',
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to delete user',
            'error',
            ''
        );
    }

    public function DeleteProfile(Array $params=[]){
        if(IsAdmin()){
            return new \Result(
                null,
                'Administrator account can be deleted from Admin Panel',
                'error',
                ''
            );
        }

        $this->user_id = GetUserID();

        $result = $this->Delete($params);

        if($result->messageType == 'error'){
            return $result;
        }

        $userName = GetUserName();
        $this->Logout();

        return new \Result(
            null,
            "Your account deleted successfully,\nWe're sorry for losing you ".$userName,
            'success',
            '/Home'
        );
    }

    public function Login(Array $params=[]){
        $sql= "SELECT u.user_id, u.first_name, u.last_name, u.email, u.photo, u.address, u.address2, c.country_id, IFNULL(c.country, '') AS country, u.state_id, IFNULL(s.state, '') AS state, u.zip, u.user_group, u.date_registered, u.status
        FROM users AS u
        LEFT JOIN states AS s ON s.state_id = u.state_id
        LEFT JOIN countries AS c ON c.country_id = s.country_id
        WHERE email = ? AND password = ? LIMIT 1;";

        if($rows = $this->Query($sql, [$this->email, $this->password])){
            $r=$rows[0];

            if($r['status'] == 'Banned'){
                return new \Result(
                    null,
                    "Sorry {$r['first_name']} {$r['last_name']}, your account is banned,\nContact the administrator for more information",
                    'error',
                    ''
                );
            }

            // Set fake photo id to trigger loading default user image
            if(empty($r['photo'])){
                $r['photo'] = 0;
            }

            // Save user info
            SetUser($r);

            $redirect = GetRedirectURL();
            if(in_array($redirect, ['', '/'])){
                $redirect = $r['user_group'] == 'Administrator'?'/Admin-Panel':'/Home';
            }

            return new \Result(
                $r,
                "Logged in Successfully, Welcome {$r['first_name']} {$r['last_name']}",
                'success',
                $redirect
            );
        }

        return new \Result(
            null,
            'Login Failed',
            'error',
            ''
        );
    }

    public function Logout(Array $params=[]){
        $userName = GetUserName();
        session_destroy();

        return new \Result(
            null,
            "Good Bye $userName",
            'success',
            '/Home'
        );
    }

    private function HandleFileUpload(){
        if(isset($_FILES['photo']) && !empty($_FILES['photo']['name'])){
            $fileName = time().''.mt_rand(100, 999);

            $u=uploadFile('photo', PROFILE_PHOTOS_DIR, $fileName, MAX_UPLOAD_SIZE, ['image/jpeg', 'image/gif', 'image/png']);

            if(!$u['success']){
                return $u['message'];
            }
            
            $this->photo = $u['uploadName'];
        }

        return true;
    }

    public function Photo(Array $params=[]){
        if(empty($params[0]??'')) {
            $this->photo = GetUserPhoto();
        }else{
            $this->photo = $params[0];
        }

        if(empty($this->photo) || !file_exists(PROFILE_PHOTOS_DIR .'/'.$this->photo)){
            $this->photo = 'user-default.png';
        }

        header('Content-Type: image/png');
        readfile(PROFILE_PHOTOS_DIR .'/'.$this->photo);

        return null;
    }
}
