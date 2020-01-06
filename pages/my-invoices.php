<h3 class="divider">My Invoices</h3>
    
<section class="data-list">
    <div class="toolbar">
        <button class="btn btn-pill btn-info" type="button" id="btnViewOrder">View Order</button>
        <button class="button refresh btn btn-pill btn-dark" id="btnRefresh">Refresh &#x21BA;</button>
    </div>

    <table id="tblData">
        <thead>
            <tr>
                <th data-model="invoice_id">ID</th>
                <th data-model="date_created">Date</th>
                <th data-model="name_on_card">Name on Card</th>
                <th data-model="subtotal">Subtotal</th>
                <th data-model="card_number">Card Number</th>
                <th data-model="coupon">Coupon</th>
                <th data-model="discount">Discount</th>
                <th data-model="vat">VAT</th>
                <th data-model="total">Total</th>
                <th data-model="status">Status</th>
            </tr>
        </thead>
    </table>
</section>

<button type="button" class="btn btn-pill btn-dark" onclick="location.href='/Product-Catalog'">&#x2B9C; Back</button>

<script>
    const tblData=$('#tblData');
    let SelectedRow = null;

    $('#btnRefresh').addEventListener('click', OperationHandler);
    $('#btnViewOrder').addEventListener('click', OperationHandler);

    // Set selected row
    tblData.addEventListener('click', function(e){
        if(SelectedRow){
            SelectedRow.classList.remove('selected');
        }

        SelectedRow = GetSelectedRow(e.target);
        SelectedRow.classList.add('selected');
    });

    // Handle CRUD operations
    function OperationHandler(e){
        e.preventDefault();
   
        switch(e.target.id){
            case 'btnRefresh':
                // Send Ajax request
                Ajax('POST', '/api/Invoice/ReadMyInvoices',
                    null,
                    function(resp){
                        if(ErrorInResponse(resp)){
                            return false;
                        }

                        // Clear the table before appending rows
                        RenderTable(tblData, resp.data, true);

                        rowIndex = -1;
                        SelectedRow = null;
                    }
                );
                break;

            case 'btnViewOrder':
                if(!SelectedRow){
                    ShowMessage('Please select a row first', 'warning');
                    return false;
                }

                // Create a dummy link in order to open invoice in a new tab
                const lnk = document.createElement('a');
                lnk.href = '/View-Order/' + GetCellValue(tblData, SelectedRow.rowIndex-1, 'invoice_id');
                lnk.target = '_blank';

                document.body.appendChild(lnk);
                lnk.click();
                lnk.remove();
                delete lnk;
                break;
        }
    }

    $('#btnRefresh').click();
</script>