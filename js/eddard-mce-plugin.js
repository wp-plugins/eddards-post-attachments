
(function() {
    tinymce.create('tinymce.plugins.EddardMceBtnPlg', {
        
        init : function(ed, url) {
            ed.addButton( 'eddard_show_attachment_images', {
                
                title : eddardAdminVars.eddard_mce_btn_title,
                cmd : 'eddard_show_attachment_images',
                image : eddardAdminVars.eddard_icon_url
                
            } );
            
            ed.addCommand( 'eddard_show_attachment_images', function() {
                if( jQuery && 'undefined' != typeof jQuery ) {
                    if( true == jQuery( '.eddard-hidden-attachments-container' ).is( ':visible' ) ) {
                        jQuery( '.eddard-hidden-attachments-container' ).hide( "fast" );
                        eddardClearAttachmentList();
                    } else {
                        jQuery( '.eddard-hidden-attachments-container' ).show( "slow", function() {
                            eddardCreateInitPostAttachemntList( 'self' );
                        } );
                    }
                }
                
            } );
            
        },
 
        
        createControl : function(n, cm) {
            return null;
        },
 
        
        getInfo : function() {
            return {
                longname : 'Eddard Post Attachemnts',
                author : 'Eddard',
                authorurl : '',
                infourl : '',
                version : "1.0"
            };
        }
    });
 
    tinymce.PluginManager.add( 'eddard_mce_btn_plg', tinymce.plugins.EddardMceBtnPlg );
})();