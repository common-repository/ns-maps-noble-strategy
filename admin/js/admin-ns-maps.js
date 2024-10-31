$ = jQuery;
$(document).ready( function(){
	
	$('.lat').prop('disabled', true);
	$('.long').prop('disabled', true);
	$('.addr_bo').prop('disabled', true);
	
	$("#lat").keydown(function (e) {
		// Allow: delete, backspace, num-pad . , keyboard . , keyboard - , numpad - 
	    if ($.inArray(e.keyCode, [46, 8, 110, 190, 173, 109]) !== -1 || (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || (e.keyCode >= 35 && e.keyCode <= 40)) {
	    	return;
	    }
	    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) || (e.keyCode == 59 || e.keyCode == 188 )) {
	    	e.preventDefault();
		}   
	});

	$("#long").keydown(function (e) {
		// Allow: delete, backspace, num-pad . , keyboard . , keyboard - , numpad - 
	    if ($.inArray(e.keyCode, [46, 8, 110, 190, 173, 109]) !== -1 || (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || (e.keyCode >= 35 && e.keyCode <= 40)) {
	    	return;
	    }
	    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) || (e.keyCode == 59 || e.keyCode == 188 )) {
	    	e.preventDefault();
		}   
	});

	var file_frame;
	$('#upload_image_button').live('click', function(podcast){
		podcast.preventDefault();
		
		if (file_frame){
			file_frame.open();
			return;
		}

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $(this).data('uploader_title'),
			button: {
				text: $(this).data('uploader_button_text'),
			},
			multiple: false
		});

		file_frame.on('select', function(){
			attachment = file_frame.state().get('selection').first().toJSON();

			var url = attachment.url;
			var field = document.getElementById("ns_maps_icon");

			field.value = url;
		});

		file_frame.open();
	});

    $("#add_marker").click(function(e){
		e.preventDefault();
		var lat = $('.lat_0').val();
		var lon = $('.long_0').val();
		var addr = $('.addr_0').val();
		var desc = $('.desc_0').val();
		var map_id = $('input[name="map_id"]').val();
		
		if ( (lat == '') || (lon == '' ) || (addr == '') ){
			alert("Complete all fields!");
		} else {
			jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				data: { 
					action: 'add_new_marker',
					id: map_id,
					lat: lat,
					lon: lon,
					addr: addr,
					desc: desc,
				},
				success : function( response ){
					if (response == "Success"){
						location.reload();
					} else {
						console.log("Error");
					}
				}
			});
		}
    });
	
    $(".remove_marker").click(function(e){
    	e.preventDefault();
    	var marker_id = $(this).attr('data-id');
        jQuery.ajax({
		    type: "POST",
		    url: ajaxurl,
		    data: { 
		    	action: 'remove_marker',
		    	id: marker_id
		    },
		    success : function( response ){
                location.reload();
            }
		 });
    });
	
    $(".save_marker").click(function(e){
    	e.preventDefault();
    	var marker_id = $(this).attr('data-id');
    	var addr = $('.addr_'+marker_id).val();
    	var desc = $('.desc_'+marker_id).val();
		var lat = $('.lat_0').val();
		var lon = $('.long_0').val();
        jQuery.ajax({
		    type: "POST",
		    url: ajaxurl,
		    data: { 
		    	action: 'save_marker',
		    	id: marker_id,
		    	lat: lat,
		    	lon: lon,
		    	addr: addr,
		    	desc: desc,
		    },
		    success : function( response ){
                location.reload();
            }
		 });
    });
	
    $(".addr").on("keyup", function(){
    	var value = $(this).val();
    	var data_id = $(this).attr('data-id');
        addr_search(value, data_id);       
    });

    $(".addr_center").on("keyup", function(){
		var value = $(this).val();
        addr_center(value);       
    });

});

function coordToAddr(tbClass,lat,lng){
	
	var xmlhttp = new XMLHttpRequest();
	var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + lat + "+" + lng;

	xmlhttp.onreadystatechange = function(){
		if (this.readyState == 4 && this.status == 200)
   		{
			var myArr = JSON.parse(this.responseText);
			for(i = 0; i < myArr.length; i++)
			{
				$(".addr_" + tbClass).val(myArr[i].display_name);
			}
		}
	};
	xmlhttp.open("GET", url, true);
 	xmlhttp.send();
}

/* Create BO Map */
var startlat = $('input[name="addr_center_lat"]').val();
var startlon = $('input[name="addr_center_lon"]').val();

//var startaddr = '<?=$addr?>';
var starticon = $('input[name="ns_maps_icon"]').val();
var startzoom = $('input[name="zoom"]').val();

//Icon size

