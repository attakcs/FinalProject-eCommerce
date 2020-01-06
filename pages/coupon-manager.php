<h3 class="divider">Coupon Manager</h3>
    
<section class="data-editor">
    <form id="frmEditor">
        <p>
            <label for="coupon_id">ID</label>
            <input type="number" id="coupon_id" name="coupon_id" readonly>
        </p>
        <p>
            <label for="coupon">Coupon</label>
            <input type="text" id="coupon" name="coupon">
        </p>
        <p>
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </p>
        <p>
            <label for="discount">Discount</label>
            <input type="number" id="discount" name="discount" min="0" max="100">
        </p>
        <p>
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date">
        </p>
        <p>
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date">
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
                <th data-model="coupon_id">ID</th>
                <th data-model="coupon">Coupon</th>
                <th data-model="description">Description</th>
                <th data-model="discount">Discount</th>
                <th data-model="start_date">Sart Date</th>
                <th data-model="end_date">End Date</th>
                <th data-model="uses">Uses</th>
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'coupon_id'));
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'coupon_id'));
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
                            coupon_id:      $('#coupon_id').value,
                            coupon:         $('#coupon').value,
                            description:    $('#description').value,
                            discount:       $('#discount').value,
                            start_date:     $('#start_date').value,
                            end_date:       $('#end_date').value
                        }
                }

                if(currentOper == 'Delete'){
                    data = {coupon_id:$('#coupon_id').value};
                }

                // Temporarily store current row index and operation until we receive the response
                let dbOper = currentOper;
                let rowIndex = -1;

                if(SelectedRow){
                    // The row index in the table body
                    rowIndex = SelectedRow.rowIndex-1;
                }
                
                // Send Ajax request
                Ajax('POST', '/api/Coupon/' + dbOper,
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
        Ajax('POST', '/api/Coupon/Read/' + id,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#coupon_id').value =     r.coupon_id;
                $('#coupon').value =        r.coupon;
                $('#description').value =   r.description;
                $('#discount').value =      r.discount;
                $('#discount').value =      r.discount;
                $('#start_date').value =    r.start_date;
                $('#end_date').value =      r.end_date;
            });
    }

    $('#btnRefresh').click();
</script>
