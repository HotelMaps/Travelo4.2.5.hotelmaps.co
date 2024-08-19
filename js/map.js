"use strict";

var markers = [];

function renderMap( _center, markersData, zoom, mapType, mapTypeControl, icon_url, mapId ) {
	var mapObject;
	var mapOptions = {
		zoom: zoom,
		center: new google.maps.LatLng(_center[0], _center[1]),
		mapTypeId: mapType,

		mapTypeControl: mapTypeControl,
		mapTypeControlOptions: {
			// style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
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
			position: google.maps.ControlPosition.TOP_LEFT
		},
		streetViewControl: true,
		streetViewControlOptions: {
			position: google.maps.ControlPosition.RIGHT_BOTTOM
		},
		styles: [/*map styles*/]
	};
	var marker;

	if ( mapId == undefined ) { 
		mapObject = new google.maps.Map( document.getElementById('map'), mapOptions );
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

			if ('undefined' === typeof markers[key])
				markers[key] = [];
			markers[key].push(marker);
			google.maps.event.addListener(marker, 'click', (function () {
				closeInfoBox();
				getInfoBox(item).open(mapObject, this);
				mapObject.setCenter(new google.maps.LatLng(item.location_latitude, item.location_longitude));
			}));
		});
	}

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
			'<div class="marker_info" id="marker_info">' +
			item.map_image +
			'<h3><a href="'+ item.url_point + '">'+ item.name_point +'</a></h3>' +
			'<p>'+ item.description_point +'</p>' +
			'</div>',
			disableAutoPan: true,
			maxWidth: 0,
			pixelOffset: new google.maps.Size(30, -210),
			closeBoxMargin: '5px -20px 2px 2px',
			closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
			isHidden: false,
			pane: 'floatPane',
			enableEventPropagation: true,
		});
	};
}

function onHtmlClick(key){
	jQuery('#collapseMap').collapse('show');
    google.maps.event.trigger(markers[key][0], "click");
}