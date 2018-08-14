(function ($) {
	"use strict";

	// QR not scanned yet
	if (step == 1) 
	{
		$("#step1").removeClass('hidden');
		$("#step2").addClass('hidden');

		// Poll server, is user scan QR throw his application?
		var timerId = setInterval(function() {
			// Detect URL base dir
	    		var path = window.location.href; path = path.substring (0, path.lastIndexOf('/'));

			var r = new XMLHttpRequest();
			r.open("POST", path + "/ajax.php", true);
			r.onreadystatechange = function () {
				if (r.readyState != 4 || r.status != 200) return;
				if (r.responseText !='') {
					var result = JSON.parse(r.responseText);

					// Login failed?	
					if (result.address == false) return;

					// If already registered, show dashboard
					if (result.info.fio) document.location = path + "/dashboard.php";
					// If not registered yet, need to fill the form
					else {
						// Stop polling
						if (timerId) clearInterval (timerId);

						// and show form
						$("#step1").addClass('hidden');
						$("#step2").removeClass('hidden');
					}
				}
			};
			r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			r.send("nonce="+nonce);
		}, 5* 1000); // 5 sec

		// If user is not active..
		var timerTimeout = setTimeout(function() {
			// Change a view
			$("#qr").parent()./* div-> */addClass('timeout').after('<p style="color:red">Timeout. Refresh for use.</p>')
				.parent()./* a-> */attr({'href': document.location, 'title': 'Press to refresh'});

			// Stop polling
			clearInterval (timerId);
		}, 1* 60* 1000); // 5 min

	}
	// QR scanned, but need to fill form to register
	else if (step == 2) 
	{
		$("#step1").addClass('hidden');
		$("#step2").removeClass('hidden');
	}    

})(jQuery);