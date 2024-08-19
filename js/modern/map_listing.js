"use strict";

(function(A) {
	if (!Array.prototype.forEach)
		A.forEach = A.forEach || function(action, that) {
			for (var i = 0, l = this.length; i < l; i++)
				if (i in this)
					action.call(that, this[i], i, this);
			};

})(Array.prototype);



var markers = [];

function renderMap( _center, markersData, zoom, mapType, mapTypeControl, icon_url, mapId ) {
	var mapObject;
	var mapOptions = {
		zoom: zoom,
		center: new google.maps.LatLng(_center[0], _center[1]),
		mapTypeId: mapType,

		mapTypeControl: mapTypeControl,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			position: google.maps.ControlPosition.TOP_LEFT
		},
		panControl: false,
		panControlOptions: {
			position: google.maps.ControlPosition.TOP_RIGHT
		},
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.LARGE,
			position: google.maps.ControlPosition.RIGHT_BOTTOM
		},
		scrollwheel: false,
		scaleControl: true,
		scaleControlOptions: {
			position: google.maps.ControlPosition.LEFT_CENTER
		},
		streetViewControl: true,
		streetViewControlOptions: {
			position: google.maps.ControlPosition.RIGHT_BOTTOM
		},
		styles: [
											 {
			"featureType": "landscape",
			"stylers": [
					{
						"hue": "#FFBB00"
					},
					{
						"saturation": 43.400000000000006
					},
					{
						"lightness": 37.599999999999994
					},
					{
						"gamma": 1
					}
				]
			},
			{
				"featureType": "road.highway",
				"stylers": [
					{
						"hue": "#FFC200"
					},
					{
						"saturation": -61.8
					},
					{
						"lightness": 45.599999999999994
					},
					{
						"gamma": 1
					}
				]
			},
			{
				"featureType": "road.arterial",
				"stylers": [
					{
						"hue": "#FF0300"
					},
					{
						"saturation": -100
					},
					{
						"lightness": 51.19999999999999
					},
					{
						"gamma": 1
					}
				]
			},
			{
				"featureType": "road.local",
				"stylers": [
					{
						"hue": "#FF0300"
					},
					{
						"saturation": -100
					},
					{
						"lightness": 52
					},
					{
						"gamma": 1
					}
				]
			},
			{
				"featureType": "water",
				"stylers": [
					{
						"hue": "#0078FF"
					},
					{
						"saturation": -13.200000000000003
					},
					{
						"lightness": 2.4000000000000057
					},
					{
						"gamma": 1
					}
				]
			},
			{
				"featureType": "poi",
				"stylers": [
					{
						"hue": "#00FF6A"
					},
					{
						"saturation": -1.0989010989011234
					},
					{
						"lightness": 11.200000000000017
					},
					{
						"gamma": 1
					}
				]
			}
		]
	};
	var marker;
	var cluster_marker = [];
	var bounds = new google.maps.LatLngBounds();

	if ( mapId == undefined ) { 
		mapObject = new google.maps.Map( document.getElementById('map_listing'), mapOptions );
	} else { 
		mapObject = new google.maps.Map( document.getElementById(mapId), mapOptions );
	}

	//var icon_url = '';
	for (var key in markersData) {
		markersData[key].forEach(function (item) {
			//icon_url = theme_url + '/images/pins/' + item.type + '.png';

			/*if ( item.type == 'Tours' && typeof tour_icon != 'undefined' ) { 
				icon_url = tour_icon;
			} else if ( item.type == 'Accommodation' && typeof hotel_icon != 'undefined' ) { 
				icon_url = hotel_icon;
			}*/

			marker = new google.maps.Marker({
				position: new google.maps.LatLng(item.location_latitude, item.location_longitude),
				map: mapObject,
				icon: icon_url,
				title: item.name,
			});

			var myLatLng = new google.maps.LatLng(item.location_latitude, item.location_longitude);
			bounds.extend(myLatLng);

			if ('undefined' === typeof markers[key]) {
				markers[key] = [];
			}

			markers[key].push(marker);
			cluster_marker.push(marker);
			
			google.maps.event.addListener(marker, 'click', (function () {
				closeInfoBox();
				getInfoBox(item).open(mapObject, this);
				mapObject.setCenter(new google.maps.LatLng(item.location_latitude, item.location_longitude));
			}));
		});
	}

	new MarkerClusterer(mapObject, cluster_marker);

	function hideAllMarkers () {
		for (var key in markers) {
			markers[key].forEach(function (marker) {
				marker.setMap(null);
			});
		}
	};
		
	function toggleMarkers (category) {
		hideAllMarkers();
		closeInfoBox();

		if ('undefined' === typeof markers[category])
			return false;
		markers[category].forEach(function (marker) {
			marker.setMap(mapObject);
			marker.setAnimation(google.maps.Animation.DROP);

		});
	};

	function closeInfoBox() {
		jQuery('div.infoBox').remove();
	};

	function getInfoBox(item) {
		return new InfoBox({
			content:
			'<div class="marker_info">' +
			'<figure><a href='+ item.url_point +'>' + item.map_image + '</a></figure>' +
			'<div class="marker-infobox">' +
			'<h3 class="title"><a href='+ item.url_point +'>'+ item.name +'</a></h3>' +
			'<span class="description">'+ item.location +'</span>' +
			'<div class="rating-part">' +
			'<div class="five-stars-container">' +
			'<span class="five-stars" style="width: '+ 100/5*item.rate +'%;"></span>' +
			'</div>' +
			'<span class="review-number">'+ item.review_number +' reviews</span>' +
			'</div>' +
			'<div class="price-field">'+ item.price +' <span>/ '+ item.price_unit +'</span></div>' +
			'</div>',
			disableAutoPan: false,
			maxWidth: 0,
			pixelOffset: new google.maps.Size(10, 105),
			closeBoxMargin: '',
			closeBoxURL: item.closeBoxURL,
			isHidden: false,
			alignBottom: true,
			pane: 'floatPane',
			enableEventPropagation: true
		});
	};
}

function onHtmlClick(key){
	jQuery('#collapseMap').collapse('show');
    google.maps.event.trigger(markers[key][0], "click");
}