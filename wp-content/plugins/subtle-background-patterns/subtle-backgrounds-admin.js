var subtleBackgrounds = {
    
    uploadform:     '#upload-form',
    customform:     '<p>'
                    + '<label for="choose-from-library-link">Or choose an pattern from Subtlepatterns.com:</label><br>'
                    + '<a id="choose-from-subtle" class="button" href="javascript:subtleBackgrounds.launch()" id="subtle-init">'
                    + 'Select a SubtlePattern as Background'
                    + '</a>'
                    + '</p>',
    modal:          jQuery('<div id="subtle-modal">'
                    + '<a id="subtle-modal-close" href="javascript:subtleBackgrounds.close()" title="Close"></a>'
                    + '<div id="subtle-modal-header">'
                    + '<h1>Select a SubtlePattern</h1>'
                    + '<div class="description">Click on the pattern to set it as background for your page!</div>'
                    + '</div>'
                    + '<div id="subtle-modal-sub-header">'
                    + 'Patterns made & provided by <a href="http://www.subtlepatterns.com" target="_blank">Subtle Patterns</a> | '
                    + 'Plugin written by <a href="http://www.clubdesign.at" target="_blank">ClubDesign - Web Development</a> | '
                    + '<span>You can help us keeping the plugin free & provide more updates: <a class="donate" href="http://plugins.clubdesign.at/?ref=plugdon" target="_blank">How to Donate</a>'
                    + '</div>'
                    + '<div id="subtle-modal-content">'
                    + 'Please wait while i am loading the patterns.'
                    + '</div>'),
    modalbg:        jQuery('<div id="subtle-modal-bg"></div>'),
    subtleitem:     '<li style="background-image: url(\'{{url}}\')" onclick="subtleBackgrounds.setBackground(\'{{fetch}}\')"><div class="subtle-name">{{name}}</div></li>',
    subtleLive:     '<tr valign="top" style="text-shadow: none !important;">'
                    + '<th scope="row" style="background: #4BB461; color: #FFF; font-weight: bold; text-shadow: none !important;">Subtle Pattern LiveMode</th>'
                    + '<td><fieldset><legend class="screen-reader-text"><span>Subtle Patter Live Mode</span></legend>'
                    + '<label>'
                    + '<input name="subtle-pattern-live-mode" id="subtle-pattern-live-mode-1" type="radio" value="1">'
                    + ' Live Mode enabled </label>'
                    + '<label>'
                    + '<input name="subtle-pattern-live-mode" id="subtle-pattern-live-mode-0" type="radio" value="0">'
                    + ' Live Mode disabled </label>'
                    + '<p style="color: #777; font-style: italic">'
                    + 'Enables the live preview mode on the frontend of your blog. Viewable just for admins! Try it..'
                    + '</p>'
                    + '</fieldset></td>'
                    + '</tr>',
    
    inject: function() {
        
        jQuery(this.uploadform).append( this.customform );

        jQuery('#save-background-options').closest('form').find('.form-table tbody').prepend( jQuery(this.subtleLive) );
        jQuery('#subtle-pattern-live-mode-' + subtle_live_mode_option).attr('checked', true);
        
    },
    
    launch: function() {
        var self = this;

        jQuery('body').prepend( this.modalbg ).prepend( this.modal );
        this.modalbg.fadeIn();
        this.modal.fadeIn(200, function() { self.update(); });
        
    },
    
    update: function() {

        var container = jQuery('#subtle-modal-content');
        var self = this;
        
        jQuery.post(ajaxurl, {'action':'subtlepat','action_type':'getPatterns'}, function( response ) {

            response = jQuery.parseJSON( response );

            if( response.error ) {
                alert(response.error);
                return;
            }
            
            container.html('<ul id="subtle-pattern-list">'
                            + '</ul>'
                            + '<div style="clear:both"></div>');

            var ul = jQuery('#subtle-pattern-list');

            jQuery.each(response, function( k, v ) {

                var itemtemplate = self.subtleitem;
                ul.append( itemtemplate.replace('{{url}}', v.url).replace('{{fetch}}', v.url).replace('{{name}}', v.name) );
                // console.log(v.url);  

            }); 


        });

    },

    setBackground: function( url ) {
        var self = this;

        jQuery.post(ajaxurl, {'action':'subtlepat','action_type':'setBackground', 'url': url}, function( response ) {
            
            response = jQuery.parseJSON( response );

            if( response.success  ) window.location.reload();
                else alert( response.error );

        });

    },
    
    close: function() {
        this.modal.fadeOut(function(){ jQuery(this).remove() });
        this.modalbg.fadeOut(function(){ jQuery(this).remove() });
    }

}

jQuery(document).ready(function($) {
    
    subtleBackgrounds.inject();
                    
});