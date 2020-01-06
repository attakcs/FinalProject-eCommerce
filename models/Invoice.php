<?php
class Invoice extends Model{
    private $invoice_id;
    private $date_created;
    private $user_id;
    private $email;
    private $address;
    private $address2;
    private $state_id;
    private $zip;
    private $name_on_card;
    private $card_number;
    private $subtotal;
    private $coupon_id;
    private $discount;
    private $vat;
    private $status;
    
    // Use coupon code for checking
    private $coupon;

    // Order details
    private $order;

    // Additional payment gateway related properties
    private $first_name;
    private $last_name;
    private $expiration;
    private $cvv;

    // Save credit card data option
    private $save_card_data;

    public function __construct(Array $props=array()){
        $this->invoice_id = abs($props['invoice_id']??0);
        $this->date_created = date('Y-m-d H:i:s');
        $this->user_id = abs($props['user_id']??0);
        $this->email = $props['email']??'';
        $this->address = $props['address']??'';
        $this->address2 = $props['address2']??'';
        $this->state_id = abs($props['state_id']??0);
        $this->zip = $props['zip']??'';
        $this->name_on_card = $props['name_on_card']??'';
        $this->card_number = $props['card_number']??'';
        $this->subtotal = abs($props['subtotal']??0.0);
        $this->coupon_id = abs($props['coupon_id']??0);
        $this->discount = abs($props['discount']??0.0);
        $this->vat = abs($props['vat']??0.0);
        $this->status = $props['status']??'Pending';
        
        // Use coupon code for checking
        $this->coupon = $props['coupon']??'';

        // Order details
        $this->order = json_decode($props['order']??'[]', true);

        // Additional payment gateway related properties
        $this->first_name = $props['first_name']??'';
        $this->last_name = $props['last_name']??'';
        $this->expiration = $props['expiration']??'';
        $this->cvv = $props['cvv']??'';

        // Save credit card data option
        $this->save_card_data = abs(intVal($props['save_card_data']??0));
    }

