<h3 class="divider">Checkout</h3>

<section class="checkout-info">
    <div class="billing-info">
        <h3>Billing Details</h3>

        <form id="frmBillingDetails">
            <div class="form-group">
                <p>
                    <label>First Name</label>
                    <input type="text" id="first_name" value="<?= GetUser('first_name') ?>" required>
                </p>
                <p>
                    <label>Last Name</label>
                    <input type="text" id="last_name" value="<?= GetUser('last_name') ?>" required>
                </p>
            </div>
            <p>
                <label>Email</label>
                <input type="email" id="email" value="<?= GetUser('email') ?>" required>
            </p>
            <p>
                <label>Address</label>
                <input type="text" id="address" value="<?= GetUser('address') ?>" required>
            </p>
            <p>
                <label>Address 2</label>
                <input type="text" id="address2" value="<?= GetUser('address2') ?>" required>
            </p>
            <div class="form-group">
                <p>
                    <label>Country</label>
                    <select id="country_id" required></select>
                </p>
                <p>
                    <label>State</label>
                    <select id="state_id" required></select>
                </p>
                <p>
                    <label>Zip</label>
                    <input type="text" id="zip"  value="<?= GetUser('zip') ?>" required>
                </p>
            </div>

            <p>Credit Card Details</p>

            <p>
                <label>Select from your saved credit cards</label>
                <select id="credit-card-selector">
                    <option value="0">Select a card</option>
                </select>
            </p>

            <div class="form-group">
                <p>
                    <label>Name on Card</label>
                    <input type="text" id="name_on_card" value="<?= GetUserName() ?>" required>
                </p>
                <p>
                    <label>Credit Card Number</label>
                    <input type="text" id="card_number" required>
                </p>
            </div>
            <div class="form-group">
                <p>
                    <label>Expiration</label>
                    <input type="month" id="expiration" required>
                </p>
                <p>
                    <label>CVV</label>
                    <input type="text" id="cvv" required>
                </p>
            </div>

            <p>
                <label><input type="checkbox" id="save-card-data"> Save my card data</label>
            </p>

            <input type="hidden" id="coupon-discount-percentage" value="0">
            <input type="hidden" id="coupon" value="0">

            <button type="submit" class="btn btn-pill btn-success btn-block" value="Buy Now">Buy Now</button>
        </form>
    </div>

    <div class="order-details">
        <h3>Your Cart <span id="items-count"></span></h3>

        <table>
            <tbody id="order-details"></tbody>
            <tfoot>
                <tr class="coupon">
                    <td>
                        <strong>Coupon Code</strong><br>
                        <span id="coupon-code">N/A</span><br>
                        <span class="coupon-description" id="coupon-description"></span>
                    </td>
                    <td id="discount-percentage" class="quantity"></td>
                    <td id="discount-amount" class="total"></td>
                </tr>
                <tr class="vat">
                    <td>VAT</td>
                    <td id="vat-percentage" class="quantity"></td>
                    <td id="vat-amount" class="total"></td>
                </tr>
                <tr class="subtotal">
                    <td>Total</td>
                    <td></td>
                    <td id="subtotal" class="total"></td>
                </tr>
            </tfoot>
        </table>

        <form class="coupon-redeem" id="frmCoupon">
            <div>
                <input type="text" id="redeem-coupon-code" placeholder="Coupon Code">
                <input type="submit" value="Redeem">
            </div>
        </form>
    </div>
</section>

