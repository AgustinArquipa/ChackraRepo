var map;
var marker;
var circle;
var geocoder;

function load() {
    geocoder = new google.maps.Geocoder();

    if ($("#field-lat").val() != '' && $("#field-lng").val() != ''){
        var latlng = new google.maps.LatLng($("#field-lat").val(), $("#field-lng").val());
    }else{
        var latlng = new google.maps.LatLng(-24.79920167537382, -65.41740417480463);
    }
    console.log(latlng);
    
    var myOptions = {
        zoom: 18,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.roadmap,
        disableDefaultUI: true,
        //ELIMINO PLACES
        styles: [
            {
                "featureType": "poi",
                "stylers": [
                    { "visibility": "off" }
                ]
            }
        ]
    };
    map = new google.maps.Map(document.getElementById("retailer-map"), myOptions);

    addMarkerMaps(map.getCenter());

    google.maps.event.addListener(map, "click", function(event) {
        addMarkerMaps(event.latLng);
    });

}

function addMarkerMaps(location) {
    if (marker) {
        marker.setMap(null);
    }
    document.getElementById("field-lat").value = location.lat();
    document.getElementById("field-lng").value = location.lng();
    console.log("Coordinates found / Latitude - " + location.lat() + " & longitude - " + location.lng());
    marker = new google.maps.Marker({
        position: location,
        draggable: true
    });
    marker.setMap(map);
    google.maps.event.addListener(marker, "dragend", function(event) {
        newlatlng = event.latLng;
        map.setCenter(newlatlng);
        document.getElementById("field-lat").value = newlatlng.lat();
        document.getElementById("field-lng").value = newlatlng.lng();
    });
}

window.onload = function() {
    load();
}