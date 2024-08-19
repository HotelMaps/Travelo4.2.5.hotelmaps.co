/*
 * Title:   Travelo | Responsive Wordpress Booking Template - Main Javascript file
 * Author:  http://themeforest.net/user/soaptheme
 */

///////////////////////////////////////////////////////////////////////////////
////////////////////////////////// MAIN FUNCTIONS /////////////////////////////
///////////////////////////////////////////////////////////////////////////////
"use strict";
var tjq = jQuery.noConflict();
var stGlobals = {};
var enableChaser = 1; // Enable Chaser menu (open on scroll) ?   1 - Yes / 0 - No
if ( settings.sticky_menu ) enableChaser = settings.sticky_menu;
stGlobals.isMobile  = (/(Android|BlackBerry|iPhone|iPod|iPad|Palm|Symbian)/.test(navigator.userAgent));
stGlobals.isMobileWebkit = /WebKit/.test(navigator.userAgent) && /Mobile/.test(navigator.userAgent);
stGlobals.isIOS = (/iphone|ipad|ipod/gi).test(navigator.appVersion);

/* middle block plugin(set image in the middle of its parent object) */
(function($) {
	var middleblock;
	var prototype = $.fn;
	middleblock = prototype.middleblock = function() {
		var $this = this;
		if ($(this).is(":visible")) {
			$this.bind("set.middleblock", set_middle_block).trigger('set.middleblock');
		}
		return $this;
	};

	function set_middle_block() {
		var $this = $(this);
		var $middleItem = $this.find(".middle-item");
		if ($middleItem.length < 1) {
			$middleItem = $this.children("img");
		}
		if ($middleItem.length < 1) {
			return;
		}
		var width = $middleItem.width();
		var height = $middleItem.height();
		var parentObj;
		if ($this.width() <= 1) {
			parentObj = $this;
			while (parentObj.width() <= 1) {
				parentObj = parentObj.parent();
			}
			$this.css("width", parentObj.width() + "px");
		}
		$this.css("position", "relative");
		$middleItem.css("position", "absolute");

		if ($this.hasClass("middle-block-auto-height")) {
			$this.removeClass("middle-block-auto-height");
			$this.height(0);
		}
		if ($this.height() <= 1) {
			parentObj = $this;
			while (parentObj.height() <= 1) {
				if (parentObj.css("float") =="left" && parentObj.index() == 0 && parentObj.next().length > 0) {
					parentObj = parentObj.next();
				} else if (parentObj.css("float") == "left" && parentObj.index() > 0) {
					parentObj = parentObj.prev();
				} else {
					parentObj = parentObj.parent();
				}
			}
			$this.css("height", parentObj.outerHeight() + "px");
			$this.addClass("middle-block-auto-height");

			width = $middleItem.width();
			height = $middleItem.height();
			if (height <= 1) {
				height = parentObj.outerHeight();
			}
		}
		$middleItem.css("top", "50%");
		$middleItem.css("margin-top", "-" + (height / 2) + "px");
		if (width >= 1) {
			/*if ($this.width() == width) {
				$this.width(width);
			}*/
			$middleItem.css("left", "50%");
			$middleItem.css("margin-left", "-" + (width / 2) + "px");
		} else {
			$middleItem.css("left", "0");
		}
	}
}(jQuery));

/* calendar */
function Calendar() {
	this.html = "";
}
(function ($) {
	Calendar.prototype.generateHTML = function(Month, Year, notAvailableDays, priceArr) {
		var days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var daynames = new Array( week_days.sunday, week_days.monday, week_days.tuesday, week_days.wednesday, week_days.thursday, week_days.friday, week_days.saturday );
		var today = new Date();
		var thisDay = today.getDate();
		var thisMonth = today.getMonth();
		var thisYear = 1900 + today.getYear();
		var i;
		if (typeof notAvailableDays == "undefined") {
			notAvailableDays = [];
		}
		if (typeof priceArr == "undefined") {
			priceArr = [];
		}
		var html = "";
		var firstDay = new Date(Year, Month, 1);
		var startDay = firstDay.getDay();
	
		if (((Year % 4 == 0) && (Year % 100 != 0)) || (Year % 400 == 0)) {
			days[1] = 29;
		} else {
			days[1] = 28;
		}
	
		html += "<table><thead><tr>";
	
		for ( i= 0; i < 7; i++) {
			html += "<td>" + daynames[i] + "</td>";
		}
	
		html += "</tr></thead><tbody><tr>";
	
		var column = 0;
		var lastMonth = Month - 1;
		if (lastMonth == -1) { lastMonth = 11; }
		for (i = 0; i < startDay; i++) {
			//html += "<td class='date-passed prev-month'><span>" + (days[lastMonth] - startDay + i + 1) + "</span></td>";
			html += "<td class='prev-month'></td>";
			column++;
		}
	
		for (i = 1; i <= days[Month]; i++) {
			var className = [];
			if ((i == thisDay) && (Month == thisMonth) && (Year == thisYear)) {
				className.push("today");
			}
			
			var priceText = "";
			if (Year > thisYear || (Year == thisYear && Month > thisMonth) || (Year == thisYear && Month == thisMonth && i >= thisDay)) {
				if ($.inArray(i, notAvailableDays) !== -1) {
					className.push("unavailable");
				} else {
					className.push("available");
					if (typeof priceArr[i] != "undefined") {
						priceText += "<span class='price-text'>" + priceArr[i] + "</span>";
					}
				}
			} else {
				className.push("date-passed");
			}

			if (i < thisDay || $.inArray(i, notAvailableDays) !== -1) {
				html += "<td class='" + className.join(" ") + "'><span>" + i + "</span></td>";
			} else {
				html += "<td class='" + className.join(" ") + "'><a href='#' class='no-redirect'>" + i + priceText + "</a></td>";
			}
			
			column++;
			if (column == 7) {
				html += "</tr><tr>";
				column = 0;
			}
		}
		html += "</tr></tbody></table>";
		this.html = html;
	};
	
	Calendar.prototype.getHTML = function() {
		return this.html;
	};
}(jQuery));

/* jQuery CountTo plugin */
(function(a){a.fn.countTo=function(g){g=g||{};return a(this).each(function(){function e(a){a=b.formatter.call(h,a,b);f.html(a)}var b=a.extend({},a.fn.countTo.defaults,{from:a(this).data("from"),to:a(this).data("to"),speed:a(this).data("speed"),refreshInterval:a(this).data("refresh-interval"),decimals:a(this).data("decimals")},g),j=Math.ceil(b.speed/b.refreshInterval),l=(b.to-b.from)/j,h=this,f=a(this),k=0,c=b.from,d=f.data("countTo")||{};f.data("countTo",d);d.interval&&clearInterval(d.interval);d.interval=setInterval(function(){c+=l;k++;e(c);"function"==typeof b.onUpdate&&b.onUpdate.call(h,c);k>=j&&(f.removeData("countTo"),clearInterval(d.interval),c=b.to,"function"==typeof b.onComplete&&b.onComplete.call(h,c))},b.refreshInterval);e(c)})};a.fn.countTo.defaults={from:0,to:0,speed:1E3,refreshInterval:100,decimals:0,formatter:function(a,e){return a.toFixed(e.decimals)},onUpdate:null,onComplete:null}})(jQuery);

