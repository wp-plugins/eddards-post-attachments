jQuery( document ).ready( function() {
    
    var global_eddard_state = false;

    eddardCreateInitPostAttachemntList = function( filter ) {
        
        jQuery( '#eddard-attachment-filter-container' ).hide( 'fast' );
        jQuery( "#eddard-attachment-inner-container" ).html( 
            jQuery( "#eddard-attachment-inner-container" ).attr( 'data-deftext' ) 
        );
        jQuery.post( ajaxurl, {
            'action'  : 'eddardRefreshList',
            'post_id' : jQuery( '.eddard-hidden-attachments-container' ).attr( "data-ofpost" ),
            'filter'  : filter || 'self',
        }, function( response ) {
            
            if( 'undefined' == typeof response ) {
                global_eddard_state = true;
                return;
            }
            
            var parsed_response = JSON && JSON.parse( response ) || jQuery.parseJSON( response );
            
            if( 0 >= parsed_response.length || 'undefined' == typeof parsed_response ) {
                global_eddard_state = true;
                return;
            }
            
            global_eddard_state = false;
            eddardComputeInnerContainer( parsed_response.count );
            var the_filter = filter || 'self';
            jQuery( '#eddard-attachment-filter-selector' ).val( the_filter );
            jQuery( "#eddard-attachment-inner-container" ).html( parsed_response.htm );
            jQuery( '#eddard-attachment-filter-container' ).show( 'slow' );
        } );
    }
    
    eddardClearAttachmentList = function() {
        jQuery( "#eddard-attachment-inner-container" ).html( 
            jQuery( "#eddard-attachment-inner-container" ).attr( 'data-deftext' ) 
        );
        jQuery( '#eddard-attachment-filter-container' ).hide( 'fast' );
    }
    
    eddardComputeInnerContainer = function( count ) {
        
        if( 620 >= jQuery( window ).width() ) {
            return;
        }
        
        var nwidth = (count * ( 200 )) + 400;
        jQuery( '#eddard-attachment-inner-container' ).width( nwidth );
        return;
    }
    
    jQuery( '#eddard-attachment-continer-close a' ).on( 'click', function( ev ) {
        
        ev.preventDefault();
        if( true === global_eddard_state ) {
            return;
        }
        jQuery( '.eddard-hidden-attachments-container' ).hide();
        eddardClearAttachmentList();
    } );
    
    jQuery( '#eddard-attachment-filter-selector' ).on( 'change', function() {
        
        if( true == global_eddard_state ) {
            return;
        }
        
        eddardCreateInitPostAttachemntList( jQuery( this ).val() );
        
    } );
    
    eddardAttachmentImageClick = function( ev, current_id ) {
        ev.preventDefault();
        if( true === global_eddard_state ) {
            return;
        }
        jQuery( '.eddard-attached-img-miniedit' ).hide( "fast" );
        jQuery( '#eddard-miniedit-' + current_id ).toggle();
    }
    
    
    eddardRemoveAttachmentImage = function( ev, current_id ) {
        
        ev.preventDefault();
        if( true === global_eddard_state ) {
            return;
        }
        jQuery( '#eddard-miniedit-container-' + current_id ).hide( "fast" );
        jQuery( '#eddard-miniedit-progress-' + current_id ).show( "fast" );
        global_eddard_state = true;
        
        jQuery.post( ajaxurl, {
            'action'          : 'eddardRemoveAttachment',
            'eddard_image_id' : current_id
        }, function( response ) {
            var parsed_response = JSON && JSON.parse( response ) || jQuery.parseJSON( response );
            if( 'undefined' == typeof parsed_response.state || false == parsed_response.state ) {
                
                jQuery( '#eddard-miniedit-progress-' + current_id ).hide( "fast" );
                jQuery( '#eddard-miniedit-container-' + current_id ).show( "fast" );
                global_eddard_state = false;
                alert( parsed_response.msg );
                return;
            } 
            
            if( true == parsed_response.state ) {
                
                global_eddard_state = false;
                alert( parsed_response.msg );
                jQuery( '#eddard-miniedit-progress-' + current_id ).hide( "fast" );
                jQuery( '#eddard-miniedit-container-' + current_id ).show( "fast" );
                eddardCreateInitPostAttachemntList( jQuery( '#eddard-attachment-filter-selector' ).val() );
                return;
            }
            
            global_eddard_state = false;
            
        } );
        
    }
    
    eddardTakeOverAttachmentImage = function( ev, current_id ) {
        
        ev.preventDefault();
        if( true == global_eddard_state ) {
            return;
        }
        
        jQuery( '#eddard-miniedit-container-' + current_id ).hide( "fast" );
        jQuery( '#eddard-miniedit-progress-' + current_id ).show( "fast" );
        global_eddard_state = true;
        
        jQuery.post( ajaxurl, {
            'action'          : 'eddardTakeOverAttachmentImage',
            'post_id'         : jQuery( '.eddard-hidden-attachments-container' ).attr( "data-ofpost" ),
            'eddard_image_id' : current_id
        }, function( response ) {
            var parsed_response = JSON && JSON.parse( response ) || jQuery.parseJSON( response );
            if( 'undefined' == typeof parsed_response.state || false == parsed_response.state ) {
                
                jQuery( '#eddard-miniedit-progress-' + current_id ).hide( "fast" );
                jQuery( '#eddard-miniedit-container-' + current_id ).show( "fast" );
                global_eddard_state = false;
                alert( parsed_response.msg );
                return;
            } 
            
            if( true == parsed_response.state ) {
                
                global_eddard_state = false;
                alert( parsed_response.msg );
                jQuery( '#eddard-miniedit-progress-' + current_id ).hide( "fast" );
                jQuery( '#eddard-miniedit-container-' + current_id ).show( "fast" );
                eddardCreateInitPostAttachemntList( jQuery( '#eddard-attachment-filter-selector' ).val() );
                return;
            }
            
            global_eddard_state = false;
            
        } );
        
    }
    
    
} );