<h3 class="divider">Contact Us</h3>

<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto mt-4 mb-4">
            
                <form class="col-lg-12" method="post">
                    <fieldset>
                        
                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="fname" name="name" type="text" placeholder="First Name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="lname" name="name" type="text" placeholder="Last Name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 offset-md-1">
                                <input id="email" name="email" type="text" placeholder="Email Address" class="form-control" required>
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
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-info btn-sm">Submit</button>
                            </div>
                        </div>
                    </fieldset>
                </form>  
        </div>

        <div id="map1" class="map col-9 mx-auto"></div>

   </div>
</div>

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


<script src="http://maps.google.com/maps/api/js?sensor=false"></script>

<script type="text/javascript">
    jQuery(function ($) {
        function init_map1() {
            var myLocation = new google.maps.LatLng(38.885516, -77.09327200000001);
            var mapOptions = {
                center: myLocation,
                zoom: 16
            };
            var marker = new google.maps.Marker({
                position: myLocation,
                title: "Property Location"
            });
            var map = new google.maps.Map(document.getElementById("map1"),
                mapOptions);
            marker.setMap(map);
        }
        init_map1();
    });
</script>