/* soaptheme popup plugin */
(function($) {
	var stp, SoapPopup = function(){};
	SoapPopup.prototype = {
		constructor: SoapPopup,
		init: function() {
			//
		},
		open: function(options, obj) {
			if (typeof options == "undefined") {
				options = {};
			}
			var wrapObj = options.wrapId ? "#" + options.wrapId : ".opacity-overlay";
			if ($(wrapObj).length < 1) {
				var idStr = options.wrapId ? " id='" + options.wrapId + "'" : "";
				$("<div class='opacity-overlay' " + idStr + "><div class='container'><div class='popup-wrapper'><i class='fa fa-spinner fa-spin spinner'></i><div class='col-xs-12 col-sm-9 popup-content'></div></div></div></div>").appendTo("body");
			}
			stp.wrap = $(wrapObj);
			stp.content = stp.wrap.find(".popup-content");
			stp.spinner = stp.wrap.find(".spinner");
			stp.contentContainer = stp.wrap.find(".popup-wrapper");
			
			if (stGlobals.isMobile) {
				stp.wrap.css({ 
					height: $(document).height(),
					position: 'absolute'
				});
				stp.contentContainer.css("top", $(window).scrollTop());
			}

			stp.updateSize();
			var sourceId = obj.attr("href");
			if (typeof sourceId == "undefined") {
				sourceId = obj.data("target");
			}

			if (options.type == "ajax") {
				stp.content.html('');
				stp.content.height('auto').css("visibility", "hidden");
				stp.wrap.fadeIn();
				stp.spinner.show();
				$("body").addClass("overlay-open");
				$.ajax({
					url: options.url,
					type: 'post',
					data: options.data,
					success: function(html) {
						stp.content.html("<a href=\"javascript:void(0);\" data-dismiss=\"modal\" style=\"text-align: right; display: block;\">X</a>" + html);
						if (options.callBack) {
							options.callBack(stp);
						}
						setTimeout(function() {
							stp.content.css("visibility", "visible");
							stp.spinner.hide();
						}, 100);
					}
				});
			} else if (options.type == "map") {
				stp.wrap.fadeIn();
				stp.spinner.show();
				var lngltd = options.lngltd.split(",");
				var contentWidth = stp.content.width();
				stp.content.gmap3({
					clear: {
						name: "marker",
						last: true
					}
				});
				var zoom = options.zoom ? parseInt(options.zoom, 10) : 12;
				stp.content.height(contentWidth * 0.5).gmap3({
					map: {
						options: {
							center: lngltd,
							zoom: zoom
						}
					},
					marker: {
						values: [
							{latLng: lngltd}

						],
						options: {
							draggable: false
						},
					}
				});
				$("body").addClass("overlay-open");
			} else {
				stp.content.children().hide();
				if (stp.content.children(sourceId).length > 0) {
					;// ignore
				} else {
					$(sourceId).appendTo(stp.content);
				}
				$(sourceId).show();
				stp.spinner.hide();
				stp.wrap.fadeIn(function() {
					$(sourceId).find(".input-text").eq(0).focus();
					$("body").addClass("overlay-open");
				});
			}
		},
		close: function() {
			$("body").removeClass("overlay-open");
			$("html").css("overflow", "");
			$("html").css("margin-right", "");
			stp.spinner.hide();
			stp.wrap.fadeOut();
		},
		updateSize: function() {
			if (stGlobals.isIOS) {
				var zoomLevel = document.documentElement.clientWidth / window.innerWidth;
				var height = window.innerHeight * zoomLevel;
				stp.contentContainer.css('height', height);
			} else if (stGlobals.isMobile) {
				stp.contentContainer.css('height', $(window).height());
			}
		},
		getScrollbarSize: function() {
			if (document.body.scrollHeight <= $(window).height()) {
				return 0;
			}
			if(stp.scrollbarSize === undefined) {
				var scrollDiv = document.createElement("div");
				scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
				document.body.appendChild(scrollDiv);
				stp.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
				document.body.removeChild(scrollDiv);
			}
			return stp.scrollbarSize;
		}
	}

	$.fn.soapPopup = function(options) {
		stp = new SoapPopup();
		stp.init();

		$(document).bind('keydown', function (e) {
			var key = e.keyCode;
			if ($(".opacity-overlay:visible").length > 0 && key === 27) {
				e.preventDefault();
				stp.close();
			}
		});
		
		$(document).on("click touchend", ".opacity-overlay", function(e) {
			if ( !$(e.target).is(".opacity-overlay .popup-content *")) {
				e.preventDefault();
				stp.close();
			}
		});

		$(window).resize(function() {
			stp.updateSize();
		});

		stp.open(options, $(this));

		$(document).on("click touchend", ".opacity-overlay [data-dismiss='modal']", function(e) {
            stp.close();
        });
		return $(this);
	};
})(jQuery);

/* jQuery set same height plugin */
tjq.fn.setSameHeight=function(){
	var max_height = 0;

	tjq(this).height('auto');
	tjq(this).each(function(index){
		if (tjq(this).outerHeight() > max_height) { max_height = tjq(this).outerHeight(); }
	});
	tjq(this).css('min-height', max_height);

	return this;
}

/* change UI of input, select, option, checkbox, datepicker */
function changeTraveloElementUI() {
	// change UI of select box
	tjq(".selector select").each(function() {
		var obj = tjq(this);
		if (obj.parent().children(".custom-select").length < 1) {
			obj.after("<span class='custom-select'>" + obj.children("option:selected").html() + "</span>");
			
			if (obj.hasClass("white-bg")) {
				obj.next("span.custom-select").addClass("white-bg");
			}
			if (obj.hasClass("full-width")) {
				//obj.removeClass("full-width");
				//obj.css("width", obj.parent().width() + "px");
				//obj.next("span.custom-select").css("width", obj.parent().width() + "px");
				obj.next("span.custom-select").addClass("full-width");
			}
		}
	});
	tjq("body").on("change", ".selector select", function() {
		if (tjq(this).next("span.custom-select").length > 0) {
			tjq(this).next("span.custom-select").text(tjq(this).children("option:selected").text());
		}
	});
	
	tjq("body").on("keydown", ".selector select", function() {
		if (tjq(this).next("span.custom-select").length > 0) {
			tjq(this).next("span.custom-select").text(tjq(this).children("option:selected").text());
		}
	});

	// change UI of file input
	tjq(".fileinput input[type=file]").each(function() {
		var obj = tjq(this);

		if (obj.parent().children(".custom-fileinput").length < 1) {
			obj.after('<input type="text" class="custom-fileinput" />');
			if (typeof obj.data("placeholder") != "undefined") {
				obj.next(".custom-fileinput").attr("placeholder", obj.data("placeholder"));
			}
			if (typeof obj.prop("class") != "undefined") {
				obj.next(".custom-fileinput").addClass(obj.prop("class"));
			}
			obj.parent().css("line-height", obj.outerHeight() + "px");
		}
	});

	tjq("body").on("change", ".fileinput input[type=file]", function() {
		var fileName = this.value,
			slashIndex = fileName.lastIndexOf("\\");

		if (slashIndex == -1) {
			slashIndex = fileName.lastIndexOf("/");
		}
		if (slashIndex != -1) {
			fileName = fileName.substring(slashIndex + 1);
		}

		tjq(this).next(".custom-fileinput").val(fileName);
	});

	// checkbox
	tjq(".checkbox input[type='checkbox'], .radio input[type='radio']").each(function() {
		if (tjq(this).is(":checked")) {
			tjq(this).closest(".checkbox").addClass("checked");
			tjq(this).closest(".radio").addClass("checked");
		}
	});
	tjq("body").on("change", ".checkbox input[type='checkbox']", function() {
		if (tjq(this).is(":checked")) {
			tjq(this).closest(".checkbox").addClass("checked");
		} else {
			tjq(this).closest(".checkbox").removeClass("checked");
		}
	});

	//radio
	tjq("body").on("change", ".radio input[type='radio']", function(event, ui) {
		if (tjq(this).is(":checked")) {
			var name = tjq(this).prop("name");

			if (typeof name != "undefined") {
				tjq(".radio input[name='" + name + "']").closest('.radio').removeClass("checked");
			}

			tjq(this).closest(".radio").addClass("checked");
		}
	});

	// timepicker
	tjq('.timepicker-wrap input').each(function() {
		tjq(this).timepicker({
			'timeFormat': 'h:i A',
		});
	});

	// datepicker
	tjq('.datepicker-wrap input').each(function() {
		var minDate = tjq(this).data("min-date"),
			maxDate = tjq(this).data("max-date");

		if (typeof minDate == "undefined") {
			minDate = 0;
		}
		if (typeof maxDate == "undefined") {
			maxDate = null;
		}
		if (tjq(this).closest('.datepicker-wrap').hasClass('to-today')) { minDate = null; maxDate = 0; }

		tjq(this).datepicker({
			showOn: 'button',
			buttonImage: themeurl + '/images/icon/blank.png',
			buttonText: '',
			buttonImageOnly: true,
			changeYear: false,
			/*showOtherMonths: true,*/
			minDate: minDate,
			maxDate: maxDate,
			dateFormat: date_format,
			// dayNamesMin: ["S", "M", "T", "W", "T", "F", "S"],
			//monthNames: [ "Jan","Feb","March","April","May","June", "July","August","September","October","November","December" ],
			beforeShow: function(input, inst) {
				var themeClass = tjq(input).parent().attr("class").replace("datepicker-wrap", "");
				tjq('#ui-datepicker-div').attr("class", "");
				tjq('#ui-datepicker-div').addClass("ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all");
				tjq('#ui-datepicker-div').addClass(themeClass);
			},
			onClose: function(selectedDate) {
				if ( tjq(this).attr('name') == 'date_from' ) {
					if ( tjq(this).closest('form').find('input[name="date_to"]').length > 0 ) {
						tjq(this).closest('form').find('input[name="date_to"]').datepicker("option", "minDate", selectedDate);

						if ( ! tjq(this).closest('form').find('input[name="date_to"]').datepicker('getDate') || tjq(this).datepicker("getDate") >= tjq(this).closest('form').find('input[name="date_to"]').datepicker('getDate') ) { 

							var t = selectedDate.split('/'),
								d = new Date( t[2], t[1]-1, t[0] );
							d.setDate(d.getDate() + 1);

							// tjq(this).closest('form').find('input[name="date_to"]').datepicker( 'setDate', d );
						}
					}
				}
				if ( tjq(this).attr('name') == 'date_to' ) {
					if ( tjq(this).closest('form').find('input[name="date_from"]').length > 0 ) {
						// $(this).closest('form').find('input[name="date_from"]').datepicker("option", "maxDate", selectedDate);
					}
				}
			}
		});
	});

	// placeholder for ie8, 9
	try {
		tjq('input, textarea').placeholder();
	} catch (e) {}
}

