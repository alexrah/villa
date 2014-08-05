
/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.noConflict();
jQuery.cookie = function (key, value, options) {

    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};


jQuery(window).load( function() {
	// add the loaded class to <body> with small delay to avoid low framerate	
	setTimeout(function() {
		jQuery('body').addClass('loaded');
	}, 500);
	
	// style area
	if(jQuery('#gkStyleArea')){
		jQuery('#gkStyleArea').find('a').each(function(i,element){
			jQuery(element).click(function(e){
	            e.preventDefault();
	            e.stopPropagation();
				changeStyle(i+1);
			});
		});
	}
	// font-size switcher
	if(jQuery('#gkTools') && jQuery('#gkMainbody')) {
		var current_fs = 100;
		
		jQuery('#gkMainbody').css('font-size', current_fs+"%");
		
		jQuery('#gkToolsInc').click(function(e){ 
			e.stopPropagation();
			e.preventDefault(); 
			if(current_fs < 150) {  
				jQuery('#gkMainbody').animate({ 'font-size': (current_fs + 10) + "%"}, 200); 
				current_fs += 10; 
			} 
		});
		jQuery('#gkToolsReset').click(function(e){ 
			e.stopPropagation();
			e.preventDefault(); 
			jQuery('#gkMainbody').animate({ 'font-size' : "100%"}, 200); 
			current_fs = 100; 
		});
		jQuery('#gkToolsDec').click(function(e){ 
			e.stopPropagation();
			e.preventDefault(); 
			if(current_fs > 70) { 
				jQuery('#gkMainbody').animate({ 'font-size': (current_fs - 10) + "%"}, 200); 
				current_fs -= 10; 
			} 
		});
	}
	
	if(jQuery('#system-message-container').length != 0){
		  jQuery('#system-message-container').each(function(i, element){
		  		(function() {
		  		     jQuery(element).fadeOut('slow');
		  		}).delay(3500);
	      });
	} 
	
	 // K2 font-size switcher fix
	 if(jQuery('#fontIncrease') && jQuery('.itemIntroText')) {
	 	jQuery('#fontIncrease').click(function() {
	 		jQuery('.itemIntroText').attr('class', 'itemIntroText largerFontSize');
	 	});
	 	
	 	jQuery('#fontDecrease').click( function() {
	 		jQuery('.itemIntroText').attr('class', 'itemIntroText smallerFontSize');
	 	});
	 }	
	 
	// login popup
	if(jQuery('#gkPopupLogin').length > 0) {
		var popup_overlay = jQuery('#gkPopupOverlay');
		popup_overlay.css({'display': 'none', 'opacity' : 0});
		popup_overlay.fadeOut();
		
		jQuery('#gkPopupLogin').css({'display': 'block', 'opacity': 0, 'height' : 0});
		var opened_popup = null;
		var popup_login = null;
		var popup_login_h = null;
		var popup_login_fx = null;
		
		if(jQuery('#gkPopupLogin')) {

			popup_login = jQuery('#gkPopupLogin');
			popup_login.css('display', 'block');
			popup_login_h = popup_login.find('.gkPopupWrap').outerHeight();
			 
			jQuery('#gkLogin').click( function(e) {
				e.preventDefault();
				e.stopPropagation();
				popup_overlay.css({'opacity' : 0.6});
				popup_overlay.fadeIn('slow');
				
				popup_login.animate({'opacity':1, 'height': popup_login_h},200, 'swing');
				opened_popup = 'login';
				
				(function() {
					if(jQuery('#modlgn-username')) {
						jQuery('#modlgn-username').focus();
					}
				}).delay(600);
			});
		}
		
		popup_overlay.click( function() {
			if(opened_popup == 'login')	{
				popup_overlay.fadeOut('slow');
				popup_login.css({
					'opacity' : 0,
					'height' : 0
				});
			}
		});
	}

	// scrolling effects - create the lists of elements to animate
	jQuery('body').find('.animate').each(function(i, element) {
		elementsToAnimate.push(['animation', element, jQuery(element).offset().top]);
	});
	
	jQuery('body').find('.animate_queue').each(function(i, element) {
		elementsToAnimate.push(['queue_anim', element, jQuery(element).offset().top]);
	});
});
//
jQuery(document).ready(function() {		
	
	// menu movements
	if(jQuery('body').find('.gk-intro').length != 0) {
	
		var IS = jQuery('.gk-intro .gkIsWrapper-gk_creativity');
				
		jQuery(window).scroll(function() {
			
			var intro = jQuery('body').find('.gkIsWrapperFixed');
			var introh = intro.height();
			var pos = jQuery(window).scrollTop();
			
			if(pos >= 100) {
				jQuery('#gkTop').css('top', '0px');
			} else if(pos < 100) {
				jQuery('#gkTop').css('top', '-100px');
			}
			
			if(!Browser.ie8 && pos < introh && jQuery(window).height() > 1040) {
				jQuery(intro).find('.figcaption').css('top', Math.floor((1.0 * pos) / 8.0) + "px");
			}
			
			if(IS.length > 0) {
				if(pos > introh) {
					if(IS.css('display') != 'none') {
						IS.css('display', 'none');
					}
				} else {
					if(IS.css('display') != 'block') {
						IS.css('display', 'block');
					}
				}
			}
		});
	}
	
	// SmoothScroll jQUery substitue
	jQuery('a[href^="#"]').click( function (e) {
	    e.preventDefault();
	    var target = this.hash,
	    $target = jQuery(target);
	    
	    if($target.length) {
	    	jQuery('html, body').stop().animate({
	    	    'scrollTop': $target.offset().top
	    	}, 1000, 'swing', function () {
	    	    window.location.hash = target;
	    	});
	    } else {
	    	window.location.hash = target;
	    }
	});
	
	// team overlays
	if(jQuery(document).find('.gkTeam')) {
		var figures = jQuery(document).find('.gkTeam figure');
		
		jQuery(figures).each(function(i, figure) {
			figure = jQuery(figure);
			if(
				figure.attr('data-fb') != undefined || 
				figure.attr('data-twitter') != undefined || 
				figure.attr('data-gplus') != undefined
			) {
				var overlay = new jQuery('<div class= "gkTeamOverlay"></div>');
				
				var htmloutput = '';
				var classcounter = 0;
				
				if(figure.attr('data-fb') != undefined) {
					htmloutput += '<a href="'+figure.attr('data-fb')+'" data-type="fb">Facebook</a>';
					classcounter++;
				}
				
				if(figure.attr('data-twitter') != undefined) {
					htmloutput += '<a href="'+figure.attr('data-twitter')+'" data-type="twitter">Twitter</a>';
					classcounter++;
				}
				
				if(figure.attr('data-gplus') != undefined) {
					htmloutput += '<a href="'+figure.attr('data-gplus')+'" data-type="gplus">Google+</a>';
					classcounter++;
				}
				
				jQuery(overlay).html(htmloutput);
				jQuery(overlay).addClass('gkIcons' + classcounter);
				
				figure.find('img').after(overlay);
				
				//overlay.inject(figure.getElement('img') , 'after');
				
				figure.mouseenter(function() { 
					figure.addClass('hover'); 
					var linksAmount = figure.find('.gkTeamOverlay a').length;
					for(i = 0; i < linksAmount; i++) {
						gkAddClass(figure.find('.gkTeamOverlay').find('a')[i], 'active', i);	
					}
				});
				
				figure.mouseleave(function() { 
						figure.removeClass('hover'); 
						figure.find('.gkTeamOverlay a').removeClass('active');
				});
			}
		});
	}
});
// scroll effects
//
// animations
var elementsToAnimate = [];
// scroll event
jQuery(window).scroll(function() {
	// animate all right sliders
	if(elementsToAnimate.length > 0) {		
		// get the necessary values and positions
		var currentPosition = jQuery(window).scrollTop();
		var windowHeight = jQuery(window).height();
		// iterate throught the elements to animate
		if(elementsToAnimate.length) {
			for(var i = 0; i < elementsToAnimate.length; i++) {
				if(elementsToAnimate[i][2] < currentPosition + (windowHeight / 1.75)) {
					// create a handle to the element
					var element = elementsToAnimate[i][1];
					// check the animation type
					if(elementsToAnimate[i][0] == 'animation') {
						gkAddClass(element, 'loaded', false);
						// clean the array element
						elementsToAnimate[i] = null;
					}
					// if there is a queue animation
					else if(elementsToAnimate[i][0] == 'queue_anim') {
						element.getElements('.animate_queue_element').each(function(item, j) {
							gkAddClass(item, 'loaded', j);
						});
						// clean the array element
						elementsToAnimate[i] = null;
					}
				}
			}
			// clean the array
			elementsToAnimate = elementsToAnimate.clean();
		}
	}
});
//
function gkAddClass(element, cssclass, i) {
	var delay = jQuery(element).attr('data-delay');
	
	if(!delay) {
		delay = (i !== false) ? i * 150 : 0;
	}

	setTimeout(function() {
		element.addClass(cssclass);
	}, delay);
}
// Function to change styles
function changeStyle(style){
	var file1 = $GK_TMPL_URL+'/css/style'+style+'.css';
	var file2 = $GK_TMPL_URL+'/css/typography/typography.style'+style+'.css';
	jQuery('head').append('<link rel="stylesheet" href="'+file1+'" type="text/css" />');
	jQuery('head').append('<link rel="stylesheet" href="'+file2+'" type="text/css" />');
	jQuery.cookie('gk_creativity_j30_style', style, { expires: 365, path: '/' });
}
