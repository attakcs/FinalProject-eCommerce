<h3 class="divider">My Cart</h3>

<section>
    <div  id="my-cart-items"></div>
    <p>Subotal: <?= CURRENCY ?><span id="subtotal"></span></p>
    <button type="button" class="btn btn-pill btn-dark" onclick="location.href='/Product-Catalog'">&#x2B9C; Back</button>
    <button type="button" class="btn btn-pill btn-success" onclick="location.href='/Checkout'">Checkout</button>
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

                const myCartItems = $('#my-cart-items');
                myCartItems.innerHTML = '';
                
                for(let p of resp.data){
                    // Make sure locally stored prices match server prices
                    let item = CartManager.Update(p.product_id, null, p.price);

                    p.quantity = item.quantity;
                    p.total = p.price * item.quantity;

                    let itemContainer = document.createElement('div');
                    itemContainer.className = 'item-container';
                    itemContainer.id = `item_${p.product_id}`;
                    
                    itemContainer.innerHTML = RenderItem(p);

                    myCartItems.appendChild(itemContainer);
                }

                let cart = UpdateCartDisplay();
                // Show subtotal
                $('#subtotal').textContent = cart.total;
            });
    }

    function RenderItem(p){
        return `<img src="/api/Product/Image/${p.image}">
            <div>
                <h3>${p.product}</h3>
                <p>Price: <span class="price"><?= CURRENCY ?>${p.price}</span></p>
                <p>Quantity: <input type="number" name="quantity" min="1" max="${p.store_quantity}" value="${p.quantity}" oninput="UpdateQuantity(${p.product_id}, this.value)"> <i>In store <span>${p.store_quantity}</span></i></p>
                <p>Total: <?= CURRENCY ?><span class="total">${p.total}</span></p>
                <p><button type="button" class="btn btn-pill btn-warning" value="Remove" onclick="RemoveItem(${p.product_id})">Remove &#x2BBF;</button>
            </div>`;
    }

    function UpdateQuantity(productID, quantity){
        let item = $(`#item_${productID}`);
        if(!item){
            return false;
        }

        let uItem = CartManager.Update(productID, quantity, null);
        let cart = UpdateCartDisplay();

        // Show item total price
        item.querySelector('.total').textContent = uItem.quantity * uItem.price

        // Show subtotal
        $('#subtotal').textContent = cart.total;
    }

    function RemoveItem(productID){
        let item = $(`#item_${productID}`);
        if(!item){
            return false;
        }

        item.remove();

        CartManager.Remove(productID);
        let cart = UpdateCartDisplay();
        // Show subtotal
        $('#subtotal').textContent = cart.total;
    }

    LoadCartProducts();
    </script>