/* display photo gallery */
function displayPhotoGallery($item) {
	if (! tjq.fn.flexslider || $item.length < 1 || $item.is(":hidden")) {
		return;
	}

	var dataAnimation = $item.data("animation"),
		dataSync = $item.data("sync"),
		dataFixPos = $item.data("fix-control-nav-pos");

	if (typeof dataAnimation == "undefined") {
		dataAnimation = "slide";
	}
	
	$item.flexslider({
		animation: dataAnimation,
		controlNav: true,
		animationLoop: true,
		slideshow: true,
		pauseOnHover: true,
		sync: dataSync,
		start: function(slider) {
			if (typeof dataFixPos != "undefined" && dataFixPos == "1") {
				var height = tjq(slider).find(".slides img").height();
				tjq(slider).find(".flex-control-nav").css("top", (height - 44) + "px");
			}
		},
	});
}

/* display image carousel */
function displayImageCarousel($item) {
	if (! tjq.fn.flexslider || $item.length < 1 || $item.is(":hidden")) {
		return;
	}

	var dataAnimation = $item.data("animation"),
		dataItemWidth = $item.data("item-width"),
		dataItemMargin = $item.data("item-margin"),
		dataSync = $item.data("sync"),
		dataAnimationLoop = true,
		dataSlideshow = false;

	if (typeof dataAnimation == "undefined") {
		dataAnimation = "slide";
	}
	if (typeof dataItemWidth == "undefined") {
		dataItemWidth = 70;
	}
	if (typeof dataItemMargin == "undefined") {
		dataItemMargin = 10;
	}
	dataItemWidth = parseInt(dataItemWidth, 10);
	dataItemMargin = parseInt(dataItemMargin, 10);

	if (typeof dataSync == "undefined") {
		dataSync = "";
		//dataAnimationLoop = true;
		dataSlideshow = true;
	}

	$item.flexslider({
		animation: dataAnimation,
		controlNav: true,
		animationLoop: dataAnimationLoop,
		slideshow: dataSlideshow,
		itemWidth: dataItemWidth,
		itemMargin: dataItemMargin,
		minItems: 1,
		pauseOnHover: true,
		asNavFor: dataSync,
		start: function(slider) {
			if (dataSync == "") {
				tjq(slider).find(".middle-block img, .middle-block .middle-item").each(function() {
					if(tjq(this).width() < 1) {
						tjq(this).load(function() {
							tjq(this).closest(".middle-block").middleblock();
						});
					} else {
						tjq(this).closest(".middle-block").middleblock();
					}
				});
			}
			if (tjq('.listing-style1').length > 0) {
				tjq('.listing-style1').each(function(index){
					tjq(this).find('article.box .details .description').setSameHeight();
					tjq(this).find('article.box .details .box-title').setSameHeight();
					//tjq(this).find('article.box').setSameHeight();
				});
			}
		},
		after: function(slider) {
			if (slider.currentItem == 0) {
				var target = 0;
				if (slider.transitions) {
					target = (slider.vars.direction === "vertical") ? "translate3d(0," + target + ",0)" : "translate3d(" + target + ",0,0)";
					slider.container.css("-" + slider.pfx + "-transition-duration", "0s");
					slider.container.css("transition-duration", "0s");
				}
				slider.args[slider.prop] = target;
				slider.container.css(slider.args);
				slider.container.css('transform',target);
			}
		}

	});
}

/* third level menu position to left */
function fixPositionSubmenu() {
	tjq("#main-menu .menu li.menu-item-has-children > ul, .ribbon ul.menu.mini, .chaser .container li.menu-item-has-children > ul").each(function(e) {
		if (tjq(this).closest(".megamenu").length > 0) {
			return;
		}

		var leftPos = tjq(this).parent().offset().left + tjq(this).parent().width();

		if (leftPos + tjq(this).width() > tjq("body").width()) {
			tjq(this).addClass("left");
		} else {
			tjq(this).removeClass("left");
		}
	});
}

/* mega menu */
//var megamenu_items_per_column = 6;
function fixPositionMegaMenu(parentObj) {
	if (typeof parentObj == "undefined") {
		parentObj = "";
	} else {
		parentObj += " ";
	}

	tjq(parentObj + ".megamenu-menu").each(function() {
		var paddingLeftStr = tjq(this).closest(".container").css("padding-left"),
			paddingLeft = parseInt(paddingLeftStr, 10),
			offsetX = tjq(this).offset().left - tjq(this).closest(".container").offset().left - paddingLeft,
			columns_arr = new Array(),
			sum_columns = 0;

		if (offsetX == 0) { return; }

		tjq(this).children(".megamenu-wrapper").css("left", "-" + offsetX + "px");
		tjq(this).children(".megamenu-wrapper").css("width", tjq(this).closest(".container").width() + "px");
		/*if (typeof tjq(this).children(".megamenu-wrapper").data("items-per-column") != "undefined") {
			megamenu_items_per_column = parseInt(tjq(this).children(".megamenu-wrapper").data("items-per-column"), 10);
		}*/
		//tjq(this).children(".megamenu-wrapper").show();

		tjq(this).find(".megamenu > li").each(function() {
			//var each_columns = Math.ceil(tjq(this).find("li > a").length / megamenu_items_per_column);
			var each_columns = tjq(this).children('ul').children('li.menu-item-has-children').length;

			if (each_columns == 0) {
				each_columns = 1;
			}

			columns_arr.push(each_columns);
			sum_columns += each_columns;
		});

		tjq(this).find(".megamenu > li").each(function(index) {
			tjq(this).css("width", (columns_arr[index] / sum_columns * 100) + "%");
			tjq(this).addClass("megamenu-columns-" + columns_arr[index]);
		});

		tjq(this).find(".megamenu > li.menu-item-has-children").each(function(index) {
			if (tjq(this).children(".sub-menu").length < 1) {
				tjq(this).append("<ul class='sub-menu'></ul>");

				for (var j = 0; j < columns_arr[index]; j++) {
					tjq(this).children(".sub-menu").append("<li><ul></ul></li>")
				}

				var lastIndex = tjq(this).children("ul").eq(0).children("li").length - 1;

				tjq(this).children("ul").eq(0).children("li").each(function(i) {
					var parentIndex = Math.floor(i / megamenu_items_per_column);

					tjq(this).closest("li.menu-item-has-children").children(".sub-menu").children("li").eq(parentIndex).children("ul").append(tjq(this).clone());

					if (i == lastIndex) {
						tjq(this).closest(".menu-item-has-children").children("ul").eq(0).remove();
					}
				});
			}
		});

		tjq(this).children(".megamenu-wrapper").show();
	});
}

/* testimonial height fix */
function fixTestimonialHeight(slider) {
	tjq(slider).find(".slides > li").css("height", "auto");

	setTimeout(function() {
		tjq(slider).each(function(){
			var maxHeight = 0;

			tjq(this).each(function() {
				if (tjq(this).height() > maxHeight) {
					maxHeight = tjq(this).height();
				}
			});

			tjq(this).find(" > li").height(maxHeight);
		});
	}, 50);
}

