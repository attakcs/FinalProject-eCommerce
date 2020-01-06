<style>
    .map {
        min-width: 300px;
        min-height: 300px;
        width: 100%;
        height: 100%;
    }

    .header {
        background-color: #F5F5F5;
        color: #36A0FF;
        height: 70px;
        font-size: 27px;
        padding: 10px;
    }
</style>

<h3 class="divider">Contact Us</h3>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto mt-4 mb-4">
            
                <form class="col-lg-12" id="frmContact">
                    <fieldset>
                        
                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="first_name" type="text" placeholder="First Name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="last_name" type="text" placeholder="Last Name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="email" name="email" type="email" placeholder="Email Address" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="phone" name="phone" type="text" placeholder="Phone" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <textarea class="form-control" id="message" name="message" placeholder="Drop here a few lines..." rows="7" required></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-12 text-xs-center">
                                <button type="submit" class="btn btn-info btn-sm">Submit</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
          
        </div>
        <div id="map1" class="map">
                        </div>
        
    </div>
</div>
<div class="department-hours">
    <h3>Department Hours:</h3>

    <div>
        <p><strong>Sales</strong></p>
        <p>Mon-Fri: 9.00am to 5.30pm GMT</p>
        <p>Sat-Sun: Closed</p>
    </div>
    <div>
        <p><strong>Billing</strong></p>
        <p>Mon-Fri: 9.00am to 5.00pm GMT</p>
        <p>Sat-Sun: Closed</p>
    </div>

    <div>
        <p><strong>Support</strong></p>
        <p>24 hours a day</p>
        <p>7 days a week</p>
    </div>
</div>


<script src="http://maps.google.com/maps/api/js?sensor=false"></script>

<script>
$('#frmContact').addEventListener('submit', function(e){
    e.preventDefault();
    const firstName = $('#first_name').value;
    const lastName = $('#last_name').value;
    Ajax('POST', '/api/Mailer/Send',
        {
            subject: `Customer Support`,
            message: $('#message').value,
            params: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email: $('#email').value,
                phone: $('#phone').value
            })
        },
        function(resp){
            if(ErrorInResponse(resp)){
                return false;
            }
        
            $('#frmContact').reset();
            setTimeout(function(){
                    document.location.href = resp.redirect;
                }, 2000);
        });
});
function init_map1() {
    var myLocation = new google.maps.LatLng(38.885516, -77.09327200000001);
    var mapOptions = {
        center: myLocation,
        zoom: 16
    };
    var marker = new google.maps.Marker({
        position: myLocation,
        title: "<?= WEBSITE_TITLE?>"
    });
    var map = new google.maps.Map(document.getElementById("map1"),
        mapOptions);
    marker.setMap(map);
}
init_map1();
</script>
