<h3 class="divider">Products</h3>

<div class="row mb-4">
    
<p>
    <h5 for="txtSearch" class=" m-3">Filter:</h5>
    <input class="ml-3" type="search" id="txtSearch" autocomplete="off" placeholder="Category, Product ...">
</p>
    
</div>

    <div id="cards-container" class="cards-container row d-flex justify-content-around">

    </div>

<script src="/js/models/Product.js"></script>
<script>
const cardsContainer = $('#cards-container');
let xhr;

// Search
$('#txtSearch').addEventListener('input', function(){
    LoadProducts(this.value);
});

function LoadProducts(search){
    search = search || '';
    search = search.trim();

    if(xhr){
        xhr.abort();
    }

    xhr = Ajax('POST', '/api/Product/ReadAvailable/'+search,
        null,
        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }

            cardsContainer.innerHTML = '';

            for (let p of resp.data) {
                p.currency = `<?= CURRENCY ?>`;
                
                let objProduct = new Product(p);
                let html = objProduct.Display();

                let card = document.createElement('div')
                card.className = 'card mb-4 d-flex'
                card.styleWidth = '18rem'
                card.innerHTML = html
                cardsContainer.appendChild(card)
            }
        }
    );
}

function AddToCart(productID, price){
    const item = CartManager.Add(productID, price);

    if(item){
        UpdateCartDisplay();
        ShowMessage('Item added', 'info');
    }else{
        ShowMessage('Item added already', 'warning');
    }
}

LoadProducts();
</script>