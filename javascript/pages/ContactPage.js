if (typeof(google) != "undefined"){
	var geocoder;
	var map;
	geocoder = new google.maps.Geocoder();
	$(window).load(function(){
		var MapTypeString = "google.maps.MapTypeId."+MapType;
		var myOptions = {
		  zoom: MapZoom,
		  zoomControl:false,
		  streetViewControl:false,
		  mapTypeControl:false,
		  mapTypeId: eval(MapTypeString)
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		codeAddress()
	});
	
	function codeAddress() {
		var address = MapAddress;
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					title:"Our Location"
				});
				var window_bottom = MapDirections ? '' : '<a href="https://maps.google.com/maps?q='+MapAddress+'" target="_blank">Get Driving Directions</a>';
				var contentString = '<div id="map-popup"><h5>'+MapLocationTitle+'</h5><div>'+MapAddress+'</div>'+window_bottom+'</div>';
				var infowindow = new google.maps.InfoWindow({
					content: contentString
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map,marker);
				});
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
		});
	}
	
	function getDirections(){
		$.ajax({
				url: PageLink+"directions/"+$('#from_address').val(),
				global: false,
				dataType: "html",
				async: true,
				cache: true,
				success: function(data) {
					$("#directions_ajax").html(data);
					jScroll('directions_top');
				}
			});
	}

	$(document).ready(function(){
		setupFormField("#from_address", "Enter Your Address");
	});

}