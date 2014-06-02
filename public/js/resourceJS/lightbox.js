function lightbox(insertContent, ajaxContentUrl){

	// jQuery wrapper (optional, for compatibility only)
	(function($) {
	
		// add lightbox/shadow <div/>'s if not previously added
		if($('#lightbox').size() == 0){
			var theLightbox = $('<div id="lightbox"/>');
			var theShadow = $('<div id="lightbox-shadow"/>');
			$(theShadow).click(function(e){
				closeLightbox();
			});
			$('body').append(theShadow);
			$('body').append(theLightbox);
		}
		
		// remove any previously added content
		$('#lightbox').empty();
		
		// insert HTML content
		if(insertContent != null){
			$('#lightbox').append(insertContent);
		}
		
		// insert AJAX content
		if(ajaxContentUrl != null){
			// temporarily add a "Loading..." message in the lightbox
			$('#lightbox').append('<p class="loading">Loading...</p>');
			
			// request AJAX content
			$.ajax({
				type: 'GET',
				url: ajaxContentUrl,
				success:function(data){
					// remove "Loading..." message and append AJAX content
					$('#lightbox').empty();
					$('#lightbox').append(data);
				},
				error:function(){
					alert('AJAX Failure!');
				}
			});
		}
		/*
		var windowWidth = $(window).width();
    	var windowHeight = $(window).height();
    	//windowsize - 50px
   		var windowHeightFixed = windowHeight - 50;
    	var windowWidthFixed = windowWidth - 50;    

    	$('#lightbox').lightBox({          
        maxHeight: windowHeightFixed,
        maxWidth: windowWidthFixed
    	});
		*/

		// move the lightbox to the current window top + 100px
		$('#lightbox').css('top', $(window).scrollTop() + 100 + 'px');
		$('#lightbox-shadow').css('top', $(window).scrollTop() - 250 + 'px');

		// display the lightbox
		$('#lightbox').show();
		$('#lightbox-shadow').show();
	
	})(jQuery); // end jQuery wrapper
	
}

// close the lightbox
function closeLightbox(){
	
	// jQuery wrapper (optional, for compatibility only)
	(function($) {
		
		// hide lightbox/shadow <div/>'s
		$('#lightbox').hide();
		$('#lightbox-shadow').hide();
		
		// remove contents of lightbox in case a video or other content is actively playing
		$('#lightbox').empty();
	
	})(jQuery); // end jQuery wrapper
	
}