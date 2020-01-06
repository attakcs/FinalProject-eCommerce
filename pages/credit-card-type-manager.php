<h3 class="divider">Credit Card Type Manager</h3>

<section class="data-editor">
    <form id="frmEditor">       
        <p>
            <label for="credit_card_type_id">ID</label>
            <input type="number" id="credit_card_type_id" readonly>
        </p>
        <p>
            <label for="credit_card_type">Credit Card Type</label>
            <input type="text" id="credit_card_type" min="0">
        </p>
        <p>
            <label for="display_order">Display Order</label>
            <input type="number" id="display_order" min="0">
        </p>
        <p>
            <label for="status">Status</label>
            <select id="status">
                <option value="Enabled">Enabled</option>
                <option value="Disabled">Disabled</option>
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
        <button class="button create btn btn-pill btn-success" id="btnCreate">Create &#10514;</button>
        <button class="button update btn btn-pill btn-info" id="btnUpdate">Update  &#x21A5;</button>
        <button class="button delete btn btn-pill btn-danger" id="btnDelete">Delete &#x2BBF;</button>
        <button class="button refresh btn btn-pill btn-dark" id="btnRefresh">Refresh &#x21BA;</button>
    </div>

    <table id="tblData">
        <thead>
            <tr>
                <th data-model="credit_card_type_id">ID</th>
                <th data-model="credit_card_type">Credit Card Type</th>
                <th data-model="display_order">Display Order</th>
                <th data-model="status">Status</th>
            </tr>
        </thead>
    </table>
</section>

<button type="button" class="btn btn-pill btn-dark" onclick="location.href='/Admin-Panel'">&#x2B9C; Back</button>

<script>
    const secDataEditor=$('.data-editor');
    const tblData=$('#tblData');
    const btnSubmit=$('#btnSubmit');
    const btnCancel=$('#btnCancel');
    let currentOper = '';
    let SelectedRow = null;

    $('#frmEditor').addEventListener('submit', OperationHandler);
    $('#btnCreate').addEventListener('click', OperationHandler);
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
    /**
     * @return {boolean}
     */
    function OperationHandler(e){
        e.preventDefault();

        btnSubmit.classList.remove('create');
        btnSubmit.classList.remove('update');
        btnSubmit.classList.remove('delete');

        switch(e.target.id){           
            case 'btnCreate':
                btnSubmit.value = 'Create';
                currentOper = 'Create';

                $('#frmEditor').reset();
                btnSubmit.classList.add('create');
                secDataEditor.classList.add('show');
                break;
            
            case 'btnUpdate':
                if(!SelectedRow){
                    ShowMessage('Please select a row first', 'warning');
                    return false;
                }

                btnSubmit.value = 'Update';
                currentOper = 'Update';

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'credit_card_type_id'));
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'credit_card_type_id'));
                btnSubmit.classList.add('delete');
                secDataEditor.classList.add('show');
                break;
                
            case 'btnRefresh':
                btnSubmit.value = 'No Operation';
                currentOper = 'Read';

            case 'frmEditor':
                let data = {};

                if(['Create', 'Update'].indexOf(currentOper)>-1){
                    data = {
                            credit_card_type_id:    $('#credit_card_type_id').value,
                            credit_card_type:       $('#credit_card_type').value,
                            display_order:          $('#display_order').value,
                            status:                 $('#status').value
                        }
                }

                if(currentOper == 'Delete'){
                    data = {credit_card_type_id:$('#credit_card_type_id').value};
                }

                // Temporarily store current row index and operation until we receive the response
                let dbOper = currentOper;
                let rowIndex = -1;

                if(SelectedRow){
                    // The row index in the table body
                    rowIndex = SelectedRow.rowIndex-1;
                }
                
                // Send Ajax request
                Ajax('POST', '/api/CreditCardType/' + dbOper,
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
                            
                            case 'Create':
                                AddRow(tblData, resp.data);
                                break;
                                
                            case 'Update':
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
        Ajax('POST', '/api/CreditCardType/Read/' + id,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#credit_card_type_id').value =    r.credit_card_type_id;
                $('#credit_card_type').value =  r.credit_card_type;
                $('#display_order').value =  r.display_order;
                $('#status').value =  r.status;
            });
    }

    $('#btnRefresh').click();
</script>
