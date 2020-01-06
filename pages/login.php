<section class="login-section">
    <form id="frmLogin">
        <h3>Login</h3>

        <p>
            <label for="email">Email</label>
            <input type="email" id="email">
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" id="password" autocomplete="off">
        </p>
        <button type="submit" class="btn btn-pill btn-primary form-operations">Login</button>
    </form>
</section>

<script>
    function Login(e){
        e.preventDefault();

        // Send Ajax request
        Ajax('POST', '/api/User/Login',
            {
                email:      $('#email').value,
                password:   $('#password').value
            },

            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                setTimeout(function(){
                    document.location.href = resp.redirect;
                }, 2000);
            }
        )
    }

    $('#frmLogin').addEventListener('submit', Login);
</script>