<script>
    function LoadCartProducts(){
        // Get stored products
        const myCart = CartManager.Get();

        // Get all product IDs
        let products = myCart.reduce(function(acc, curr){
            acc.push(curr.product_id);
            
            return acc;
        }, []);

        if(myCart.length == 0){
            return false;
        }
        
        // Request server for all products at once and get product_id, product, price, quantity(available), image
        Ajax('POST', '/api/Product/ReadMulti/' + products.join(),
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                RenderOrder(resp.data);
            });
    }

    function RenderOrder(data){
        const myOrder = $('#order-details');

        // Adding cart items
        for(let p of data){
            // Make sure locally stored prices match server prices
            let item = CartManager.Update(p.product_id, null, p.price);

            p.quantity = item.quantity;
            p.total = p.price * item.quantity;

            let itemContainer = document.createElement('tr');
            itemContainer.id = `item_${p.product_id}`;

            itemContainer.innerHTML = `<td class="product">
                        <strong>${p.product}</strong><br>
                        <span>${p.brief}</span>
                    </td>
                    <td class="quantity">&times;${p.quantity}</td>
                    <td class="total"><?= CURRENCY ?>${p.total}</td>`;

            myOrder.appendChild(itemContainer);
        }

        CalculateSubtotal();
    }

    // Update discount, vat and subtotal
    function CalculateSubtotal(){
        const couponDiscountPercentage = parseInt($('#coupon-discount-percentage').value);
        const vatPercentage = parseInt(<?= VAT_PERCENTAGE ?>);
        const cart = CartManager.Calculate();
        let subtotal = cart.total;

        $('#discount-percentage').textContent = `%${couponDiscountPercentage}`;
        $('#vat-percentage').textContent = `%${vatPercentage}`;

        const couponDiscountAmount = subtotal * couponDiscountPercentage / 100;
        $('#discount-amount').innerHTML = `-<?= CURRENCY ?>${couponDiscountAmount}`;

        subtotal -= couponDiscountAmount;

        const vatAmount = subtotal * vatPercentage / 100;
        $('#vat-amount').innerHTML = `<?= CURRENCY ?>${vatAmount}`;

        subtotal += vatAmount;

        // Show subtotal
        $('#subtotal').innerHTML = `<?= CURRENCY ?>${subtotal}`;
        $('#items-count').textContent = cart.count;
    }

    // Country, States
    const country = $('#country_id');
    const state = $('#state_id');
    
    country.addEventListener('change', LoadStates);

    // Load countries
    function LoadCountries(){
        Ajax('POST', '/api/Country/Read',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                for(let c of resp.data){
                    let o = new Option(c.country, c.country_id);
                    country.appendChild(o);
                }

                country.value = `<?= GetUser('country_id') ?>`;
                LoadStates(<?= GetUser('state_id') ?>);
            });
    }

    function LoadStates(defaultID){
        defaultID = defaultID||0;

        state.innerHTML = '';

        Ajax('POST', '/api/State/Read',
        {country_id: country.value},

        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }

            for(let s of resp.data){
                let o = new Option(s.state, s.state_id);
                state.appendChild(o);
            }

            if(defaultID){
                state.value = defaultID;
            }
        });
    }

    // Load saved credit cards
    function LoadCreditCards(){
        Ajax('POST', '/api/CreditCard/ReadValid',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                const creditCardSelector = $('#credit-card-selector');

                for(let c of resp.data){
                    let o = new Option(`[ ${c.credit_card_type} ] ${c.name_on_card}: ${c.card_number} (${c.expiration})`, c.credit_card_id);
                    creditCardSelector.appendChild(o);
                }
            });
    }

    // Optionally select one of saved credit cards to auto-fill the data
    $('#credit-card-selector').addEventListener('change', function(){
        $('#name_on_card').value = '';
        $('#card_number').value = '';
        $('#expiration').value = '';
        $('#cvv').value = '';
        
        if(this.value == 0){
            return false;
        }

        Ajax('POST', '/api/CreditCard/Read/' + this.value,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                const card = resp.data[0];

                $('#name_on_card').value = card.name_on_card;
                $('#card_number').value = card.card_number;
                $('#expiration').value = card.expiration;
                $('#cvv').value = card.cvv;

            });
    });

    // Redeem coupon
    $('#frmCoupon').addEventListener('submit', function(e){
        e.preventDefault();

        // Rest coupon display and recalculate
        $('#coupon-discount-percentage').value = 0;
        $('#coupon').value = '';
        $('#coupon-code').textContent = 'N/A';
        $('#coupon-description').textContent = '';

        CalculateSubtotal();

        const coupon = $('#redeem-coupon-code').value.trim();

        if(!coupon){
            ShowMessage('Coupon removed', 'info');

            return false;
        }
        
        if(CartManager.Get().length == 0){
            ShowMessage('Your cart is empty!\nGo add some products to it', 'info');

            return false;
        }

        Ajax('POST', '/api/Coupon/Redeem',
            {coupon: coupon},
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                const coupon = resp.data[0];

                $('#coupon-discount-percentage').value = coupon.discount;
                $('#coupon').value = coupon.coupon;
                $('#coupon-code').textContent = coupon.coupon;
                $('#coupon-description').innerHTML = coupon.description?`<br>${coupon.description}`:'';

                CalculateSubtotal();
            });
    })
    
    // Submit the order
    $('#frmBillingDetails').addEventListener('submit', function(e){
        e.preventDefault();

        if(CartManager.Get().length == 0){
            ShowMessage('Your cart is empty!\nGo add some products to it', 'info');
            
            return false;
        }

        Ajax('POST', '/api/Invoice/Create',
            {
                first_name: $('#first_name').value,
                last_name: $('#last_name').value,
                email: $('#email').value,
                address: $('#address').value,
                address2: $('#address2').value,
                state_id: $('#state_id').value,
                zip: $('#zip').value,
                name_on_card: $('#name_on_card').value,
                card_number: $('#card_number').value,
                expiration: $('#expiration').value,
                cvv: $('#cvv').value,
                coupon: $('#coupon').value,
                order: JSON.stringify(CartManager.Get()),
                save_card_data: $('#save-card-data').checked?1:0
            },
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                // Redirect to invoice viewer page
                if(!resp.redirect){
                    return false;
                }

                setTimeout(function(){
                    document.location.href = resp.redirect;
                }, 3000);

                // Clear cart
                CartManager.Clear();
            });
    });

    LoadCartProducts();
    LoadCountries();
    LoadCreditCards();
</script>