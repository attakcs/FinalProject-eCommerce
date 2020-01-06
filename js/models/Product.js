class Product {
    constructor(props) {
        this.product_id = props.product_id||0;
        this.category = props.category||'';
        this.product = props.product||'';
        this.brief = props.brief||'';
        this.description = props.description||'';
        this.price = props.price||0.0;
        this.quantity = props.quantity||'';
        this.date_added = new Date(props.date_added||new Date());
        this.status = props.status||'';
        this.image = props.image||'';
        this.currency = props.currency||'';
    }

    Display() {
        let html = `<img class="card-img-top" src="/api/Product/Image/${this.image}" alt="${this.product}">
                    <div class="card-body">
                        <h5 class="card-title">${this.product}</h5>
                        <p class="card-text mt-3 mb-3 text-right"><b>${this.currency}${this.price}</b></p>
                        <button id="cardButtonCart" onclick="AddToCart(${this.product_id}, ${this.price})" class="btn btn-outline-dark">Add</button>
                        <button id="cardButtonView" onclick="location.href='/Product-Specs/${this.product_id}'" class="btn btn-outline-dark">View</button>
                    </div>`;
        
        return html;
    }
}


 
