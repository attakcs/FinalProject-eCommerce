function $(selector, elem){
    elem = elem||document
    return elem.querySelector(selector)
}

function $$(selector, elem){
    elem = elem||document
    return elem.querySelectorAll(selector)
}

function ErrorInResponse(resp){
    let msg='';
    let isError=false;

    if(resp.message){
        msg=resp.message
    }

    if(['error', 'exception'].indexOf(resp.messageType)>-1){
        isError=true
    }

    if(msg){
       ShowMessage(msg, resp.messageType);
    }

    return isError;
}

function ShowMessage(message, messageType){
    //alert(`${messageType}: ${message}`);  
    let msg = document.createElement('div');
    msg.className = `message ${messageType.toLowerCase()}`;

    message = message.replace(/\n/g, '<br>');
    
    msg.innerHTML = `<p><span class="close" onclick="this.parentElement.parentElement.remove()">&#x2BBF;</span></p>
            <p>${message}</p>`;

    document.body.appendChild(msg);

    setTimeout(function(){
        msg.remove();
    }, 1500);
}

function SetupTableBody(container){
    let tBody = $('tbody', container);
    if(!tBody){
        tBody=document.createElement('tBody');
        container.appendChild(tBody);
    }

    return tBody;
}

function RenderTable(container, data, clear, renderer){
    const cols = GetModelColumns(container);
    const tBody = SetupTableBody(container);

    if(clear){
        tBody.innerHTML = '';
    }

    // Generate tbody rows
    for(let row of data){
        let tr = BuildTableRow(row, cols, renderer);

        tBody.appendChild(tr);
    }
}

function AddRow(container, data, renderer){
    const cols = GetModelColumns(container);
    const tBody = SetupTableBody(container);

    const tr = BuildTableRow(data[0], cols, renderer);

    tBody.prepend(tr);
}

function UpdateRow(container, data, rowIndex, renderer){
    const cols = GetModelColumns(container);
    const tBody = SetupTableBody(container);

    const tr = BuildTableRow(data[0], cols, renderer);

    tBody.insertBefore(tr, tBody.rows[rowIndex]);
    tBody.deleteRow(rowIndex+1);
}

function RemoveRow(container, rowIndex){
    const tBody = SetupTableBody(container);
    
    tBody.deleteRow(rowIndex);
}

function GetModelColumns(container){
    let tHead = $$('thead th', container);
    if(!tHead){
        return null;
    }

    let cols = [];

    for(let c of tHead){
        if('model' in c.dataset){
            cols.push(c.dataset['model']);
        }
    }

    return cols;
}

function BuildTableRow(row, cols, renderer){
    let tr = document.createElement('tr');

    if(typeof renderer == 'function'){
        tr.innerHTML = renderer(row);
    }else{
        for(let c of cols){
            if(!(c in row)){
                continue;
            }
    
            let td = document.createElement('td');
            td.textContent = row[c];
            tr.appendChild(td);
        }
    }

    return tr;
}

function GetSelectedRow(target){
    if(target.tagName == 'TR'){
        return target;
    }

    return target.closest('tr');
}

function GetCellValue(container, rowIndex, name){
    const tBody = SetupTableBody(container);
    let cellIndex = $(`thead [data-model='${name}']`, container).cellIndex;

    return tBody.rows[rowIndex].cells[cellIndex].textContent;
}

function Logout(){
    // Send Ajax request
    Ajax('POST', '/api/User/Logout',
        null,
        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }

            setTimeout(function(){
                document.location.href = resp.redirect;
            }, 1500);
        }
    )
}

// Calculate items count and price from local storage on every page load
function UpdateCartDisplay(){
    const cart = CartManager.Calculate();
    const cartCounter = $('#cartCounter');

    // Display number of items and total in cart icon on nav bar with 
    if(cartCounter){
        cartCounter.textContent = cart.count
        cartCounter.parentElement.title = `Items: ${cart.count}\nTotal: ${cart.total}`;
    }

    return cart;
};

function NL2P(str){
    str = str||'';
    return '<p>' + str.replace(/\r\n/g, "\n").replace(/\r/g, "\n").replace(/\n/g, '</p><p>') + '</p>';
}