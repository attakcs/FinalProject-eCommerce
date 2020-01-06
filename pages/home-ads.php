<div class="slideUp" id="news-bar">
    <marquee id="marq" direction="left" scrollamount="6" behavior="scroll" onmouseover="this.stop()" onmouseout="this.start()">
        <div id="news-container">

        </div>
    </marquee>
</div>

<div class="container w-75">
<div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
        <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
        <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="../product_photos/image_slider.jpg" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
                <h5>First slide label</h5>
                <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="../product_photos/image_slider(1).jpg" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
                <h5>Second slide label</h5>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="../product_photos/image_slider(2).jpg" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
                <h5>Third slide label</h5>
                <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur.</p>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
</div>

<div id="product-container">

</div>
<hr>
<script>
    const cardsContainer = $('#news-container');
    let xhr;
function newsbar() {
        xhr = Ajax('POST', '/api/Product/Read',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }
                for (let p of resp.data) {
                    p.currency = `<?= CURRENCY ?>`;
                     let card = document.createElement('a')
                    card.className = 'hvr-pop'
                    card.href = `/Product-Specs/${p.product_id}`
                    let html = `   ${p.product} ${p.currency}${p.price}       `
                    card.innerHTML = html
                    cardsContainer.appendChild(card)
                }
            }
        );

}
</script>
<script>
    const cardsContainer2 = $('#product-container');
    let xhr2;
    function homeProducts() {
        xhr2 = Ajax('POST', '/api/Product/Read',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }
                for (let p of resp.data) {
                    p.currency = `<?= CURRENCY ?>`;
                    let card = document.createElement('div')
                    card.className = 'card'
                    card.href = `/Product-Specs/${p.product_id}`
                    let html = `<img class="card-img-top" src="/api/Product/Image/${p.image}" alt="${p.alt}">
                      <div class="card-body">
                        <p class="card-text">${p.product} Price: ${p.currency}${p.price}</p>
                      </div>`
                    card.innerHTML = html
                    cardsContainer2.appendChild(card)
                }
            }
        );

    }
</script>
<script>
    newsbar()
    homeProducts()
</script>



