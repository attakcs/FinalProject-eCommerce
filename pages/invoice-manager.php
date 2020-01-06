<h3 class="divider">Invoice Manager</h3>

<section class="data-editor">
    <form id="frmEditor">
        <p>
            <label for="invoice_id">ID</label>
            <input type="number" id="invoice_id" name="invoice_id" readonly>
        </p>
        <p>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Pending">Pending</option>
                <option value="Paid">Paid</option>
                <option value="Not_Paid">Not Paid</option>
                <option value="Canceled">Canceled</option>
            </select>
        </p>

        <p class="form-operations">
            <input type="button" class="button cancel" id="btnCancel" value="Cancel">
            <input type="submit" class="button" id="btnSubmit" value="No Operation">
        </p>
    </form>
</section>

<section class="data-list">

    <div class="toolbar">
        <button class="button update btn btn-pill btn-info" id="btnUpdate">Update  &#x21A5;</button>
        <button class="button delete btn btn-pill btn-danger" id="btnDelete">Delete &#x2BBF;</button>
        <button class="button refresh btn btn-pill btn-dark" id="btnRefresh">Refresh &#x21BA;</button>
        <button class="button view-order btn btn-pill btn-info" id="btnViewOrder">View Order</button>
    </div>

    <table id="tblData">
        <thead>
            <tr>
                <th data-model="invoice_id">ID</th>
                <th data-model="date_created">Date</th>
                <th data-model="customer">Customer</th>
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

<button class="btn btn-pill btn-dark" onclick="location.href='/Admin-Panel'">&#x2B9C; Back</button>

<script>
    const secDataEditor=$('.data-editor');
    const tblData=$('#tblData');
    const btnSubmit=$('#btnSubmit');
    const btnCancel=$('#btnCancel');
    let currentOper = '';
    let SelectedRow = null;

    $('#frmEditor').addEventListener('submit', OperationHandler);
    $('#btnViewOrder').addEventListener('click', OperationHandler);
    $('#btnUpdate').addEventListener('click', OperationHandler);
    $('#btnDelete').addEventListener('click', OperationHandler);
    $('#btnRefresh').addEventListener('click', OperationHandler);

    // Set selected row
    tblData.addEventListener('click', function(e){
        if(SelectedRow){
            SelectedRow.classList.remove('selected');
        }

        SelectedRow = GetSelectedRow(e.target);
        SelectedRow.classList.add('selected');
    });

    btnCancel.addEventListener('click', function(e){
        secDataEditor.classList.remove('show');
    });

    // Handle CRUD operations
    function OperationHandler(e){
        e.preventDefault();
   
        btnSubmit.classList.remove('create');
        btnSubmit.classList.remove('update');
        btnSubmit.classList.remove('delete');

        switch(e.target.id){
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
            
            case 'btnUpdate':
                if(!SelectedRow){
                    ShowMessage('Please select a row first', 'warning');
                    return false;
                }

                btnSubmit.value = 'Update';
                currentOper = 'UpdateStatus';

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'invoice_id'));
                btnSubmit.classList.add('update');
                secDataEditor.classList.add('show');
                break;
            
            case 'btnDelete':
                if(!SelectedRow){
                    ShowMessage('Please select a row first', 'warning');
                    return false;
                }

                btnSubmit.value = 'Delete';
                currentOper = 'Delete';

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'invoice_id'));
                btnSubmit.classList.add('delete');
                secDataEditor.classList.add('show');
                break;
            
            case 'btnRefresh':
                btnSubmit.value = 'No Operation';
                currentOper = 'Read';

            case 'frmEditor':
                let data = {};

                if(['UpdateStatus'].indexOf(currentOper)>-1){
                    data = {
                            invoice_id: $('#invoice_id').value,
                            status:     $('#status').value
                        }
                }

                if(currentOper == 'Delete'){
                    data = {invoice_id:$('#invoice_id').value};
                }

                // Temporarily store current row index and operation until we receive the response
                let dbOper = currentOper;
                let rowIndex = -1;

                if(SelectedRow){
                    // The row index in the table body
                    rowIndex = SelectedRow.rowIndex-1;
                }

                // Send Ajax request
                Ajax('POST', '/api/Invoice/' + dbOper,
                    data,
                    function(resp){
                        if(ErrorInResponse(resp)){
                            return false;
                        }

                        // Hide the form
                        btnCancel.click();

                         // Handle received data
                         switch(dbOper){
                            case 'Read':
                                // Clear the table before appending rows
                                RenderTable(tblData, resp.data, true);
                                break;
                                
                            case 'UpdateStatus':
                                UpdateRow(tblData, resp.data, rowIndex);
                                break;

                            case 'Delete':
                                RemoveRow(tblData, rowIndex);
                                break;
                        }

                        rowIndex = -1;
                        SelectedRow = null;
                    }
                );

                break;
        }
    }

    function FillForm(id){
        Ajax('POST', '/api/Invoice/Read/' + id,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#invoice_id').value =    r.invoice_id;
                $('#status').value =   r.status;
            });
    }

    $('#btnRefresh').click();
</script>