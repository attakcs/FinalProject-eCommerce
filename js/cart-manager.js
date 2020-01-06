CartManager = {
    name: 'MyCart',
    idProperty: 'product_id',
    priceProperty: 'price',
    quantityProperty: 'quantity',

    Get: function(){
        let myCart = localStorage.getItem(this.name);
        
        if(myCart){
            myCart = JSON.parse(myCart);
        }else{
            myCart = [];
        }
    
        return myCart;
    },
    
    Save: function (myCart){
        localStorage.setItem(this.name, JSON.stringify(myCart));
    },
    
    Clear: function (){
        localStorage.removeItem(this.name);
    },
    
    Find: function(itemID, items){
        let idProperty = this.idProperty;

        let item = items.find(function(elem){
            return elem[idProperty] == itemID
        });
    
        return item;
    },

    Add: function(itemID, itemPrice){
        let myCart = this.Get();
    
        // Make sure item is not stored in the cart
        let item = this.Find(itemID, myCart);
        
        if(item){
            return null;
        }

        // Create new item and add it to cart
        item = {};

        item[this.idProperty] = itemID;
        item[this.priceProperty] = itemPrice;
        item[this.quantityProperty] = 1;

        myCart.push(item);
    
        this.Save(myCart);

        return item;
    },

    Remove: function(itemID){
        let myCart = this.Get();
        let idProperty = this.idProperty;

        myCart = myCart.filter(function(elem){
            return elem[idProperty] != itemID
        });
    
        this.Save(myCart);
    },

    Update(itemID, quantity, price){
        let myCart = this.Get();

        let item = this.Find(itemID, myCart);
        
        if(!item){
            return null;
        }

        let doSave = false;
        quantity = parseInt(quantity);
        price = parseInt(price);
        
        if(!isNaN(quantity) && quantity >= 0){
            item[this.quantityProperty] = quantity;
            
            doSave = true;
        }
        
        if(!isNaN(price) && price >= 0){
            item[this.priceProperty] = price;
            
            doSave = true;
        }

        if(doSave){
            this.Save(myCart);
        }

        return item;
    },

    Increment(itemID){
        let myCart = this.Get();

        let item = this.Find(itemID, myCart);
        
        if(!item){
            return null;
        }

        item[this.quantityProperty]++;

        this.Save(myCart);

        return item;
    },

    Decrement(itemID){
        let myCart = this.Get();

        let item = this.Find(itemID, myCart);
        
        if(!item){
            return null;
        }

        if(item[this.quantityProperty] > 0){
            item[this.quantityProperty]--;

            this.Save(myCart);
        }

        return item;
    },
    
    Calculate: function(){
        let myCart = this.Get();
    
        let cart = myCart.reduce(function(acc, curr){
            acc.count++;
            acc.total += curr.price * curr.quantity;
            
            return acc;
        }, {count: 0, total: 0});
    
        return cart;
    }
}