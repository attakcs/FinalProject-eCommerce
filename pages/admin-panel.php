<h3 class="divider">Admin Panel</h3>
    
<section>
   <div class="admin-panel">
        <div class='item'>
             <h3><a href="/Category-Manager">Category Manager</a></h3>
             <p class="info" id="category_info">&nbsp;</p>
        </div>
        
        <div class='item'>
             <h3><a href="/Credit-Card-Type-Manager">Credit Card Type Manager</a></h3>
             <p class="info" id="credit_card_type_info">&nbsp;</p>
        </div>

        <div class='item'>
             <h3><a href="/User-Manager">User Manager</a></h3>
             <p class="info" id="user_info">&nbsp;</p>
        </div>

        <div class='item'>
            <h3><a href="/Product-Manager">Product Manager</a></h3>
            <p class="info" id="product_info">&nbsp;</p>
        </div>

        <div class='item'>
            <h3><a href="/Review-Manager">Review Manager</a></h3>
            <p class="info" id="review_info">&nbsp;</p>
        </div>

        <div class='item'>
            <h3><a href="/Question-Manager">Question Manager</a></h3>
            <p class="info" id="question_info">&nbsp;</p>
        </div>

        <div class='item'>
            <h3><a href="/Coupon-Manager">Coupon Manager</a></h3>
            <p class="info" id="coupon_info">&nbsp;</p>
        </div>

        <div class='item'>
            <h3><a href="/Invoice-Manager">Invoice Manager</a></h3>
            <p class="info" id="invoice_info">&nbsp;</p>
        </div>
  
        <div class='item'>
            <h3><a href="/Statistics">Statistics</a></h3>
            <p class="info" id="statistics_info">&nbsp;</p>
        </div>
    </div>
</section>

<script>
const category_info = $('#category_info');
const credit_card_type_info = $('#credit_card_type_info');
const user_info = $('#user_info');
const product_info = $('#product_info');
const review_info = $('#review_info');
const question_info = $('#question_info');
const coupon_info = $('#coupon_info');
const invoice_info = $('#invoice_info');
const statistics_info = $('#statistics_info');

function GetStatistics(){
    Ajax('POST', '/api/Statistics/AdminPanel',
        null,
        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }

            let r = resp.data[0];
            category_info.innerHTML = `<span title="Product Categories Count">${r.categories_count}</span>`;
            credit_card_type_info.innerHTML = `<span title="Credit Card Types Count">${r.credit_card_types_count}</span>`;
            user_info.innerHTML = `<span title="Users Count">${r.users_count}</span>`;
            product_info.innerHTML = `<span title="Product Shortage">${r.shortage_products_count}</span> | <span title="Products Count">${r.products_count}</span>`;
            review_info.innerHTML = `<span title="Pending Reviews">${r.pending_reviews_count}</span> | <span title="Reviews Count">${r.reviews_count}</span>`;
            question_info.innerHTML = `<span title="Pending Questions">${r.pending_questions_count}</span> | <span title="Questions Count">${r.questions_count}</span>`;
            coupon_info.innerHTML = `<span title="Active Coupons">${r.active_coupons_count}</span> | <span title="Coupons Count">${r.coupons_count}</span>`;
            invoice_info.innerHTML = `<span title="Pending / Not Paid Invoices">${r.pending_invoices_count}</span> | <span title="Invoices Count">${r.invoices_count}</span>`;
            statistics_info.innerHTML = `&nbsp;`;
        });
}

setInterval(GetStatistics, 5000);

// Call for the first time
GetStatistics();
</script>