var icon_size = new_icon_size;


var options = {
	center: [startlat, startlon],
	zoom: startzoom
}

var map = L.map('map', options);

map.attributionControl.setPrefix(false);

var nzoom = 12;

L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
	attribution: "&copy <a href='https://www.noblestrategy.pt'>Noble Strategy</a>"
}).addTo(map);

var LeafIcon = L.Icon.extend({ options: { iconSize:[icon_size, icon_size] } });

var greenIcon = new LeafIcon({iconUrl: starticon});

var iconNewMarker = new LeafIcon({iconUrl: newMarker, iconSize: [icon_size, icon_size]});

var markers_lat_lon = [];
if (ns_markers.length > 0) {
	for(var i = 0; i < ns_markers.length; i++) {
		var myMarker = new L.marker([ns_markers[i][1],ns_markers[i][2]], {
			icon: greenIcon,
			draggable: "true"
		})
		.bindPopup(ns_markers[i][3])
		.addTo(map);
		
		var lat = ns_markers[i][1];
		var lng = ns_markers[i][2];
		var addr = ns_markers[i][4];
		
		coordToAddr(addr,lat,lng);
	}

	var myMarker = new L.marker([startlat,startlon], {
		icon: iconNewMarker,
		draggable: "true"
	})
	.bindPopup('Add Marker')
	.addTo(map);		
} 
else {
	var myMarker = new L.marker([startlat,startlon], {
		icon: iconNewMarker,
		draggable: "true"
	})
	.bindPopup('Add Marker')
	.addTo(map);
}

myMarker.on('dragend', function(event) {

	$('.lat_0').val(myMarker.getLatLng().lat.toFixed(8));
	$('.long_0').val(myMarker.getLatLng().lng.toFixed(8));
	
	var position = myMarker.getLatLng();
	
	myMarker.setLatLng(position, {
		draggable: 'true'
    })
});
































function chooseAddr(lat1, lng1, data_id)
{
	myMarker.closePopup();
	map.setView([lat1, lng1],18);
	myMarker.setLatLng([lat1, lng1]);
	lat = lat1.toFixed(8);
	lon = lng1.toFixed(8);

	document.getElementById('lat').value = lat;
	document.getElementById('long').value = lon;
 	myMarker.bindPopup("Lat " + lat + "<br />Lon " + lon).openPopup();
}



function myFunction(arr, data_id)
{
	var out = "<br />";
	var i;

	if(arr.length > 0)
 	{
		for(i = 0; i < arr.length; i++)
  		{
   			out += "<div class='address' style='cursor:pointer' title='Show Location and Coordinates' onclick='chooseAddr(" + arr[i].lat + ", " + arr[i].lon + ", " + data_id + ");return false;'>" + arr[i].display_name + "</div>";
  		}
  		//document.getElementById('results').innerHTML = out;
  	$('.results_'+data_id).html(out);
 	} else {
  		//document.getElementById('results').innerHTML = "Sorry, no results...";
  		$('.results_'+data_id).html("Sorry, no results...");
 	}
}



function addr_search(value, data_id)
{
	var inp = value;
	var xmlhttp = new XMLHttpRequest();
	var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + inp;
	xmlhttp.onreadystatechange = function()
	{
   		if (this.readyState == 4 && this.status == 200)
   		{
    		var myArr = JSON.parse(this.responseText);
    		myFunction(myArr, data_id);
   		}
 	};
 	xmlhttp.open("GET", url, true);
 	xmlhttp.send();
}



function addr_center(value) {
	var inp = value;
	var xmlhttp = new XMLHttpRequest();
	var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + inp;
	xmlhttp.onreadystatechange = function()
	{
	   if (this.readyState == 4 && this.status == 200)
	   {
	    var myArr = JSON.parse(this.responseText);
	    get_map_center(myArr);
	   }
	};
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}



function get_map_center(arr)
{
	var out = "<br />";
	var i;

	if(arr.length > 0)
 	{
  		for(i = 0; i < arr.length; i++)
		{
   			out += "<div class='address' style='cursor:pointer' title='Show Location and Coordinates' onclick='chooseAddrCenter(" + arr[i].lat + ", " + arr[i].lon + ");return false;'>" + arr[i].display_name + "</div>";
  		}
  		//document.getElementById('results').innerHTML = out;
  		$('.results_center').html(out);
 	}
 	else
 	{
  		//document.getElementById('results').innerHTML = "Sorry, no results...";
  		$('.results_center').html("Sorry, no results...");
 	}
}



function chooseAddrCenter(lat1, lng1)
{
	$('input[name="addr_center_lat"]').val(lat1);
	$('input[name="addr_center_lon"]').val(lng1);
}