/* render testimonial*/
function displayTestimonials() {
	try {
		if (tjq('.testimonial.style1').length > 0 && tjq('.testimonial.style1').is(":visible")) {
			tjq('.testimonial.style1').flexslider({
				namespace: "testimonial-",
				animation: "slide",
				controlNav: true,
				animationLoop: true,
				directionNav: false,
				slideshow: true,
				start: fixTestimonialHeight
			});
		}
	} catch (e) {}

	try {
		if (tjq('.testimonial.style2').length > 0 && tjq('.testimonial.style2').is(":visible")) {
			tjq('.testimonial.style2').flexslider({
				namespace: "testimonial-",
				animation: "slide",
				controlNav: false,
				animationLoop: true,
				directionNav: true,
				slideshow: true,
				start: fixTestimonialHeight
			});
		}
	} catch (e) {}

	try {
		if (tjq('.testimonial.style3').length > 0 && tjq('.testimonial.style3').is(":visible")) {
			tjq('.testimonial.style3').flexslider({
				namespace: "testimonial-",
				controlNav: false,
				animationLoop: true,
				directionNav: true,
				slideshow: true,
				start: fixTestimonialHeight
			});
		}
	} catch (e) {}
}

/*promo box */
function fixPromoBoxHeight() {
	tjq(".promo-box").each(function() {
		if (tjq(this).find(".content-section").css("float") == "right" || tjq(this).find(".image-container").css("float") == "right") {
			var maxHeight = tjq(this).find(".image-container > img").height(),
				calcPaddingTop = tjq(".content-section").css("padding-top"),
				calcPaddingBottom = tjq(".content-section").css("padding-bottom"),
				calcPadding = 0;

			var contentHeight = tjq(this).find(".content-section >.table-wrapper").length > 0 ? tjq(this).find(".content-section > .table-wrapper").height() + calcPadding : tjq(this).find(".content-section").innerHeight();

			tjq(this).find(".content-section .table-wrapper").css("height", "auto");

			try {
				calcPadding = parseInt(calcPaddingTop, 10) + parseInt(calcPaddingBottom, 10);
			} catch (e) {  }

			if (maxHeight < contentHeight) {
				maxHeight = contentHeight;
			} else {
				maxHeight += 15;
			}

			tjq(this).find(".image-container").height(maxHeight);
			tjq(this).find(".content-section").innerHeight(maxHeight);
			tjq(this).find(".content-section .table-wrapper").css("height", "100%");
			tjq(this).find(".image-container").css("position", "relative");
			tjq(this).find(".image-container > img").css("position", "absolute");
			tjq(this).find(".image-container > img").css("bottom", "0");
			if (tjq(this).find(".image-container").css("float") == "right") {
				tjq(this).find(".image-container > img").css("right", "0");
				tjq(this).find(".image-container").css("margin-right", "-5%");
			} else {
				tjq(this).find(".image-container > img").css("left", "0");
				tjq(this).find(".image-container").css("margin-left", "-5%");
			}
			tjq(this).find(".image-container > img").css("left", "0");
		} else {
			tjq(this).find(".image-container").css("height", "auto");
			tjq(this).find(".image-container").css("margin", "0");
			tjq(this).find(".content-section").css("height", "auto");
			tjq(this).find(".image-container > img").css("position", "static");
		}

		if (! tjq(this).find(".image-container > img").hasClass("animated")) {
			tjq(this).find(".image-container > img").css("visibility", "visible");
		}
	});
}

/* init function */
function trav_init() {
	//
}

function trav_init_chasermenu() {
	if (enableChaser == 1 && tjq('#content').length > 0 && tjq('#main-menu ul.menu').length > 0) {
		var forchBottom;
		var chaser = tjq('#main-menu ul.menu').clone().hide().appendTo(document.body).wrap("<div class='chaser hidden-mobile'><div class='container'></div></div>");
		var forch = tjq('#content').first();

		tjq('header .container div.logo').clone().insertBefore('.chaser .menu');

		forchBottom = forch.offset().top + 2;

		tjq(window).on('scroll', function () {
			var top = tjq(document).scrollTop();
			if (tjq(".chaser").is(":hidden") && top > forchBottom) {
				tjq(".chaser").slideDown(300);
				//chaser.fadeIn(300, shown);
			} else if (tjq(".chaser").is(":visible") && top < forchBottom) {
				tjq(".chaser").slideUp(200);
				//chaser.fadeOut(200, hidden);
			}
		});

		tjq(window).on('resize', function () {
			var top = tjq(document).scrollTop();
			if (tjq(".chaser").is(":hidden") && top > forchBottom) {
				tjq(".chaser").slideDown(300);
			} else if (tjq(".chaser").is(":visible") && top < forchBottom) {
				tjq(".chaser").slideUp(200);
			}
		});

		tjq(".chaser").css("visibility", "hidden");
		chaser.show();

		fixPositionMegaMenu(".chaser");

		tjq(".chaser .megamenu-menu").removeClass("light");
		//chaser.hide();
		tjq(".chaser").hide();
		tjq(".chaser").css("visibility", "visible");
	}

}

