/*
 * Capslock 0.2 - jQuery plugin to detect if a user's caps lock is on or not.
 * 
 * Provides events, "caps_lock_on" and "caps_lock_off", that custom functions can be bound to.
 * The capslock function can be called on a specific element, a set of elements, or globally:
 * 		$("#my_textarea").capslock(options);	// One textarea
 * 		$("textarea").capslock(options);		// All textareas
 * 		$().capslock(options);					// Globally
 *
 * Copyright (c) 2009 Arthur McLean
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 */

; // Just in case the previously included plug-in failed to close with a semi-colon.
(function($) {
	
	$.fn.capslock = function(options) {
		
		if (options) $.extend($.fn.capslock.defaults, options);
		
		this.each(function() {
			$(this).bind("caps_lock_on", $.fn.capslock.defaults.caps_lock_on);
			$(this).bind("caps_lock_off", $.fn.capslock.defaults.caps_lock_off);
			
			$(this).keypress(function(e){
				check_caps_lock(e);
			});
		});
		
		return this;
	};
	

	
	// The actual check:
	function check_caps_lock(e)
	{
		var ascii_code	= e.which;
		var shift_key	= e.shiftKey;
		if( (65 <= ascii_code) && (ascii_code <= 90) && !shift_key)
		{
			$(e.target).trigger("caps_lock_on");
		}
		else
		{
			$(e.target).trigger("caps_lock_off");
		}
		
	}
	
	// Public definition of defaults for easy overriding:
	$.fn.capslock.defaults = {
		caps_lock_on:	function() {},
		caps_lock_off:	function() {}
	};
	
})(jQuery);

