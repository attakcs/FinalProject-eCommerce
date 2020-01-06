<h3 class="divider">Review Manager</h3>

<section class="data-editor">
    <form id="frmEditor">
        

        <p>
            <label for="review_id">ID</label>
            <input type="number" id="review_id" name="review_id" readonly>
        </p>
        <p>
            <label for="review">review</label>
            <textarea id="review" name="review"></textarea>
        </p>
        <p>
            <label for="stars">stars</label>
            <input type="number" id="stars" name="stars" min="0" max="5">
        </p>
        <p>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Banned">Banned</option>
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
    </div>

    <table id="tblData">
        <thead>
            <tr>
                <th data-model="review_id">ID</th>
                <th data-model="product">Product</th>
                <th data-model="customer">Customer</th>
                <th data-model="review">Review</th>
                <th data-model="stars">stars</th>
                <th data-model="status">Status</th>
                <th data-model="date_added">Date</th>
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
            case 'btnUpdate':
                if(!SelectedRow){
                    ShowMessage('Please select a row first', 'warning');
                    return false;
                }

                btnSubmit.value = 'Update';
                currentOper = 'Update';

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'review_id'));
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'review_id'));
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
                            review_id:  $('#review_id').value,
                            review:     $('#review').value,
                            stars:      $('#stars').value,
                            status:     $('#status').value
                        }
                }

                if(currentOper == 'Delete'){
                    data = {review_id:$('#review_id').value};
                }

                // Temporarily store current row index and operation until we receive the response
                let dbOper = currentOper;
                let rowIndex = -1;

                if(SelectedRow){
                    // The row index in the table body
                    rowIndex = SelectedRow.rowIndex-1;
                }
                
                // Send Ajax request
                Ajax('POST', '/api/Review/' + dbOper,
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
        Ajax('POST', '/api/Review/Read/' + id,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#review_id').value =     r.review_id;
                $('#review').value =        r.review;
                $('#stars').value =         r.stars;
                $('#status').value =        r.status;
            });
    }

    $('#btnRefresh').click();
</script>