var loaded = 0;
function trav_ready() {
	changeTraveloElementUI();

	if ( stGlobals.isMobile ) {
		tjq("body").addClass("is-mobile");
	}

	// parallax for wekbit mobile
	if (stGlobals.isMobileWebkit) {
		tjq(".parallax").css("background-attachment", "scroll");
	}

	// testimonials
	displayTestimonials();
	fixPositionMegaMenu();
	fixPositionSubmenu();

	// accordion & toggles
	tjq(".toggle-container .panel-collapse").each(function() {
		if (! tjq(this).hasClass("in")) {
			tjq(this).closest(".panel").find("[data-toggle=collapse]").addClass("collapsed");
		}
	});

	// tooltip
	tjq("[data-toggle=tooltip]").tooltip();

	// activate tab
	var traveloLocationHash = window.location.hash;
	if (traveloLocationHash != "") {
		traveloLocationHash = escape(traveloLocationHash.replace("#", ""));
		if (tjq('a[href="#' + traveloLocationHash + '"]').length > 0) {
			setTimeout(function() {
				tjq('a[href="#' + traveloLocationHash + '"]').tab('show');
			}, 100);
		}
	}

	// chaser
	trav_init_chasermenu();

	// footer menu position to top
	tjq("#footer #main-menu .menu >  li.menu-item-has-children").each(function(e) {
		var height = tjq(this).children("ul, .megamenu-wrapper").height();
		tjq(this).children("ul, .megamenu-wrapper").css("top", "-" + height + "px");
	});

	// filters on search result page
	if (tjq(".filters-container").length > 0) {

		// price range
		if ( tjq("#price-range").length ) {
			var price_slide_min_val = 0;
			var price_slide_step = tjq("#price-range").data('slide-step');
			var price_slide_last_val = tjq("#price-range").data('slide-last-val');
			var price_slide_max_val = price_slide_last_val + price_slide_step;

			var def_currency = tjq("#price-range").data('def-currency');
			var min_price = tjq("#price-range").data('min-price');
			var max_price = tjq("#price-range").data('max-price');

			if (max_price == "no_max") { max_price = price_slide_max_val; }

			var url_noprice = tjq("#price-range").data('url-noprice').replace(/&amp;/g, '&');

			if ((min_price != 0) || (max_price != price_slide_max_val)) {
				tjq('#price-filter').collapse('show');
				tjq('a[href="#price-filter"]').removeClass('collapsed');
			}

			tjq("#price-range").slider({
				range: true,
				min: price_slide_min_val,
				max: price_slide_max_val,
				step: price_slide_step,
				values: [ min_price, max_price ],
				slide: function(event, ui) {
					// make handles uncollapse
					if ((ui.values[0] + 1) >= ui.values[1]) {
						return false;
					}

					// min price text
					tjq(".min-price-label").text(def_currency + ui.values[ 0 ]);

					// max price text
					max_price = ui.values[1];
					if (max_price == price_slide_max_val) {
						max_price = price_slide_last_val + '+';
					}
					tjq(".max-price-label").text(def_currency + max_price);
				},
				change: function(event, ui) {
					if (ui.values[0] != 0) {
						url_noprice += '&min_price=' + ui.values[0];
					}
					if (ui.values[1] != price_slide_max_val) {
						 url_noprice += '&max_price=' + ui.values[1];
					}
					if (url_noprice.indexOf("?") < 0) { url_noprice = url_noprice.replace(/&/, '?'); }
					window.location.href = url_noprice;
				}
			});

			tjq(".min-price-label").text(def_currency + tjq("#price-range").slider("values", 0));

			if (tjq("#price-range").slider("values", 1) == price_slide_max_val) {
				tjq(".max-price-label").text(def_currency + price_slide_last_val + "+");
			} else {
				tjq(".max-price-label").text(def_currency + tjq("#price-range").slider("values", 1));
			}
		}

		// rating
		if ( tjq("#rating").length ) {
			var url_norating = tjq("#rating").data('url-norating').replace(/&amp;/g, '&');
			var rating = tjq("#rating").data('rating');
			/*if (rating > 0) {
				tjq('#rating-filter').collapse('show');
				tjq('a[href="#rating-filter"]').removeClass('collapsed');
			}*/

			tjq("#rating").slider({
				range: "min",
				value: rating,
				min: 0,
				max: 5,
				step: 0.5,
				slide: function(event, ui) {
					var label_rating = '';
					if (ui.value == 0) {
						label_rating = tjq("#rating").data('label-norating');
					} else if (ui.value == 5) {
						label_rating = tjq("#rating").data('label-fullrating');
					} else {
						label_rating = ui.value + ' ' + tjq("#rating").data('label-rating');
					}
					tjq("#rating-filter .panel-content > span").text(label_rating);
				},
				change: function(event, ui) {
					if (ui.value != 0) {
						url_norating += '&rating=' + ui.value;
					}
					if (url_norating.indexOf("?") < 0) { url_norating = url_norating.replace(/&/, '?'); }
					window.location.href = url_norating;
				}
			});

			var label_rating = '';
			if (rating == 0) {
				label_rating = tjq("#rating").data('label-norating');
			} else if (rating == 5) {
				label_rating = tjq("#rating").data('label-fullrating');
			} else {
				label_rating = rating + ' ' + tjq("#rating").data('label-rating');
			}

			tjq("#rating-filter .panel-content > span").text(label_rating);
		}

	}

	// fit video
	if (tjq('.full-video').length > 0 && tjq.fn.fitVids) {
		tjq('.full-video').fitVids();
	}

	tjq(".toggle-container.with-image").each(function() {
		var type = "";
		var duration = "1s";

		if (typeof tjq(this).data("image-animation-type") != "undefined") {
			type = tjq(this).data("image-animation-type");
		}
		if (typeof tjq(this).data("image-animation-duration") != "undefined") {
			duration = tjq(this).data("image-animation-duration");
		}

		var imageHtml = '<div class="image-container';

		if (type != "") {
			imageHtml += ' animated" data-animation-type="' + type + '" data-animation-duration="' + duration;
		}
		imageHtml += '"><img src="" alt="toggle-image" /></div>';
		tjq(this).prepend(imageHtml);

		if (tjq(this).find(".panel-collapse.in").length > 0) {
			var activeImg = tjq(this).find(".panel-collapse.in").parent().children("img");
			var src = activeImg.attr("src");
			var width = activeImg.attr("width");
			var height = activeImg.attr("height");
			var alt = activeImg.attr("alt");
			
			var imgObj = tjq(this).find(".image-container img");
			imgObj.attr("src", src);
			if (typeof width != "undefined") {
				imgObj.attr("width", width);
			}
			if (typeof height != "undefined") {
				imgObj.attr("height", height);
			}
			if (typeof alt != "undefined") {
				imgObj.attr("alt", alt);
			}
			tjq(this).children(".image-container").show();
		}
	});

	tjq('.toggle-container.with-image').on('show.bs.collapse', function (e) {
		var activeImg = tjq(e.target).parent().children("img");

		if (activeImg.length > 0) {
			var src = activeImg.attr("src");
			var width = activeImg.attr("width");
			var height = activeImg.attr("height");
			var alt = activeImg.attr("alt");
			var imgObj = tjq(this).find(".image-container img");

			imgObj.attr("src", src);

			if (typeof width != "undefined") {
				imgObj.attr("width", width);
			}
			if (typeof height != "undefined") {
				imgObj.attr("height", height);
			}
			if (typeof alt != "undefined") {
				imgObj.attr("alt", alt);
			}
			
			imgObj.parent().css("visibility", "hidden");
			imgObj.parent().removeClass(imgObj.parent().data("animation-type"));

			setTimeout(function() {
				imgObj.parent().addClass(imgObj.parent().data("animation-type"));
				imgObj.parent().css("visibility", "visible");
			}, 10);
		}
	});

	tjq('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		if (loaded == 1) {
			var contentId = tjq(e.target).attr("href");

			if (tjq(contentId).find(".image-carousel").length > 0) {
				displayImageCarousel(tjq(contentId).find(".image-carousel"));
			}
			if (tjq(contentId).find(".photo-gallery").length > 0) {
				displayPhotoGallery(tjq(contentId).find(".photo-gallery"));
			}
			if (tjq(contentId).find(".testimonial").length > 0) {
				displayTestimonials();
			}

			tjq(window).trigger('resize');
		}
	});

	//waypoint
	if(tjq().waypoint) {
		// animation effect
		tjq('.animated').waypoint(function() {
			var type = tjq(this).data("animation-type");
			if (typeof type == "undefined" || type == false) {
				type = "fadeIn";
			}
			tjq(this).addClass(type);
			
			var duration = tjq(this).data("animation-duration");
			if (typeof duration == "undefined" || duration == false) {
				duration = "1";
			}
			tjq(this).css("animation-duration", duration + "s");
			
			var delay = tjq(this).data("animation-delay");
			if (typeof delay != "undefined" && delay != false) {
				tjq(this).css("animation-delay", delay + "s");
			}
			
			tjq(this).css("visibility", "visible");

			setTimeout(function() { tjq.waypoints('refresh'); }, 1000);
		}, {
			triggerOnce: true,
			offset: 'bottom-in-view'
		});

		// display counter
		tjq('.counters-box').waypoint(function() {
			tjq(this).find('.display-counter').each(function() {
				var value = tjq(this).data('value');
				tjq(this).countTo({from: 0, to: value, speed: 3000, refreshInterval: 10});
			});
			setTimeout(function() { tjq.waypoints('refresh'); }, 1000);
		}, {
			triggerOnce: true,
			offset: '100%'
		});
	}

	// Mobile search
	if (tjq('#mobile-search-tabs').length > 0) {
		var mobile_search_tabs_slider = tjq('#mobile-search-tabs').bxSlider({
			mode: 'fade',
			infiniteLoop: false,
			hideControlOnEnd: true,
			touchEnabled: true,
			pager: false,
			onSlideAfter: function($slideElement, oldIndex, newIndex) {
				tjq('a[href="' + tjq($slideElement).children("a").attr("href") + '"]').tab('show');
			}
		});
	}

	// Mobile menu
	tjq(".mobile-menu ul.menu > li.menu-item-has-children").each(function(index) {
		var menuItemId = "mobile-menu-submenu-item-" + index;
		tjq('<button class="dropdown-toggle collapsed" data-toggle="collapse" data-target="#' + menuItemId + '"></button>').insertAfter(tjq(this).children("a"));
		/*tjq(this).children(".dropdown-toggle").click(function(e) {
			if (tjq(this).hasClass("collapsed")) {
				tjq(this).parent().addClass("open");
			} else {
				tjq(this).parent().removeClass("open");
			}
		});*/
		tjq(this).children("ul").prop("id", menuItemId);
		tjq(this).children("ul").addClass("collapse");

		tjq("#" + menuItemId).on("show.bs.collapse", function() {
			tjq(this).parent().addClass("open");
		});
		tjq("#" + menuItemId).on("hidden.bs.collapse", function() {
			tjq(this).parent().removeClass("open");
		});
	});
}

function trav_show_modal(success, title, content) {
	var modal;

	if (success == 1) {
		if (tjq('#travelo-success').length > 0) {
			modal = tjq('#travelo-success');
		} else {
			modal = tjq('<div id="travelo-success" class="travelo-modal-box travelo-box"><div class="travelo-modal-head"><p class="travelo-modal-icon"><i class="soap-icon-check circle"></i></p><h4 class="travelo-modal-title"></h4></div><div class="travelo-modal-content"></div></div>').appendTo('footer');
		}
	} else {
		if (tjq('#travelo-failure').length > 0) {
			modal = tjq('#travelo-failure');
		} else {
			modal = tjq('<div id="travelo-failure" class="travelo-modal-box travelo-box"><div class="travelo-modal-head"><p class="travelo-modal-icon"><i class="soap-icon-notice circle"></i></p><h4 class="travelo-modal-title"></h4></div><div class="travelo-modal-content"></div></div>').appendTo('footer');
		}
	}
	modal.find('.travelo-modal-title').html(title);
	modal.find('.travelo-modal-content').html(content);
	if (tjq("#soap-popupbox").length < 1) {
		tjq("<div class='opacity-overlay' id='soap-popupbox' tabindex='-1'><div class='container'><div class='popup-wrapper'><div class='popup-content'></div></div></div></div>").appendTo("body");
	}
	tjq("#soap-popupbox .popup-content").children().hide();
	modal.appendTo(tjq("#soap-popupbox .popup-content"));
	modal.show();
	tjq("#soap-popupbox").fadeIn(function() {
		modal.find(".input-text").eq(0).focus();
	});
}

