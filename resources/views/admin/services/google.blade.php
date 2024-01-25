
@foreach($data as $row)
<div class="row">
    <div class="col-12 col-lg-12 mx-auto">
        <div class=" " style="height: 680px;background:transparent;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">Ruta del servicio</h6>
                    </div>
                </div>

                <div class="chart-container-2  mt-4">
                  <div id="map" style="width:100%;height:600px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let lat = 31.326015;
let lng = 75.576180;
  function initMap() {
    var map;
    var marker_origin;
    var marker_destin;
    const directionsService = new google.maps.DirectionsService();
    const directionsRenderer = new google.maps.DirectionsRenderer();
    
    map = new google.maps.Map(document.getElementById('map'),{
            center: {lat: lat, lng: lng},
            zoom: 10,
            disableDefaultUI: true
          });
    
    directionsRenderer.setMap(map);

    calculateAndDisplayRoute(directionsService, directionsRenderer);
    
    marker_origin = new google.maps.Marker({
      position: {lat: lat, lng: lng},
      map: map,
      icon: {
        min: 2, 
        max: 100, url: "https://deliverygo.grupoorus.mx/assets/images/icons/icon_polyline.png", 
        anchor: {x: 16, y: 16}
      },
    });

    marker_destin = new google.maps.Marker({
      position: {lat: lat, lng: lng},
      map: map,
      icon: {
        min: 2, 
        max: 100, url: "https://deliverygo.grupoorus.mx/assets/images/icons/icon_polyline.png", 
        anchor: {x: 16, y: 16}
      }
    }); 
  }

  function calculateAndDisplayRoute(directionsService, directionsRenderer) {
    directionsService
    .route({
      origin: {
        query: document.getElementById("address_origin").value,
      },
      destination: {
        query: document.getElementById("address_destin").value,
      },
      travelMode: google.maps.TravelMode.DRIVING,
    })
    .then((response) => {
      directionsRenderer.setDirections(response);
    })
    .catch((e) => window.alert("Directions request failed due to " + status));
  }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{$admin->ApiKey_google}}&libraries=places&callback=initMap"></script>
@endforeach