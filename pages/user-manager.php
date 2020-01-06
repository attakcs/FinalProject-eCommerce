<h3 class="divider">User Manager</h3>

<section class="data-editor">
    <form id="frmEditor">
        

        <p>
            <img src="" class="user-photo" id="user_photo">
        </p>
        <p>
            <label for="user_id">ID</label>
            <input type="number" id="user_id" readonly>
        </p>
        <p>
            <label for="first_name">First Name</label>
            <input type="text" id="first_name">
        </p>
        <p>
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name">
        </p>
        <p>
            <label for="email">Email</label>
            <input type="text" id="email">
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" id="password" autocomplete="off">
        </p>
        <p>
            <label for="address">Address</label>
            <input type="text" id="address" name="address">
        </p>
        <p>
            <label for="address2">Address 2</label>
            <input type="text" id="address2" name="address2">
        </p>
        <div class="form-group">
            <p>
                <label for="country_id">Country</label>
                <select id="country_id" name="country_id" required></select>
            </p>
            <p>
                <label for="state_id">State</label>
                <select id="state_id" name="state_id" required></select>
            </p>
            <p>
                <label for="zip">Zip</label>
                <input type="text" id="zip" name="zip" required>
            </p>
        </div>
        <p>
            <label for="user_group">User Group</label>
            <select id="user_group">
                <option value="Customer">Customer</option>
                <option value="Administrator">Administrator</option>
            </select>
        </p>
        <p>
            <label for="status">Status</label>
            <select id="status">
                <option value="Active">Active</option>
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
        <button class="button create btn btn-pill btn-success" id="btnCreate">Create &#10514;</button>
        <button class="button update btn btn-pill btn-info" id="btnUpdate">Update  &#x21A5;</button>
        <button class="button delete btn btn-pill btn-danger" id="btnDelete">Delete &#x2BBF;</button>
        <button class="button refresh btn btn-pill btn-dark" id="btnRefresh">Refresh &#x21BA;</button>
    </div>

    <table id="tblData">
        <thead>
            <tr>
                <th data-model="user_id">ID</th>
                <th data-model="first_name">First Name</th>
                <th data-model="last_name">Last Name</th>
                <th data-model="email">Email</th>
                <th data-model="user_group">User Group</th>
                <th data-model="date_registered">Date Registered</th>
                <th data-model="status">status</th>
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
                $('#user_photo').src = '/api/User/Photo/x';
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'user_id'));
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

                FillForm(GetCellValue(tblData, SelectedRow.rowIndex-1, 'user_id'));
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
                            user_id:    $('#user_id').value,
                            first_name: $('#first_name').value,
                            last_name:  $('#last_name').value,
                            email:      $('#email').value,
                            password:   $('#password').value,
                            address:    $('#address').value,
                            address2:   $('#address2').value,
                            state_id:   $('#state_id').value,
                            zip:        $('#zip').value,
                            user_group: $('#user_group').value,
                            status:     $('#status').value
                        }
                }

                if(currentOper == 'Delete'){
                    data = {user_id:$('#user_id').value};
                }

                // Temporarily store current row index and operation until we receive the response
                let dbOper = currentOper;
                let rowIndex = -1;

                if(SelectedRow){
                    // The row index in the table body
                    rowIndex = SelectedRow.rowIndex-1;
                }
                
                // Send Ajax request
                Ajax('POST', '/api/User/' + dbOper,
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

// Country, States
    const country = $('#country_id');
    const state = $('#state_id');
    
    country.addEventListener('change', LoadStates);

    function LoadStates(selectedID){
        selectedID = selectedID||0;

        state.innerHTML = '';

        Ajax('POST', '/api/State/Read',
        {country_id: country.value},

        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }

            for(let s of resp.data){
                let o = new Option(s.state, s.state_id);
                state.appendChild(o);
            }

            if(selectedID > 0){
                state.value = selectedID
            }
        });
    }

    // Load countries
    function LoadCountries(){
        Ajax('POST', '/api/Country/Read',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                for(let c of resp.data){
                    let o = new Option(c.country, c.country_id);
                    country.appendChild(o);
                }

                LoadStates();
            });
    }

    function FillForm(id){
        Ajax('POST', '/api/User/Read/' + id,
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#user_id').value =       r.user_id;
                $('#first_name').value =    r.first_name;
                $('#last_name').value =     r.last_name;
                $('#email').value =         r.email;
                $('#password').value =      '';
                $('#address').value =       r.address;
                $('#address2').value =      r.address2;
                $('#country_id').value =    r.country_id;
                $('#zip').value =           r.zip;
                $('#user_group').value =    r.user_group;
                $('#user_photo').src =      '/api/User/Photo/' + r.photo;

                LoadStates(r.state_id);
            });
    }

    $('#btnRefresh').click();
    LoadCountries();
</script>