/*slideshow bg*/
function resizeSlideshowBGHeight() {
	if (tjq(".slideshow-bg.full-screen").length == 1) {
		var offsetTop = tjq(".slideshow-bg.full-screen").offset().top;
		tjq(".slideshow-bg.full-screen").height(tjq(window).height() - offsetTop);
	}
}

///////////////////////////////////////////////////////////////////////////////
////////////////////////////////// MAIN ACTIONS ///////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/* disable click before loading page */
tjq("body").on("click", "a.popup-gallery, a.no-redirect", function(e) {
	e.preventDefault();
	return false;
});

// back to top
tjq("body").on("click", "#back-to-top", function(e) {
	e.preventDefault();
	tjq("html,body").animate({scrollTop: 0}, 1000);
});

// login box
tjq("body").on("click", ".soap-popupbox", function(e) {
	e.preventDefault();

	var sourceId = tjq(this).attr("href");

	if (typeof sourceId == "undefined") {
		sourceId = tjq(this).data("target");
	}
	if (typeof sourceId == "undefined") {
		return;
	}
	if (tjq(sourceId).length < 1) {
		return;
	}

	tjq(this).soapPopup({
		wrapId: "soap-popupbox",
	});
});

// go back
tjq(".go-back").click(function(e) {
	e.preventDefault();
	window.history.go(-1);
});

// alert, info box
tjq("body").on("click", ".alert > .close, .info-box > .close", function() {
	tjq(this).parent().fadeOut(300);
});

// redirect to the location
tjq(".location-reload").click(function(e) {
	e.preventDefault();

	var url = tjq(this).prop("href").split("#")[0];

	if (window.location.href.indexOf(url) != -1) {
		var hash = tjq(this).prop("href").split("#")[1];
		if (typeof hash != "undefined" && hash != "" && tjq("a[href='#" + hash + "']").length > 0) {
			tjq("a[href='#" + hash + "']").tab('show');
		}
	} else {
		window.location.href = tjq(this).prop("href");
	}
});

// sort of trip
tjq(".sort-trip a").click(function(e) {
	e.preventDefault();

	tjq(this).parent().parent().children().removeClass("active");
	tjq(this).parent().addClass("active");
});

// filters option
tjq(".filters-container .filters-option a").click(function(e) {
	e.preventDefault();

	if (tjq(this).parent().hasClass("active")) {
		tjq(this).parent().removeClass("active");
	} else {
		tjq(this).parent().addClass("active");
	}
});

// load more button click action on blog page
tjq("body").on("click", ".btn-load-more-posts", function(e){
	e.preventDefault();

	var url = tjq(this).attr('href');

	jQuery.ajax({
		url: url,
		type: "GET",
		success: function(response){
			var success =  tjq(tjq.parseHTML(response)).find(".blog-infinite");
			var load_more_btn = tjq(tjq.parseHTML(response)).find('.btn-load-more-posts');

			tjq(".blog-infinite").append(success.html());

			if (load_more_btn.length) {
				tjq('.btn-load-more-posts').attr('href', load_more_btn.attr('href'));
			} else {
				tjq('.btn-load-more-posts').remove();
			}

			tjq('.image-carousel').each(function() {
				displayImageCarousel(tjq(this));
			});

			tjq('.photo-gallery').each(function() {
				displayPhotoGallery(tjq(this));
			});

			if (tjq('.full-video').length > 0 && tjq.fn.fitVids) {
				tjq('.full-video').fitVids();
			}
		}
	});
	return false;
});

// handle kid age filter
tjq('select[name=kids]').change(function(){
	var prev_kids = tjq('.age-of-children .child-age-field').length;
	var i;

	tjq('.age-of-children').removeClass('no-display');

	if (prev_kids > tjq(this).val()) {

		var current_kids = tjq(this).val();

		if (current_kids == 0) {
			current_kids = 1;
			tjq('.age-of-children').addClass('no-display');
		}

		for (i = prev_kids; i > current_kids; --i) {
			tjq('.age-of-children .child-age-field').eq(i-1).remove();
		}
	} else {
		for (i = prev_kids + 1; i <= tjq(this).val(); i++) {
			var clone_age_last = tjq('.age-of-children .child-age-field:last').clone();
			var clone_age = clone_age_last.clone();
			tjq('.age-of-children .row').append(clone_age);
			var name = clone_age.find('label').text().replace(/(\d+)/, function(match, p1)
			{
				return (parseInt(p1) + 1);
			});
			clone_age.find('label').text(name);
			clone_age.find('select').val(0);
			clone_age.find('.custom-select').text(0);
		}
	}
});

// mobile top nav(language and currency)
tjq("body").on("click", function(e) {
	var target = tjq(e.target);

	if (! target.is(".mobile-topnav .ribbon.opened *")) {
		tjq(".mobile-topnav .ribbon.opened > .menu").toggle();
		tjq(".mobile-topnav .ribbon.opened").removeClass("opened");
	}
});
tjq(".mobile-topnav .ribbon > a").on("click", function(e) {
	e.preventDefault();

	if (tjq(".mobile-topnav .ribbon.opened").length > 0 && ! tjq(this).parent().hasClass("opened")) {
		tjq(".mobile-topnav .ribbon.opened > .menu").toggle();
		tjq(".mobile-topnav .ribbon.opened").removeClass("opened");
	}

	tjq(this).parent().toggleClass("opened");
	tjq(this).parent().children(".menu").toggle(200);

	if (tjq(this).parent().hasClass("opened") && tjq(this).parent().children(".menu").offset().left + tjq(this).parent().children(".menu").width() > tjq("body").width()) {
		var offsetX = tjq(this).parent().children(".menu").offset().left + tjq(this).parent().children(".menu").width() - tjq("body").width();
		offsetX = tjq(this).parent().children(".menu").position().left - offsetX - 1;
		tjq(this).parent().children(".menu").css("left", offsetX + "px");
	} else {
		tjq(this).parent().children(".menu").css("left", "0");
	}
});
tjq(".sort-by-container").click(function(){
	if ( tjq(this).hasClass('sorted') ) {
		return false;
	}

	tjq(this).addClass('sorted');
});

tjq(document).ready(function() {
	trav_ready();

	// popup
	tjq(document).bind('keydown', function (e) {
		var key = e.keyCode;

		if (tjq(".opacity-overlay:visible").length > 0 && key === 27) {
			e.preventDefault();
			tjq(".opacity-overlay").fadeOut();
		}
	});

	tjq(document).on("click touchend", ".opacity-overlay", function(e) {
		if (! tjq(e.target).is(".opacity-overlay .popup-content *")) {
			tjq(".opacity-overlay").fadeOut();
		}
	});

	// popup gallery
	tjq("body").on("click", "a.popup-gallery", function(e) {
		var post_id = tjq(this).data('post_id');

		e.preventDefault();

		tjq(this).soapPopup({
			type: "ajax",
			wrapId: "soap-gallery-popup",
			url: ajaxurl,
			data: { 'action': 'get_post_gallery', 'post_id': post_id },
			callBack: function(stp) {
				if (stp.wrap.find('.image-carousel').length > 0) {
					displayImageCarousel(stp.wrap.find('.image-carousel'));
				}
				if (stp.wrap.find('.photo-gallery').length > 0) {
					displayPhotoGallery(stp.wrap.find('.photo-gallery'));
				}
			}
		});
	});

	// popup map
	tjq("body").on("click", ".popup-map", function(e) {
		var lngltd = tjq(this).data("box");
		if (typeof lngltd != "undefined") {
			e.preventDefault();
			tjq(this).soapPopup({
				type: "map",
				zoom: 12,
				wrapId: "soap-map-popup",
				lngltd: lngltd
			});
		}
	});

	//ajax loading overlay
	tjq(document).ajaxStart(function(){
		tjq('.opacity-ajax-overlay').show();
	}).ajaxStop(function(){
		tjq('.opacity-ajax-overlay').hide();
	});
});

tjq(window).load(function() {

	tjq('.slideshow-bg').each(function() {
		tjq(this).flexslider({
			animation: "fade",
			controlNav: false,
			animationLoop: true,
			directionNav: false,
			slideshow: true,
			slideshowSpeed: 5000
		});
	});

	resizeSlideshowBGHeight();
	/* photo gallery and slideshow */
	// photo gallery with thumbnail
	tjq('.image-carousel').each(function() {
		displayImageCarousel(tjq(this));
	});

	tjq('.photo-gallery').each(function() {
		displayPhotoGallery(tjq(this));
	});

	// parallax
	if( ( ! stGlobals.isMobileWebkit && tjq(".parallax").length > 0 ) || tjq(".style-changer").length > 0 ) {
		tjq.stellar({
			responsive: true,
			horizontalScrolling: false
		});
	}
	//tjq(window).trigger('resize');
	setTimeout(function() {
		tjq(window).trigger('resize');
	}, 500);

	loaded = 1;
});

