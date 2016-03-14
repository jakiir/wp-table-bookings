(function() {
    tinymce.PluginManager.add('wtb_scg', function( editor, url ) {
        var wtbsc_tag = 'wtb';


        //add popup
        editor.addCommand('wtb_scg_popup', function(ui, v) {
            //setup defaults

            editor.windowManager.open( {
                title: 'Table bookings shortCode',
                width: jQuery( window ).width() * 0.3,
                height: (jQuery( window ).height() - 36 - 50) * 0.1,
                id: 'wtb-insert-dialog',
                body: [
                    {
                        type   : 'container',
                        html   : '<span class="tlp_loading">Loading...</span>'
                    },
                ],
                onsubmit: function( e ) {

                    var shortcode_str;
                    var id = jQuery("#wtb_scid").val();
                    var title = jQuery( "#wtb_scid option:selected" ).text();
                    if(id && id != 'undefined'){
                        shortcode_str = '[' + wtbsc_tag;
                            shortcode_str += ' id="'+id+'" title="'+ title +'"';
                        shortcode_str += ']';
                    }
                    if(shortcode_str) {
                        editor.insertContent(shortcode_str);
                    }else{
                        alert('No short code selected');
                    }
                }
            });

            putScList();
        });

        //add button
        editor.addButton('wtb_scg', {
            icon: 'wtb_scg',
            tooltip: 'WP Table Bookings',
            cmd: 'wtb_scg_popup',
        });

        function putScList(){
                var dialogBody = jQuery( '#wtb-insert-dialog-body' )
                jQuery.post( ajaxurl, {
                    action: 'wtbShortCodeList'
                }, function( response ) {

                    dialogBody.html(response);
                    console.log(response);
                });

        }

    });
})();