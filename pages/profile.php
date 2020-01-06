<h3 class="divider">Update Your Profile</h3>

<section class="register-section">
    <form id="frmProfile">
        
        <p>
            <img src="" class="user-photo" id="user_photo">
            <input type="file" id="photo" name="photo">
        </p>
        <p>
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name">
        </p>
        <p>
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name">
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="off">
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

        <p class="form-operations">
            <?php if(IsCustomer()){?>
                <button class="button delete btn btn-pill btn-danger" type="button" id="btnDelete">Delete My Account</button>
            <?php }?>

            <button class="button update btn btn-pill btn-info" type="submit" id="btnUpdate">Update  &#x21A5;</button>
        </p>
    </form>
</section>

<script>
    // Using FormData requires using names for input fields 
    $('#frmProfile').addEventListener('submit', function(e){
            e.preventDefault();

            // Send Ajax request
            Ajax('POST', '/api/User/UpdateProfile',
                new FormData($('#frmProfile')),

                function(resp){
                    if(ErrorInResponse(resp)){
                        return false;
                    }

                    $('#user_photo').src = '/api/User/Photo';
                }
            )
        });

<?php if(IsCustomer()){?>
    $('#btnDelete').addEventListener('click', function(e){
            e.preventDefault();

            if(!confirm("This will delete your account and all its related public data")){
                return false;
            }

            // Send Ajax request
            Ajax('POST', '/api/User/DeleteProfile',
                null,

                function(resp){
                    if(ErrorInResponse(resp)){
                        return false;
                    }

                    setTimeout(function(){
                        document.location.href = resp.redirect;
                    }, 1000);
                }
            )
        });
<?php }?>

    function FillForm(id){
        Ajax('POST', '/api/User/Profile',
            null,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                let r = resp.data[0];
                $('#first_name').value =    r.first_name;
                $('#last_name').value =     r.last_name;
                $('#email').value =         r.email;
                $('#password').value =      '';
                $('#user_photo').src =      '/api/User/Photo';
                $('#address').value =       r.address;
                $('#address').value =       r.address;
                $('#address2').value =      r.address2;
                $('#address2').value =      r.address2;
                $('#country_id').value =    r.country_id;
                $('#country_id').value =    r.country_id;
                $('#state_id').value =      r.state_id;
                $('#state_id').value =      r.state_id;
                $('#zip').value =           r.zip;
            });
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

                country.value = parseInt(<?= GetUser('country_id') ?>);
                LoadStates(<?= GetUser('state_id') ?>);
            });
    }

    // Loading user data
    FillForm();
    LoadCountries();
</script>
