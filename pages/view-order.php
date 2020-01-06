<?php
    $segments =  GetURISegments();
    $invoiceID =  $segments[1]??0;
?>

<h3 class="divider">Order Preview</h3>

<div>
    <p>Invoice No: <span id="invoice_id"></span></p>
    <p>Date Created: <span id="date_creted"></span></p>
    <p>Payment method: Credit Card (<span id="card_number"></span>)</p>
</div>

<div>
    <h3>Customer:</h3>
    <p><span id="customer"></span></p>
    <p><span id="email"></span></p>
    <p><span id="address"></span></p>
    <p><span id="address2"></span></p>
    <p><span id="state"></span></p>
</div>

<div class="order-details">
    <h3>Order Details</h3>

    <table>
        <tbody id="order-details"></tbody>
        <tfoot>
            <tr class="coupon">
                <td colspan="2">
                    <strong>Coupon Code</strong><br>
                    <span id="coupon"></span><br>
                    <span class="coupon-description" id="coupon_description"></span>
                </td>
                <td id="coupon_percentage" class="quantity"></td>
                <td id="discount" class="total"></td>
            </tr>
            <tr class="vat">
                <td colspan="2">VAT</td>
                <td id="vat_percentage" class="quantity"></td>
                <td id="vat" class="total"></td>
            </tr>
            <tr class="subtotal">
                <td colspan="2">Total</td>
                <td></td>
                <td id="total" class="total"></td>
            </tr>
        </tfoot>
    </table>
</div>

<form id="frmReview" class="add-review-form">
    <h3>Add your review for:</h3>
    <p id="product"></p>

    <p>
        <label for="review">Review</label>
        <textarea id="review" name="review"></textarea>
    </p>
    <p>
        <label for="stars">Stars</label>
        <input type="number" id="stars" name="stars" value="5" min="0" max="5">
    </p>
    
    <input type="hidden" id="product_id">

    <p class="form-operations">
        <input type="submit" class="button" value="Add Review">
    </p>
</form>

<script>
    const frmReview = $('#frmReview');

    function ViewOrder(invoiceID){
        Ajax('POST', '/api/Invoice/ViewOrder',
            {invoice_id: invoiceID},
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                const invoice = resp.data;

                $('#customer').textContent = invoice.customer;
                $('#card_number').textContent = invoice.card_number;
                $('#email').textContent = invoice.email;
                $('#address').textContent = invoice.address;
                $('#address2').textContent = invoice.address2;
                $('#state').textContent = `${invoice.state} ${invoice.zip}, ${invoice.country}`;
                $('#coupon').textContent = invoice.coupon;
                $('#coupon_description').textContent = invoice.coupon_description;
                $('#coupon_percentage').textContent = `%${invoice.coupon_percentage}`;
                $('#discount').innerHTML = `-<?= CURRENCY ?>${invoice.discount}`;
                $('#vat_percentage').textContent = `%<?= VAT_PERCENTAGE ?>`;
                $('#vat').innerHTML = `<?= CURRENCY ?>${invoice.vat}`;
                $('#total').innerHTML = `<?= CURRENCY ?>${invoice.total}`;

                const orderDetails = $('#order-details');

                // Adding cart items
                for(let p of invoice.order){
                    let itemContainer = document.createElement('tr');
                    itemContainer.id = `item_${p.product_id}`;

                    itemContainer.innerHTML = `<td><img src="/api/Product/Image/${p.image}"></td>
                            <td class="product">
                                <strong>${p.product}</strong><br>
                                <span>${p.brief}</span><br>
                                <input type="button" class="button review" value="Add Review" onclick="AddReview(${p.product_id}, '${p.product}')">
                            </td>
                            <td class="quantity">&times;${p.quantity}</td>
                            <td class="total"><?= CURRENCY ?>${p.total}</td>`;

                    orderDetails.appendChild(itemContainer);
                }
            });
    }

    function AddReview(productID, product){
        $('#product_id').value = productID;
        $('#product').textContent = product;

        frmReview.classList.add('show');
        $('#review').focus();
    }

    // Product review
    frmReview.addEventListener('submit', function(e){
            e.preventDefault();

            Ajax('POST', '/api/Review/Create',
                {
                    product_id: $('#product_id').value,
                    review: $('#review').value,
                    stars: $('#stars').value,
                },
                function(resp) {
                    if (ErrorInResponse(resp)) {
                        return false;
                    }

                    frmReview.classList.remove('show');
                    frmReview.reset();
                });
        });

    ViewOrder(parseInt(<?= $invoiceID ?>));
</script>