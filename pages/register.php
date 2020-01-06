<section class="register-section">
    <form id="frmRegister">
        
        <h3>Create Account</h3>

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
            <input type="email" id="email">
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" id="password" autocomplete="off">
        </p>
        <button type="submit" class="btn btn-pill btn-primary form-operations">Register</button>
 
    </form>
</section>

<script>
    function Register(e){
        e.preventDefault();

        // Send Ajax request
        Ajax('POST', '/api/User/Register',
            {
                first_name: $('#first_name').value,
                last_name:  $('#last_name').value,
                email:      $('#email').value,
                password:   $('#password').value
            },

            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                setTimeout(function(){
                    document.location.href = resp.redirect;
                }, 1000);
            }
        )
    }

    $('#frmRegister').addEventListener('submit', Register);
</script>
