var subtleBackgroundsFrontend = {
	toggleState: 			true,
	subtleContainer: 		'<div id="subtle-background-live-container">'
							+ '<div id="subtle-live-loading">Loading</div>'
							+ '</div>',
	subtleSlider: 			'<div id="subtle-background-live-header">'
							+ '<span>Slide through the Patterns below. Click on any pattern to test it as background. To save the background click on "Save Background".</span>'
							+ '<div id="subtle-live-toggle-button" onclick="subtleBackgroundsFrontend.toggleBox()">Hide/Show</div>'
							+ '<div id="subtle-live-save-button" onclick="subtleBackgroundsFrontend.saveBackground()">Save Background</div>'
							+'</div>'
							+ '<div id="subtle-background-live-footer">'
							+ 'Patterns made & provided by <a href="http://www.subtlepatterns.com" target="_blank">Subtle Patterns</a> | '
							+ 'Plugin written by <a href="http://www.clubdesign.at" target="_blank">ClubDesign - Web Development</a> | '
							+ '<a href="http://plugins.clubdesign.at/?ref=plugdon" target="_blank">Help us! How to donate?</a>'
							+'</div>'
							+ '<div class="flexslider carousel">'
							+ '<ul class="slides">'
							+ '</ul>'
							+ '</div>',
    subtleitem:     		'<li>'
    						+ '<div class="subtle-image" style="background-image: url(\'{{url}}\')" onclick="subtleBackgroundsFrontend.setBackground(\'{{fetch}}\')">'
    						+ '<div class="subtle-name">{{name}}</div>'
    						+ '</div>'
    						+ '</li>',
    selectedBackground: 	'',
	inject: function() {
		var self = this;
		jQuery('body').prepend( self.subtleContainer );
		this.update();

	},

	update: function() {
		
		var container = jQuery('#subtle-background-live-container');
        var self = this;

		jQuery.post(ajaxurl, {'action':'subtlepat','action_type':'getPatterns'}, function( response ) {

			response = jQuery.parseJSON( response );

			if( response.error ) {
                alert(response.error);
                return;
            }

			container.html( self.subtleSlider );
			var ul = jQuery('#subtle-background-live-container').find('ul');

			jQuery.each(response, function( k, v ) {

                var itemtemplate = self.subtleitem;
                ul.append( itemtemplate.replace('{{url}}', v.url).replace('{{fetch}}', v.url).replace('{{name}}', v.name) );

            }); 

            jQuery('.flexslider').flexslider({
				animation: "slide",
				animationLoop: false,
				itemWidth: 210,
				itemMargin: 5,
				slideshow: false,
				controlNav: false 
			});

		});

	},

	setBackground: function( url ) {
		
		jQuery('body').css('background-image', 'url(' + url + ')');
		this.selectedBackground = url;

	},

	saveBackground: function() {
		
		if( this.selectedBackground == '' ) {
			alert('No background selected! Please select any background before you try to save!');
			return;
		}
		jQuery('#subtle-live-save-button').html('Please wait, while i am saving the background..');

        jQuery.post(ajaxurl, {'action':'subtlepat','action_type':'setBackground', 'url': this.selectedBackground}, function( response ) {
            
            response = jQuery.parseJSON( response );
            
            if( response.success  ) window.location.reload();
            	else alert( response.error );
        });

	},

	toggleBox: function() {
		
		if( this.toggleState ) {
			jQuery('#subtle-background-live-container').animate({
				'bottom' : '-115px'
			}, 400);
			this.toggleState = false;
		} else {
			jQuery('#subtle-background-live-container').animate({
				'bottom' : '50px'
			}, 400);
			this.toggleState = true;
		}
	}

}

jQuery(document).ready(function() {
    
    subtleBackgroundsFrontend.inject();
                    
});