tjq(window).resize(function() {
	tjq(".middle-block").middleblock();
	fixPositionMegaMenu();
	fixPositionSubmenu();
	fixPromoBoxHeight();
	fixTestimonialHeight('.testimonial');
	resizeSlideshowBGHeight();

	// fix slider position of gallery slideshow style2
	if (tjq(".photo-gallery.style2").length > 0) {
		tjq(".photo-gallery.style2").each(function() {
			var dataFixPos = tjq(this).data("fix-control-nav-pos");
			if (typeof dataFixPos != "undefined" && dataFixPos == "1") {
				var height = tjq(this).find(".slides img").height();
				tjq(this).find(".flex-control-nav").css("top", (height - 44) + "px");
			}
		});
	}

	// make listing same height
	if (tjq('.listing-style1').length > 0) {
		tjq('.listing-style1').each(function(index){
			tjq(this).find('article.box .details .description').setSameHeight();
			tjq(this).find('article.box .details .box-title').setSameHeight();
			//tjq(this).find('article.box').setSameHeight();
		});
	}
	if (tjq('div.listing-style2').length > 0) {
		tjq('div.listing-style2').each(function(index){
			tjq(this).find('article.box').setSameHeight();
		});
	}
});

tjq(".login_link").click(function(){
	document.location.href = tjq(this).data('target');
});
trav_init();

///////////////////////////////////////////////////////////////////////////////
////////////////////////////////// OTHER ACTIONS //////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/* CONTACT FORM 7 CODE */
tjq(".wpcf7-submit").click(function(event) {
	tjq(document).ajaxComplete(function() {
		setTimeout(function(){
			var title = tjq(".wpcf7-response-output").html();
			var message = '';
			var success = '';

			if (tjq(".wpcf7-response-output").hasClass('wpcf7-validation-errors')) {
				success = 0;
			} else if (tjq(".wpcf7-response-output").hasClass('wpcf7-spam-blocked')) {
				success = 0;
			} else if (tjq(".wpcf7-response-output").hasClass('wpcf7-mail-sent-ng')) {
				success = 0;
			} else {
				success = 1;
			}

			trav_show_modal(success, title, message);
		},50);
	});
});

/* Accommodation Search Page */
// accommodation type filter
tjq("#accomodation-type-filter .filters-option li").click(function(){
	var url_noacc_type = tjq("#accomodation-type-filter").data('url-noacc_type').replace(/&amp;/g, '&');

	if (tjq(this).hasClass('all-types')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');

			return false;
		} else {
			tjq("#accomodation-type-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#accomodation-type-filter .filters-option li.active").length == 0) {
			tjq("#accomodation-type-filter .filters-option li.all-types").addClass('active');
		} else {
			tjq("#accomodation-type-filter .filters-option li.all-types").removeClass('active');
			tjq("#accomodation-type-filter .filters-option li.active").each(function(index){
				url_noacc_type += '&acc_type[]=' + tjq(this).data('term-id');
			});
		}
	}

	if (url_noacc_type.indexOf("?") < 0) { url_noacc_type = url_noacc_type.replace(/&/, '?'); }

	window.location.href = url_noacc_type;
});

// amenity filter
tjq("#amenities-filter .filters-option li").click(function(){
	var url_noamenities = tjq("#amenities-filter").data('url-noamenities').replace(/&amp;/g, '&');

	tjq("#amenities-filter .filters-option li.active").each(function(index){
		url_noamenities += '&amenities[]=' + tjq(this).data('term-id');
	});

	if (url_noamenities.indexOf("?") < 0) { url_noamenities = url_noamenities.replace(/&/, '?'); }

	window.location.href = url_noamenities;

	return false;
});

// load more button click action on search result page
tjq(".btn-load-more-accs").click(function(e){
	e.preventDefault();

	var url = tjq(this).attr('href');
	var _this = tjq(this);
	var wrapper = tjq(this).closest('.list-wrapper');
	var wrapper_class = tjq(this).closest('.list-wrapper').attr('class').split(" ").filter(Boolean).join('.');

	jQuery.ajax({
		url: url,
		type: "GET",
		success: function(response){
			var response_list =  tjq(tjq.parseHTML(response)).find('.' + wrapper_class);
			wrapper.children('div').append(response_list.children('div').html());
			tjq(window).trigger('resize');
			var load_more_btn = response_list.find('.btn-load-more-accs');
			if (load_more_btn.length) {
				_this.attr('href', load_more_btn.attr('href'));
			} else {
				_this.remove();
			}
		}
	});

	return false;
});

// make accommodation search works with valid date frame
tjq('.acc-searchform').submit(function(){
	tjq('.date-error').remove();

	var error_obj;
	var date_from_obj = tjq(this).find('input[name="date_from"]');
	var date_to_obj = tjq(this).find('input[name="date_to"]');

	if ((date_from_obj.val() != '') && (date_to_obj.val() != '')) {
		if (date_from_obj.datepicker("getDate") >= date_to_obj.datepicker("getDate")) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message1') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}

		var now = tjq.now();

		if (date_from_obj.datepicker("getDate") - now + 86400 * 1000 < 0) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message2') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}
	}
});

// remove date-error field when date change
tjq('input[name="date_from"], input[name="date_to"]').change(function(){
	tjq('.date-error').remove();
});

/* Tour Search Page */
tjq("#tour-type-filter .filters-option li").click(function(){
	var url_notour_type = tjq("#tour-type-filter").data('url-notour_type').replace(/&amp;/g, '&');
	if (tjq(this).hasClass('all-types')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');
			return false;
		} else {
			tjq("#tour-type-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#tour-type-filter .filters-option li.active").length == 0) {
			tjq("#tour-type-filter .filters-option li.all-types").addClass('active');
		} else {
			tjq("#tour-type-filter .filters-option li.all-types").removeClass('active');
			tjq("#tour-type-filter .filters-option li.active").each(function(index){
				url_notour_type += '&tour_types[]=' + tjq(this).data('term-id');
			});
		}
	}

	if (url_notour_type.indexOf("?") < 0) { url_notour_type = url_notour_type.replace(/&/, '?'); }

	window.location.href = url_notour_type;
});

// make tour search works with valid date frame
tjq('.tour-searchform').submit(function(){
	tjq('.date-error').remove();

	var error_obj;
	var date_from_obj = tjq(this).find('input[name="date_from"]');
	var date_to_obj = tjq(this).find('input[name="date_to"]');

	if ((date_from_obj.val() != '') && (date_to_obj.val() != '')) {
		if (date_from_obj.datepicker("getDate") > date_to_obj.datepicker("getDate")) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message1') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}

		var now = tjq.now();
		
		if (date_from_obj.datepicker("getDate") - now + 86400 * 1000 < 0) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message2') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}
	}
});

//make car search works with valid date frame
tjq('.car-searchform').submit(function(){
	var error_obj;
	tjq('.date-error').remove();
	var date_from_obj = tjq(this).find('input[name="date_from"]');
	var date_to_obj = tjq(this).find('input[name="date_to"]');
	if ((date_from_obj.val() != '') && (date_to_obj.val() != '')) {
		if (date_from_obj.datepicker("getDate") > date_to_obj.datepicker("getDate")) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message1') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();
			return false;
		}
		var now = tjq.now();
		if (date_from_obj.datepicker("getDate") - now + 86400 * 1000 < 0) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message2') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();
			return false;
		}
	}
});

/* Car Search Page */
// car type filter
tjq("#car-type-filter .filters-option li").click(function(){
	var url_nocar_type = tjq("#car-type-filter").data('url-nocar_type').replace(/&amp;/g, '&');
	if (tjq(this).hasClass('all-types')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');
			return false;
		} else {
			tjq("#car-type-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#car-type-filter .filters-option li.active").length == 0) {
			tjq("#car-type-filter .filters-option li.all-types").addClass('active');
		} else {
			tjq("#car-type-filter .filters-option li.all-types").removeClass('active');
			tjq("#car-type-filter .filters-option li.active").each(function(index){
				url_nocar_type += '&car_types[]=' + tjq(this).data('term-id');
			});
		}
	}
	if (url_nocar_type.indexOf("?") < 0) { url_nocar_type = url_nocar_type.replace(/&/, '?'); }
	window.location.href = url_nocar_type;
});
// car agent filter
tjq("#car-agent-filter .filters-option li").click(function(){
	var url_nocar_agent = tjq("#car-agent-filter").data('url-nocar_agent').replace(/&amp;/g, '&');
	if (tjq(this).hasClass('all-types')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');
			return false;
		} else {
			tjq("#car-agent-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#car-agent-filter .filters-option li.active").length == 0) {
			tjq("#car-agent-filter .filters-option li.all-types").addClass('active');
		} else {
			tjq("#car-agent-filter .filters-option li.all-types").removeClass('active');
			tjq("#car-agent-filter .filters-option li.active").each(function(index){
				url_nocar_agent += '&car_agents[]=' + tjq(this).data('term-id');
			});
		}
	}
	if (url_nocar_agent.indexOf("?") < 0) { url_nocar_agent = url_nocar_agent.replace(/&/, '?'); }
	window.location.href = url_nocar_agent;
});

