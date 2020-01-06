<?php
class Statistics extends Model{
    public function __construct(Array $props=[]){
        $this->start_date = $props['start_date']??'first day of this month';
        $this->end_date = $props['end_date']??'last day of this month';
        $this->limit = abs($props['limit']??3);

        $startDate = new DateTime($this->start_date);
        $this->start_date = $startDate->format('Y-m-d 00:00:00');

        $endDate = new DateTime($this->end_date);
        $this->end_date = $endDate->format('Y-m-d 23:59:59');
    }

    // Admin Panel
    public function AdminPanel(){
        $sql = "SELECT 
            (SELECT COUNT(*) FROM answers) AS answers_count,
            (SELECT COUNT(*) FROM categories) AS categories_count,
            (SELECT COUNT(*) FROM coupons) AS coupons_count,
            (SELECT COUNT(*) FROM coupons WHERE NOW() BETWEEN start_date AND end_date) AS active_coupons_count,
            (SELECT COUNT(*) FROM credit_card_types) AS credit_card_types_count,
            (SELECT COUNT(*) FROM invoices) AS invoices_count,
            (SELECT COUNT(*) FROM invoices WHERE status IN ('Pending', 'Not_Paid')) AS pending_invoices_count,
            (SELECT COUNT(*) FROM products) AS products_count,
            (SELECT COUNT(*) FROM products WHERE status = 'Shortage' OR quantity = 0) AS shortage_products_count,
            (SELECT COUNT(*) FROM questions) AS questions_count,
            (SELECT COUNT(*) FROM questions WHERE status = 'Pending') AS pending_questions_count,
            (SELECT COUNT(*) FROM reviews) AS reviews_count,
            (SELECT COUNT(*) FROM reviews WHERE status = 'Pending') AS pending_reviews_count,
            (SELECT COUNT(*) FROM users) AS users_count";
        
        $rows = $this->Query($sql, []);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get admin panel statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    // Top sold products based on quantity
    public function TopSales(Array $params=[]){
        $sql = "SELECT ts.product_id, p.product, p.brief, ts.quantity, ts.last_purchased, p.status, p.image
            FROM(
                SELECT od.product_id, SUM(od.quantity) AS quantity, MAX(i.date_created) AS last_purchased
                FROM order_details AS od
                INNER JOIN invoices AS i ON i.invoice_id = od.invoice_id
                WHERE i.date_created BETWEEN ? AND ? AND i.status ='Paid'
                GROUP BY od.product_id
                ORDER BY SUM(od.quantity) DESC
                LIMIT ?
            ) AS ts
            INNER JOIN products AS p ON p.product_id = ts.product_id
            ORDER BY ts.quantity DESC";

        $rows = $this->Query($sql, [
            $this->start_date,
            $this->end_date,
            $this->limit
        ]);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get top sales statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    // Top Products based on review stars
    public function TopRated(Array $params=[]){
        $sql = "SELECT tr.product_id, p.product, p.brief, ROUND(tr.stars, 1) AS stars, tr.last_rated, p.status, p.image
            FROM(
                SELECT product_id, AVG(stars) AS stars, MAX(date_added) AS last_rated
                FROM reviews AS r
                WHERE date_added BETWEEN ? AND ? AND status = 'Approved'
                GROUP BY product_id
                ORDER BY SUM(stars) DESC
                LIMIT ?
            ) AS tr
            INNER JOIN products AS p ON p.product_id = tr.product_id
            ORDER BY tr.stars DESC";

        $rows = $this->Query($sql, [
            $this->start_date,
            $this->end_date,
            $this->limit
        ]);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get top rated statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    // Frequently ordering customers
    public function LoyalCustomers(Array $params=[]){
        $sql = "SELECT lb.user_id, CONCAT(u.first_name, ' ', u.last_name) AS customer, u.email, lb.placed_orders, lb.total_paid, u.photo
            FROM(
                SELECT i.user_id, COUNT(*) AS placed_orders, SUM(i.subtotal - i.discount + i.vat) AS total_paid
                FROM invoices AS i
                WHERE i.date_created BETWEEN ? AND ? AND i.status ='Paid'
                GROUP BY i.user_id
                ORDER BY COUNT(*) DESC
                LIMIT ?
            ) AS lb
            INNER JOIN users AS u ON u.user_id = lb.user_id
            ORDER BY lb.placed_orders DESC";

        $rows = $this->Query($sql, [
            $this->start_date,
            $this->end_date,
            $this->limit
        ]);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get top loyal customers statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    // Monthly paid invoices
    function MonthlyIncome(Array $params=[]){
        $sql = "SELECT YEAR(date_created) AS `year`, MONTH(date_created) AS `month`, SUM(subtotal- discount) AS income, SUM(vat) AS vat, MAX(date_created) AS last_payment
            FROM invoices
            WHERE date_created BETWEEN ? AND ? AND status = 'Paid'
            GROUP BY YEAR(date_created), MONTH(date_created)
            ORDER BY `year` DESC, `month` DESC";

        $rows = $this->Query($sql, [
            $this->start_date,
            $this->end_date
        ]);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get top monthly income statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }

    // Monthly logged invoices grouped by status
    function MonthlyInvoices(Array $params=[]){
        $sql = "SELECT YEAR(date_created) AS `year`, MONTH(date_created) AS `month`,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Pending' THEN subtotal- discount ELSE 0 END) AS pending_amount,
                SUM(CASE WHEN status = 'Pending' THEN vat ELSE 0 END) AS pending_vat,
                SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                SUM(CASE WHEN status = 'Paid' THEN subtotal- discount ELSE 0 END) AS paid_amount,
                SUM(CASE WHEN status = 'Paid' THEN vat ELSE 0 END) AS paid_vat,
                SUM(CASE WHEN status = 'Not_Paid' THEN 1 ELSE 0 END) AS not_paid,
                SUM(CASE WHEN status = 'Not_Paidd' THEN subtotal- discount ELSE 0 END) AS not_paid_amount,
                SUM(CASE WHEN status = 'Not_Paidd' THEN vat ELSE 0 END) AS not_paid_vat,
                SUM(CASE WHEN status = 'Canceled' THEN 1 ELSE 0 END) AS canceled,
                SUM(CASE WHEN status = 'Canceled' THEN subtotal- discount ELSE 0 END) AS canceled_amount,
                SUM(CASE WHEN status = 'Canceled' THEN vat ELSE 0 END) AS canceled_vat
            FROM invoices
            WHERE date_created BETWEEN ? AND ?
            GROUP BY YEAR(date_created), MONTH(date_created)
            ORDER BY `year` DESC, `month` DESC";

        $rows = $this->Query($sql, [
            $this->start_date,
            $this->end_date
        ]);

        if($rows === false){
            return new \Result(
                null,
                'Failed to get top monthly invoice statistics',
                'error',
                ''
            );
        }

        return new \Result($rows);
    }
    
}
