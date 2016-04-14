var map;
var bounds;

$(window).load(function(){
	bounds = new google.maps.LatLngBounds();
	
	var myOptions = {
	  zoom: 12,
	  center: new google.maps.LatLng(Avgs[0],Avgs[1]),
	  zoomControl:true,
	  streetViewControl:false,
	  mapTypeControl:false,
	  mapTypeId: google.maps.MapTypeId.MapType
	};
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
	for(var i = 0; i < address_objects.length; i++){
		addMarker(address_objects[i]);
	}	
	if(address_objects.length>1)map.fitBounds(bounds);
});

function addMarker(object) {
	var latlng = new google.maps.LatLng(object.LatLng[0], object.LatLng[1]);
	bounds.extend(latlng);
	var marker = new google.maps.Marker({
		map: map,
		position: latlng,
		title:object.Title/*,
		icon:'/themes/mysite/images/custom_icon.png'*/
	});
	var heading = object.Title ? "<h5>"+object.Title+"</h5>" : "";
	var contentString = '<div id="map-popup">'+heading+'<p>'+object.Address+'</p><p><a href="https://maps.google.com/maps?q='+object.Address+'" target="_blank">Get Driving Directions</a></p></div>';
	var infowindow = new google.maps.InfoWindow({
		content: contentString
	});
	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map,marker);
	});
}

function getDirections(){
	$.ajax({
			url: PageLink+"directions/"+$('#to_address').val()+"/"+$('#from_address').val(),
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