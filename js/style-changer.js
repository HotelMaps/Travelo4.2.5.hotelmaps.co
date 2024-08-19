// style changer
/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
 tjq = jQuery.noConflict();
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function (tjq) {

    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape...
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            // Replace server-side written pluses with spaces.
            // If we can't decode the cookie, ignore it, it's unusable.
            // If we can't parse the cookie, ignore it, it's unusable.
            s = decodeURIComponent(s.replace(pluses, ' '));
            return config.json ? JSON.parse(s) : s;
        } catch(e) {}
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return tjq.isFunction(converter) ? converter(value) : value;
    }

    var config = tjq.cookie = function (key, value, options) {

        // Write

        if (arguments.length > 1 && !tjq.isFunction(value)) {
            options = tjq.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setTime(+t + days * 864e+5);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // Read

        var result = key ? undefined : {};

        // To prevent the for loop in the first place assign an empty array
        // in case there are no cookies at all. Also prevents odd result when
        // calling tjq.cookie().
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = parts.join('=');

            if (key && key === name) {
                // If second argument (value) is a function it's a converter...
                result = read(cookie, value);
                break;
            }

            // Prevent storing a cookie that we couldn't decode.
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    tjq.removeCookie = function (key, options) {
        if (tjq.cookie(key) === undefined) {
            return false;
        }

        // Must not alter options, thus extending a fresh object...
        tjq.cookie(key, '', tjq.extend({}, options, { expires: -1 }));
        return !tjq.cookie(key);
    };

}));

function setCookie(name, value, expires, path, domain, secure){
    cookieStr = name + "=" + escape(value) + "; ";
    
    if(expires){
        expires = setExpiration(expires);
        cookieStr += "expires=" + expires + "; ";
    }
    if(path){
        cookieStr += "path=" + path + "; ";
    }
    if(domain){
        cookieStr += "domain=" + domain + "; ";
    }
    if(secure){
        cookieStr += "secure; ";
    }
    
    document.cookie = cookieStr;
}

function setExpiration(cookieLife){
    var today = new Date();
    var expr = new Date(today.getTime() + cookieLife * 24 * 60 * 60 * 1000);
    return  expr.toGMTString();
}

tjq(document).ready(function(){
    tjq(".style-changer .style-chagner-main .bg-img li a").each(function(index){
        var bg_left = index * -25;
        tjq(this).css('background-position', bg_left + 'px 0');
    });
    tjq(".style-changer .design-skins a").click(function(e) {
        e.preventDefault();
        tjq(this).closest("ul").children("li").removeClass("active");
        tjq(this).parent().addClass("active");
    });
    tjq("#style-changer .style-toggle").click(function(e) {
        e.preventDefault();
        if (tjq(this).hasClass("open")) {
            tjq("#style-changer").css("left", "0");
            tjq(this).removeClass("open");
            tjq(this).addClass("close");
        } else {
            tjq("#style-changer").css("left", "-275px");
            tjq(this).removeClass("close");
            tjq(this).addClass("open");
        }
    });
 
    tjq("#style-changer .apply-settings").on("click", function(e) {
        e.preventDefault();
        var headerStyle = tjq("#style-changer #header-style").val();
        var footerStyle = tjq("#style-changer #footer-style").val();
        var activeSkinObj = tjq("#style-changer .design-skins > .active");
        var colorSkin = activeSkinObj.removeClass("active").attr("class").replace(" ", "");
        activeSkinObj.addClass("active");
        tjq.cookie('headerStyle', headerStyle, { path: '/' } );
        tjq.cookie('footerStyle', footerStyle, { path: '/' } );
        tjq.cookie('colorSkin', colorSkin, { path: '/' } );
        location.reload();
    });

    tjq("#style-changer .layout-style a").click(function(e) {
        e.preventDefault();
        tjq(this).closest(".layout-style").children("a").removeClass("active");
        tjq(this).addClass("active");
        var layout;
        if ( tjq(this).hasClass('boxed') ) {
            if ( tjq('#boxed-style').length ) {
                tjq('#boxed-style').prop('disabled', false);
            } else {
                tjq('body').append('<style id="boxed-style">#page-wrapper { max-width: 1200px; margin: 0 auto; }</style>');
            }
            layout = 'boxed';
        } else {
            if ( tjq('#boxed-style').length ) {
                tjq('#boxed-style').prop('disabled', true);
            }
            layout = 'fw';
        }
        tjq.cookie('layout', layout, { path: '/' } );
    });

    tjq("#style-changer .style-chagner-main .bg-img li a").click(function(e) {
        e.preventDefault();
        if ( ! tjq('#style-changer .layout-style a.boxed').hasClass('active') ) {
            alert('Please select boxed layout.');
            return;
        }
        var img_url = 'http://www.soaptheme.net/wordpress/travelo/wp-content/uploads/bgimages/' + tjq(this).data('name');
        if ( tjq(this).hasClass('bkgd') ) { tjq('body').css('background', 'url(' + img_url + ') 50% 50% / cover no-repeat fixed' );
        } else { tjq('body').css('background', 'url(' + img_url + ') repeat scroll' ); }
        tjq.cookie('bgimg', tjq("#style-changer .bg-img li a").index(tjq(this)), { path: '/' } );
    });

});
/* Style changer options */
if (tjq("#style-changer").length > 0) {
    var headerStyle = tjq.cookie('headerStyle');
    var footerStyle = tjq.cookie('footerStyle');
    var colorSkin = tjq.cookie('colorSkin');
    var layout = tjq.cookie('layout');
    var bgimg = tjq.cookie('bgimg');

    if (headerStyle != null && headerStyle != "") {
        tjq("#style-changer #header-style option[value='" + headerStyle + "']").prop("selected", "selected");
    }

    if (footerStyle != null && footerStyle != "") {
        tjq("#style-changer #footer-style option[value='" + footerStyle + "']").prop("selected", "selected");
    }

    if (colorSkin != null) {
        tjq("#style-changer .design-skins li." + colorSkin).addClass("active").siblings().removeClass('active');
    }

    if (layout != null) {
        tjq("#style-changer .layout-style a." + layout).addClass("active").siblings().removeClass('active');
        if ( tjq("#style-changer .layout-style a." + layout).hasClass('boxed') ) tjq('body').append('<style id="boxed-style">#page-wrapper { max-width: 1200px; margin: 0 auto; }</style>');
    }

    if (bgimg != null) {
        var bg_obj = tjq("#style-changer .bg-img li a").eq(bgimg);
        var img_url = 'http://www.soaptheme.net/wordpress/travelo/wp-content/uploads/bgimages/' + bg_obj.data('name');
        if ( bg_obj.hasClass('bkgd') ) { tjq('body').css('background', 'url(' + img_url + ') 50% 50% / cover no-repeat fixed' );
        } else { tjq('body').css('background', 'url(' + img_url + ') repeat scroll' ); }
    }
}

tjq('#menu-item-1306 ul li a').click( function(e) {
    e.preventDefault();
    var r = confirm( 'This page is coming soon. Do you want to check our HTML site template which will be converted to WordPress in near future?' );
    if ( r == true ) {
        document.location.href = tjq(this).attr('href');
    }
});