// preference filter
tjq("#car-preferences-filter .filters-option li").click(function(){
	var url_nopreferences = tjq("#car-preferences-filter").data('url-nocar_preference').replace(/&amp;/g, '&');
	tjq("#car-preferences-filter .filters-option li.active").each(function(index){
		url_nopreferences += '&preferences[]=' + tjq(this).data('term-id');
	});
	if (url_nopreferences.indexOf("?") < 0) { url_nopreferences = url_nopreferences.replace(/&/, '?'); }
	window.location.href = url_nopreferences;
	return false;
});

/* Cruise Search Page */
// cruise type filter
tjq("#cruise-type-filter .filters-option li").click(function(){
	var url_nocruise_type = tjq("#cruise-type-filter").data('url-nocruise_type').replace(/&amp;/g, '&');

	if (tjq(this).hasClass('all-types')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');

			return false;
		} else {
			tjq("#cruise-type-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#cruise-type-filter .filters-option li.active").length == 0) {
			tjq("#cruise-type-filter .filters-option li.all-types").addClass('active');
		} else {
			tjq("#cruise-type-filter .filters-option li.all-types").removeClass('active');
			tjq("#cruise-type-filter .filters-option li.active").each(function(index){
				url_nocruise_type += '&cruise_types[]=' + tjq(this).data('term-id');
			});
		}
	}

	if (url_nocruise_type.indexOf("?") < 0) { url_nocruise_type = url_nocruise_type.replace(/&/, '?'); }

	window.location.href = url_nocruise_type;
});

// cruise line filter
tjq("#cruise-line-filter .filters-option li").click(function(){
	var url_nocruise_line = tjq("#cruise-line-filter").data('url-nocruise_line').replace(/&amp;/g, '&');

	if (tjq(this).hasClass('all-lines')) {
		if (! tjq(this).hasClass('active')) {
			tjq(this).toggleClass('active');

			return false;
		} else {
			tjq("#cruise-line-filter .filters-option li").removeClass('active');
			tjq(this).addClass('active');
		}
	} else {
		if (tjq("#cruise-line-filter .filters-option li.active").length == 0) {
			tjq("#cruise-line-filter .filters-option li.all-lines").addClass('active');
		} else {
			tjq("#cruise-line-filter .filters-option li.all-lines").removeClass('active');
			tjq("#cruise-line-filter .filters-option li.active").each(function(index){
				url_nocruise_line += '&cruise_lines[]=' + tjq(this).data('term-id');
			});
		}
	}

	if (url_nocruise_line.indexOf("?") < 0) { url_nocruise_line = url_nocruise_line.replace(/&/, '?'); }

	window.location.href = url_nocruise_line;
});

// make accommodation search works with valid date frame
tjq('.cruise-searchform').submit(function(){
	tjq('.date-error').remove();

	var error_obj;
	var date_from_obj = tjq(this).find('input[name="date_from"]');
	var date_to_obj = tjq(this).find('input[name="date_to"]');

	if ((date_from_obj.val() != '') && (date_to_obj.val() != '')) {
		if (date_from_obj.datepicker("getDate") >= date_to_obj.datepicker("getDate")) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message1') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}

		var now = tjq.now();

		if (date_from_obj.datepicker("getDate") - now + 86400 * 1000 < 0) {
			error_obj = '<label class="date-error">' + tjq('.search-when').data('error-message2') + '</label>';
			tjq('.search-when').append(error_obj);
			date_from_obj.focus();

			return false;
		}
	}
});

///////////////////////////////////////////////////////////////////////////////
////////////////////////////// WooCommerce ACTIONS ////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/* Shop page */
// Shop page View mode
tjq('.woocommerce-ordering .swap-tiles a').click( function(e){ 
	e.preventDefault();

	tjq(this).closest('ul').find('li').removeClass('active');
	tjq(this).parent().addClass('active');

	var view_mode = tjq(this).data( 'view' );

	if ( view_mode == 'list' ) { 
		tjq('ul.products').addClass('list');
	} else { 
		tjq('ul.products').removeClass('list');
	}
} );

/* display photo gallery */
function displayProductCarousel($item) {
	if (! tjq.fn.flexslider || $item.length < 1 || $item.is(":hidden")) {
		return;
	}

	var dataColumns = $item.data("columns");
	if (typeof dataColumns == "undefined") {
		dataColumns = 4;
	}

	if ( window.innerWidth < 480 ) { 
		dataColumns = 1;
	} else if ( window.innerWidth < 767 ) { 
		dataColumns = 3;
	}
	
	$item.flexslider({
		animation: "slide",
		animationLoop: true,
		controlNav: false,
		itemWidth: 210,
		itemMargin: 10,
		minItems: dataColumns,
		maxItems: dataColumns
	});
}

tjq(document).ready(function() { 
	tjq('.toggle-widget').click(function() { 
		var $parent_widget = tjq(this).closest('.widget');

		if ( tjq(this).parent().next().is(':visible') ) { 
			$parent_widget.removeClass('closed');
		} else { 
			$parent_widget.addClass('closed');
		}

		tjq(this).parent().next().stop().slideToggle(200);
	});

	tjq('body').bind('added_to_cart', function(e, fragments, cart_hash, $thisbutton) { 
		$thisbutton.parent().find('.added_to_cart').addClass('button');
	});

    /* Input incrementer*/
    tjq(".cart .quantity").append( '<div class="inc button_inc"><i class="soap-icon-roundtriangle-top"></i></div><div class="dec button_inc"><i class="soap-icon-roundtriangle-bottom"></i></div>' );

    tjq(".cart .quantity input").on( "change", function() {
        if ( tjq(this).parent().attr("max") && tjq(this).val() > tjq(this).parent().attr('max') ) {
            tjq(this).val( tjq(this).parent().attr('max') );
        }
        if ( tjq(this).parent().attr("min") && tjq(this).val() < tjq(this).parent().attr('min') ) {
            tjq(this).val( tjq(this).parent().attr('min') );
        }
    });

    tjq('body').on( 'click', '.button_inc', function () {

        var $button = tjq(this);
        var oldValue = $button.parent().find("input").val();

        if ( $button.hasClass('inc') ) {
            var max_val = 9999;

            if ( tjq(this).parent().attr("max") ) {
                max_val = tjq(this).parent().attr("max");
            }
            if (oldValue < max_val) {
                var newVal = parseFloat(oldValue) + 1;
            } else {
                newVal = max_val;
            }
        } else {
            // Don't allow decrementing below zero
            var min_val = 0;
            if ( tjq(this).parent().attr("min") ) {
                min_val = tjq(this).parent().attr("min");
            }
            if ( oldValue > min_val ) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                if ( tjq(this).parent() )
                newVal = min_val;
            }
        }

        if ( ! $button.parent().find("input").attr('disabled') ) { 
            $button.parent().find("input").val(newVal).change();
        }
    });
});

tjq(window).load(function() { 
	/* Product Carousel */
	if ( tjq('.up-sells.products').length > 0 ) { 
		displayProductCarousel(tjq('.up-sells.products'));
	}
	if ( tjq('.related.products').length > 0 ) { 
		displayProductCarousel(tjq('.related.products'));
	}
});

tjq('body').on( 'click', '.vc_tta-tab', function() {
	var contentId = tjq(this).find('a').attr("href");
	setInterval(function() {		

		if (tjq(contentId).find(".image-carousel").length > 0) {
			displayImageCarousel(tjq(contentId).find(".image-carousel"));
		}
		if (tjq(contentId).find(".photo-gallery").length > 0) {
			displayPhotoGallery(tjq(contentId).find(".photo-gallery"));
		}
		if (tjq(contentId).find(".testimonial").length > 0) {
			displayTestimonials();
		}

		tjq(window).trigger('resize');
	}, 100 );
});