    public function Read (Array $params=array()){
        $sql="SELECT i.invoice_id, i.date_created, i.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, i.email, i.address, i.address2, cnt.country, i.state_id, s.state, i.zip, i.name_on_card, i.card_number, i.subtotal, i.coupon_id, IFNULL(c.coupon, 'N/A') AS coupon, IFNULL(c.description, '') AS coupon_description, IFNULL(c.discount, 0) AS coupon_percentage, i.discount, i.vat, (i.subtotal - i.discount + i.vat) AS total, i.status
            FROM invoices AS i
            INNER JOIN users AS u ON u.user_id = i.user_id
            INNER JOIN states AS s ON s.state_id = i.state_id
            INNER JOIN countries AS cnt ON cnt.country_id = s.country_id
            LEFT JOIN coupons AS c on c.coupon_id = i.coupon_id";
        
        $args=[];
        // Make sure invoices belong to user if it's not an admin
        if(!IsAdmin()){
            $sql .= " WHERE i.user_id = ?";
            $args = [GetUserID()];
        }

        if(!empty($params)){
            $sql .= (empty($args)?" WHERE":" AND") . " i.invoice_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY i.date_created DESC, i.invoice_id DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read invoices',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ReadMyInvoices(Array $params=array()){
        $sql="SELECT i.invoice_id, i.date_created, i.user_id, i.email, i.address, i.address2, cnt.country, i.state_id, s.state, i.zip, i.name_on_card, i.card_number, i.subtotal, i.coupon_id, IFNULL(c.coupon, 'N/A') AS coupon, IFNULL(c.description, '') AS coupon_description, IFNULL(c.discount, 0) AS coupon_percentage, i.discount, i.vat, (i.subtotal - i.discount + i.vat) AS total, i.status
            FROM invoices AS i
            INNER JOIN states AS s ON s.state_id = i.state_id
            INNER JOIN countries AS cnt ON cnt.country_id = s.country_id
            LEFT JOIN coupons AS c on c.coupon_id = i.coupon_id
            WHERE i.user_id = ?";
        
        $args=[GetUserID()];

        if(!empty($params)){
            $sql .= " AND i.invoice_id = ?";
            $args[] = (int)$params[0];
        }
        
        $sql .= " ORDER BY i.date_created DESC, i.invoice_id DESC";

        $rows = $this->Query($sql, $args);

        if($rows === false){
            return new \Result(
                [],
                'Failed to read invoices',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    public function ViewOrder(Array $params=array()){
        if($this->invoice_id == 0 && !empty($params)){
            $this->invoice_id = (int)$params[0];
        }

        $result = $this->Read([$this->invoice_id]);

        if(empty($result->data)){
            return new \Result(
                [],
                'Can not find this order',
                'error',
                ''
            );
        }
        
        $invoice = $result->data[0];

        // Get order details
        $sql = "SELECT od.order_detail_id, od.product_id, p.product, p.brief, od.price, od.quantity, (od.price * od.quantity) AS total, p.image
            FROM order_details AS od
            INNER JOIN products AS p ON p.product_id = od.product_id
            WHERE invoice_id = ?";

        $invoice['order'] = $this->Query($sql, [$this->invoice_id]);

        return new \Result($invoice );
    }

    public function Create(Array $params=array()){
        if(empty($this->order)){
            return new \Result(
                [],
                "Your cart is empty!\nGo add some products to it",
                'info',
                ''
            );
        }

        $this->date_created = date('Y-m-d H:i:s');
        $this->user_id = GetUserID();
        $this->subtotal = 0;
        $this->discount = 0;
        $this->coupon_id = 0;
        $this->vat = 0;
        $this->status = 'Pending';

        // Get coupon discount if any, and make sure it's not already used by the customer
        $discountPercentage = 0;

        if(!empty($this->coupon)){
            $objCoupon = new Coupon([
                'coupon' => $this->coupon
            ]);
            
            $result = $objCoupon->Redeem();
            if($result->messageType == 'error'){
                return $result;
            }

            $this->coupon_id = $result->data[0]['coupon_id'];
            $discountPercentage = $result->data[0]['discount'];
        }
        
        // Save basic invoice data and get new invoice_id
        $sql="INSERT INTO invoices(date_created, user_id, email, address, address2, state_id, zip, name_on_card, card_number, subtotal, coupon_id, discount, vat, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // masking car number
        $maskedCardNumber = $this->card_number;
        $maskLen = strlen($maskedCardNumber) - 4;
        
        if($maskLen>0){
            $maskedCardNumber = str_repeat('*', $maskLen) . substr($maskedCardNumber, -4);
        }

        $this->invoice_id = $this->Query($sql, [
                $this->date_created,
                $this->user_id,
                $this->email,
                $this->address,
                $this->address2,
                $this->state_id,
                $this->zip,
                $this->name_on_card,
                $maskedCardNumber,
                $this->subtotal,
                $this->coupon_id,
                $this->discount,
                $this->vat,
                $this->status
            ]);

        if(!$this->invoice_id){
            return new \Result(
                null,
                'Failed to create invoice',
                'error',
                ''
            );
        }

        // Save order details
        // Use products table to verify that the product_id exists and get trusted price
        $sql = "INSERT INTO order_details(invoice_id, product_id, price, quantity)
            SELECT ?, product_id, price, ?
            FROM products WHERE product_id = ?";
        
        foreach($this->order as $p){
            $this->Query($sql, [
                $this->invoice_id,
                $p['quantity'],
                $p['product_id']
            ]);
        }

        // Calculate subtotal
        $sql = "SELECT IFNULL(SUM(price * quantity), 0) AS subtotal
            FROM order_details
            WHERE invoice_id = ?";

        $rows = $this->Query($sql, [$this->invoice_id]);
        $this->subtotal = $rows[0]['subtotal'];

        // Calculate discount
        $this->discount = $this->subtotal * $discountPercentage / 100;

        // Calculate VAT
        $this->vat = ($this->subtotal - $this->discount) * VAT_PERCENTAGE / 100;
        
        // Update invoice
        $this->Update($params);

        // Call payment API and get payment success/faild result
        $totalAmount = $this->subtotal - $this->discount + $this->vat;

        $ApiPaymentResult = $this->CallPaymentAPI([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'address' => $this->address,
            'address2' => $this->address2,
            'state_id' => $this->state_id,
            'zip' => $this->zip,
            'name_on_card' => $this->name_on_card,
            'expiration' => $this->expiration,
            'cvv' => $this->cvv,
            'amount' => $totalAmount
        ]);
        
        if($ApiPaymentResult == 'Success'){
            $this->status = 'Paid';
        }else{
            $this->status = 'Not_Paid';
        }

        // Update invoice status will affect store quantities
        $this->UpdateStatus($params);

        // Save credit card data if user opted to
        if($this->save_card_data){
            $objCreditCard = new CreditCard([
                'user_id' => $this->user_id,
                'credit_card_type_id' => 1,
                'name_on_card' => $this->name_on_card,
                'card_number' => $this->card_number,
                'expiration' => $this->expiration,
                'cvv' => $this->cvv
            ]);

            $objCreditCard->Save([]);
        }

        // Return created record
        $res = $this->Read([$this->invoice_id]);

        // Notify customer of any sudden shortage occurred in the store while processing the order
        $sql = "SELECT 1
            FROM order_details AS od
            INNER JOIN products AS p ON p.product_id = od.product_id
            WHERE od.invoice_id = ? AND p.status = 'Shortage'";

        $rows = $this->Query($sql, [$this->invoice_id]);

        $msg = "Invoice created, WE will ship your order ASAP,";
        $msgType = 'success';

        if(!empty($rows)){
            $msg = "Invoice created with some shortage in our store,\nYour order might be delayed a bit";
            $msgType = 'info';
        }
        
        if($this->status == 'Not_Paid'){
            $msg = "Invoice created, But we couldn't process your payment,";
            $msgType = 'warning';
        }
        
        // Send invoice email to customer
        $isSent = $this->SendEmailInvoice($this->invoice_id);
        if($isSent){
            $msg .= "\nWe sent your order details to your email, Please check";
        }

        $msg .="\n\nThank You";

        return new \Result(
            $res->data,
            $msg,
            $msgType,
            '/View-Order/'.$this->invoice_id
        );
        
    }

    public function Update(Array $params=array()){
        $sql="UPDATE invoices
            SET
                subtotal = ?,
                discount = ?,
                vat = ?,
                status = ?
            WHERE invoice_id = ?";
       
        if($this->Query($sql,  [
            $this->subtotal,
            $this->discount,
            $this->vat,
            $this->status,
            $this->invoice_id
        ])
        ){
            // Return created record
            $res = $this->Read([$this->invoice_id]);

            return new \Result(
                $res->data,
                "Invoice is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update invoice',
            'error',
            ''
        );
    }

    public function UpdateStatus(Array $params=array()){
        // Get old status
        $sql = "SELECT status FROM invoices WHERE invoice_id = ?";
        $rows = $this->Query($sql, [$this->invoice_id]);
        
        if(empty($rows)){
            return new \Result(
                null,
                'Unkown invoice',
                'error',
                ''
            );
        }

        $oldStatus = $rows[0]['status'];

        $sql="UPDATE invoices
            SET
                status = ?
            WHERE invoice_id = ?";
       
        if($this->Query($sql,  [
            $this->status,
            $this->invoice_id
        ])
        ){
            // Detecting status changed to decide how to update store quantities
            if($oldStatus != $this->status){
                // Increment store quantities when new status is Pending, Canceled or Not_Paid
                if(in_array($this->status, ['Pending', 'Canceled', 'Not_Paid']) && $oldStatus == 'Paid'){
                    $this->ReturnItems($this->invoice_id);
                }

                // Decrement store quantities when new status is Paid
                if($this->status == 'Paid' && in_array($oldStatus, ['Pending', 'Canceled', 'Not_Paid'])){
                    $this->ShipItems($this->invoice_id);
                }
            }

            // Return created record
            $res = $this->Read([$this->invoice_id]);

            return new \Result(
                $res->data,
                "Invoice is updated",
                'success',
                ''
            );
        }
        
        return new \Result(
            null,
            'Failed to update invoice',
            'error',
            ''
        );
    }

    public function Delete(Array $params=array()){
        // Get old status
        $sql = "SELECT status FROM invoices WHERE invoice_id = ?";
        $rows = $this->Query($sql, [$this->invoice_id]);
        
        if(empty($rows)){
            return new \Result(
                null,
                'Unkown invoice',
                'error',
                ''
            );
        }

        $oldStatus = $rows[0]['status'];

        // Increment store quantities when old status is Paid
        if($oldStatus == 'Paid'){
            $this->ReturnItems($this->invoice_id);
        }

        $sql="DELETE FROM invoices WHERE invoice_id = ?";

        if($this->Query($sql, [
                $this->invoice_id
            ])
        ){
            return new \Result(
                null,
                "Invoice deleted",
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

    // Removing ordered items from store
    private function ShipItems($invoiceID){
        $sql = "UPDATE products AS p 
            INNER JOIN  order_details AS od ON od.product_id = p.product_id
            SET
                p.quantity = p.quantity - CAST(od.quantity AS SIGNED),
                p.status = IF((p.quantity - CAST(od.quantity AS SIGNED)) < 0, 'Shortage', p.status)
            WHERE od.invoice_id = ?";
        
        $this->Query($sql, [$invoiceID]);
    }

    // Returning ordered items to store (canceled/deleted)
    private function ReturnItems($invoiceID){
        $sql = "UPDATE products AS p 
        INNER JOIN  order_details AS od ON od.product_id = p.product_id
        SET
            p.quantity = p.quantity + CAST(od.quantity AS SIGNED),
            p.status = IF((p.quantity + CAST(od.quantity AS SIGNED)) > 0, 'Available', p.status)
        WHERE od.invoice_id = ?";
     
        $this->Query($sql, [$invoiceID]);
    }

    private function SendEmailInvoice($invoiceID){
        $invoice = $this->ViewOrder([$invoiceID])->data;

        $subject = 'Invoice #'.$invoice['invoice_id'];
        return SendTemplateEmail($invoice['email'], $subject, 'invoice', $invoice);
    }

    // This is a fake payment API call function
    // Any logic related to payment can be put here
    // At the end of the processing we must return either 'Success' or 'Fail'
    private function CallPaymentAPI($data){
        return 'Success';
    }
}
?>