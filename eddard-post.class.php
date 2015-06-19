<?php

class EddardPost {
    
    const MCE_PLUGIN = 'eddard_mce_btn_plg';
    const MCE_FEATURE_ID = 'eddard_show_attachment_images';
    const EDDARD_TDOMAIN = 'eddard-pa';
    private $plugin_url = '';
    private $plugin_path = '';
    private $admin_base = '';
    
    public function __construct() {
        
        $this->plugin_url = plugin_dir_url( __FILE__ );
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->admin_base = admin_url();
        
    }
    
    /**
     * Loads necessary javascript and css files on WP admin
     * @param string $hooked_by - the file name where the stuff is loaded (e.g.: edit.php)
     * @return void
     * @access public
     * @since 1.0
     * @todo If possible we should filter the script and css loading by the $hooked_by
     * parameter
    **/
    public function eddardAdminEnqueue( $hooked_by ) {
        
        wp_register_style( 'eddard-admin-style', $this->plugin_url . 'css/eddard-post-attachment.css' );
        wp_enqueue_style( 'eddard-admin-style' );
        
        wp_register_script( 'eddard-admin-script', $this->plugin_url . 'js/eddard-post-attachment.js' );
        wp_localize_script( 'eddard-admin-script', 'eddardAdminVars', array(
            'eddard_mce_btn_title' => __( 'Eddard Post Attachments', self::EDDARD_TDOMAIN ),
            'eddard_icon_url'      => $this->plugin_url . 'icons/eddardattach.jpg',
        ) );
        wp_enqueue_script( 'eddard-admin-script' );
    }
    
    /**
     * Loads the defined text domain for the plugin. This method hooked to init action
     * @return void
     * @access public
     * @since 1.0
     * @todo Later we should consider using plugins_loaded hook
    **/
    public function eddardLocalizePlugin() {
        load_plugin_textdomain( self::EDDARD_TDOMAIN , null , dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    /**
     * Register a Tiny MCE plugin callbac and  new Tiny MCE button for post attachments and its callback
     * @retun void
     * @access public
     * @since 1.0
    **/
    public function eddardMceRegisterPlugin() {
        
        add_filter( 'mce_external_plugins', array( &$this, 'eddardMceAddPlugin' ) );
        add_filter( 'mce_buttons', array( &$this, 'eddardMceAddButtons' ) );
        
    }
    
    /**
     * Adds the post attachemnt Tiny MCE plugin to the plugins will be loaded to
     * the post editor
     * @param array $plugin_array - contains all Tiny Mce plugin handles. We append ours to it
     * @return array
     * @access public 
     * @since 1.0
    **/
    public function eddardMceAddPlugin( $plugin_array ) {
        
        $plugin_array[ self::MCE_PLUGIN ] = $this->plugin_url . 'js/eddard-mce-plugin.js';
        
        return $plugin_array;
    }
    
    /**
     * Adds a new button to the Tiny MCE editor first row. This method hooked to mce_buttons filter, what
     * is for the first row on editor screen.
     * @param array $buttons - the Tiny Mce buttons
     * @return array
     * @access public 
     * @since 1.0
    **/
    public function eddardMceAddButtons( $buttons ) {
        
        array_push( $buttons, self::MCE_FEATURE_ID );
        
        return $buttons;
    }
    
    /**
     * Creates a hidden field after the post/page title. This will contain the post attachemnts and
     * will apper if user clicks on tiny mce Eddard Post Attachment icon. This hooked to edit_form_after_title
     * action, and it outputs its content rather then returning it
     * @return void
     * @access public
     * @since 1.0
    **/
    public function eddardMceWorkingField() {
        global $post;
        $htm = '
        <div class="eddard-hidden-attachments-container" data-ofpost="'. $post->ID .'">
            <div id="eddard-attachemnts-header">
                <div id="eddard-attachment-main-title">'. __( "Post attachments", self::EDDARD_TDOMAIN ) .'</div>
                <div id="eddard-attachment-continer-close">
                    <a href="#" title="'. __( "Close attachments", self::EDDARD_TDOMAIN ) .'">
                    '. __( "Close", self::EDDARD_TDOMAIN ) .'
                    </a>
                </div>
                <div class="clear"></div>
            </div>    
            <div id="eddard-attachment-list-container">
            
                <div id="eddard-attachment-filter-container">
                    <div id="eddard-attachment-filter-selector-div">
                        <select id="eddard-attachment-filter-selector">
                            <option value="self">'. __( "Current attachments", self::EDDARD_TDOMAIN ) .'</option>
                            <option value="attached">'. __( "Attached to other", self::EDDARD_TDOMAIN ) .'</option>
                            <option value="unattached">'. __( "Unattached", self::EDDARD_TDOMAIN ) .'</option>
                        </select>
                    </div>
                </div>
            
                <div id="eddard-attachment-inner-container" data-deftext="'.  __( "Attachment list creation...", self::EDDARD_TDOMAIN ) .'">
                '. __( "Attachment list creation...", self::EDDARD_TDOMAIN ) .'
                </div>
            </div>
        </div>';
        
        echo $htm;
    }
    
    /**
     * Get all attachments attached to a specified post.
     * @param int $id - the ID of the currently edited post
     * @param string $filter - filter option to determine what images should be displayed.
     * This can be self=images attached to the current post, attached=all images what attached
     * to any post (except the current one), unattached=images not attached to any post
     * @return array
     * @access private
     * @since 1.0
    **/
    private function eddardGetAttachments( $id=null, $filter='self' ) {
        
        if( true == empty( $id ) ) {
            return array();
        }
        
        if( 'self' === $filter ) {
            //retrieve only images attached to the currently edited post
            $args = array(
                'post_parent' => $id,
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'order' => 'date',
                'order' => 'DESC',
                'exclude' => get_post_thumbnail_id( $id ),
                //'post_mime_type' => 'image/jpeg,image/png,image/gif',
            );
            $attachments = get_posts( $args );
            
            return $attachments;
        } 
        
        if( 'attached' !== $filter && 'unattached' !== $filter ) {
            return array();
        }
        
        $args = array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'order' => 'date',
            'order' => 'DESC',
            //'post_mime_type' => 'image/jpeg,image/png,image/gif',
        );
        $attachments = get_posts( $args );
        
        $attached = array();
        $unattached = array();
        
        foreach( $attachments as $a ) {
            
            if( false == isset( $a->post_parent ) || true == empty( $a->post_parent ) ) {
                array_push( $unattached, $a );
            } else {
                if( $id == $a->post_parent ) {
                    continue;
                }
                array_push( $attached, $a );
            }
            
        }
        
        return ( 'attached' === $filter ) ? $attached : $unattached;
    }
    
    /**
     * Creates a html container with attachment images
     * @param array $attachments - the attachment objects in an array
     * @param string $filter - this filter tells if the images attached, unattached or attached to current post
     * self=current post, attached, unattached
     * @return string
     * @access private
     * @since 1.0
    **/
    private function eddardCreateAttachmentHtml( $attachments=array(), $filter='self' ) {
        
        $attachment_count = count( $attachments );
        
        $htm = '
        
        <ul id="eddard-image-list-ul">
        ';
        foreach( $attachments as $a ) {
            $thumb = wp_get_attachment_image_src( $a->ID );//url, width , height, if resized
            $full = wp_get_attachment_image_src( $a->ID, 'full' );
            $htm .= '
            <li id="eddard-attached-img-li-'. $a->ID .'">
                <div class="eddard-attached-image-box">
                    <img title="'. __( "Click the image to toggle edit screen", self::EDDARD_TDOMAIN ) .'" 
                    onclick="eddardAttachmentImageClick(event, '. $a->ID .')"
                    data-imgid="'. $a->ID .'" 
                    class="eddard-attached-img" 
                    id="eddard-attached-img-'. $a->ID .'" 
                    src="'. $thumb[0] .'" />
                    <div class="eddard-attached-img-miniedit" id="eddard-miniedit-'. $a->ID .'">
                        <div class="edddard-miniedit-container" id="eddard-miniedit-container-'. $a->ID .'">
                          
                        '. $this->eddardCreateMiniEditor( $a, $full, $filter ) .'
                            
                        </div>
                        <div class="eddard-miniedit-progress" id="eddard-miniedit-progress-'. $a->ID .'">
                            '. __( "Please wait...", self::EDDARD_TDOMAIN ) .'
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </li>';
        }
        $htm .= '</ul>';
        
        return $htm;
        
    }
    
    /**
     * Creates a html for image mini editor. The content depends on the filter
     * @param object $attachment - the attachment post object
     * @param array $url - the image information in an array served by wp_get_attachment_image_src
     * @param string $filter - type of request (self, attached, unattached)
     * @return string
     * @access private
     * @since 1.0
    **/
    private function eddardCreateMiniEditor( $attachment=null, $url=array(), $filter='self' ) {
        
        if( true == empty( $attachment ) || true == empty( $url ) ) {
            return "";
        }
        
        $attachment_remove = '';
        if( 'self' === $filter ) {
            $attachment_remove = '
            <a href="#" class="eddard-remove-img-link" onclick="eddardRemoveAttachmentImage( event, '. $attachment->ID .' )" 
            data-imgid="'. $attachment->ID .'" 
            title="'. __( "This image will no longer attached to the current post/page" ,self::EDDARD_TDOMAIN ) .'">
            '. __( "REMOVE from post", self::EDDARD_TDOMAIN ) .'
            </a><br/>
            ';    
        }
        
        $attachment_takeover = '';
        if( 'attached' === $filter || 'unattached' === $filter ) {
            $attachment_takeover = '
            <a href="#" class="eddard-takeover-img-link" onclick="eddardTakeOverAttachmentImage( event, '. $attachment->ID .' )" 
            data-imgid="'. $attachment->ID .'" 
            title="'. __( "This will attach this image to the current post/page" ,self::EDDARD_TDOMAIN ) .'">
            '. __( "Attach to current", self::EDDARD_TDOMAIN ) .'
            </a><br/>
            ';
        }
        
        $htm = '
        <a href="'. $url[0] .'" target="_blank">
        '. __( "Click for full size", self::EDDARD_TDOMAIN ) .'
        </a> ( '. $url[1] .'x'. $url[2] .' )<br/>
        
        <a href="'. admin_url() .'upload.php?item='. $attachment->ID .'">
        '. __( "Edit with Media Libary", self::EDDARD_TDOMAIN ) .'
        </a><br/>
        
        '. $attachment_remove .'
        
        '. $attachment_takeover .'
        
        <span class="eddard-image-postdate">
        '. __( "Last modified", self::EDDARD_TDOMAIN ) .': '. $attachment->post_date .'
        </span><br/>
        
        <label for="eddardAttachedImageFullUrl-'. $attachment->ID .'">'. __( "Image url (full size)", self::EDDARD_TDOMAIN ) .'</label><br/>
        <input type="text" 
        name="eddardAttachedImageFullUrl-'. $attachment->ID .'" 
        id="eddardAttachedImageFullUrl-'. $attachment->ID .'" 
        value="'. $url[0] .'" />
        
        ';
        
        return $htm;
    }
    
    /**
     * This method serves the ajax request to refresh the attachment image list
     * @return void
     * @access public
     * @since 1.0
    **/
    public function eddardRefreshList() {
        
        $post_id = ( true == isset( $_POST['post_id'] ) && false == empty( $_POST['post_id'] ) ) ? $_POST['post_id'] : null;
        if( true == empty( $post_id ) ) {
            echo json_encode( array(
                'status' => false,
                'msg'    => "",
                'htm'    => "",
                'count'  => 0
            ) );
            exit();
        }
        $filter = ( true == isset( $_POST['filter'] ) && false == empty( $_POST['filter'] ) ) ? $_POST['filter'] : 'self';
        
        $attachments = $this->eddardGetAttachments( $post_id , $filter );
        $acount = count( $attachments );
        
        $htm = $this->eddardCreateAttachmentHtml( $attachments, $filter );
        
        echo json_encode( array(
            'status' => true,
            'msg'    => "",
            'htm'    => $htm,
            'count'  => $acount
        ) );
        exit();
    }
    
    /**
     * Ajax method to remove the selected post attachment from the current post
     * @return void
     * @access public
     * @since 1.0
    **/
    public function eddardRemoveAttachment() {
        
        $image_id = ( true == isset( $_POST['eddard_image_id'] ) ) ? $_POST['eddard_image_id'] : 0;
        
        if( true == empty( $image_id ) ) {
            echo json_encode( array(
                'state' => false,
                'msg'   => __( "Cannot remove image! Attachment image id is empty.", self::EDDARD_TDOMAIN ),
            ) );
            exit();
        }
        
        if( false == $this->eddardTheTakeOver( $image_id ) ) {
            echo json_encode( array(
                'state' => false,
                'msg'   => __( "Cannot remove image!", self::EDDARD_TDOMAIN ),
            ) );
            exit();
        }
        
        echo json_encode( array(
            'state' => true,
            'msg'   => __( "Selected image removed from post successfully!", self::EDDARD_TDOMAIN ),
        ) );
        exit();
    }
    
    
    
    public function eddardTakeOverAttachmentImage() {
        
        $post_id = ( true == isset( $_POST['post_id'] ) && false == empty( $_POST['post_id'] ) ) ? $_POST['post_id'] : null;
        if( true == empty( $post_id ) ) {
            echo json_encode( array(
                'state' => false,
                'msg'    => __( "Cannot take over image! Post ID is empty.", self::EDDARD_TDOMAIN ),
            ) );
            exit();
        }
        
        $image_id = ( true == isset( $_POST['eddard_image_id'] ) ) ? $_POST['eddard_image_id'] : 0;
        if( true == empty( $image_id ) ) {
            echo json_encode( array(
                'state' => false,
                'msg'   => __( "Cannot take over image! Attachment image id is empty.", self::EDDARD_TDOMAIN ),
            ) );
            exit();
        }
        
        $result = $this->eddardTheTakeOver( $image_id, $post_id );
        
        if( true == $result ) {
            echo json_encode( array(
                'state' => true,
                'msg'   => __( "Attachment successfully moved!", self::EDDARD_TDOMAIN ),
            ) );
            exit();
        }
        
        echo json_encode( array(
            'state' => false,
            'msg'   => __( "Attachment cannot be moved!", self::EDDARD_TDOMAIN ),
        ) );
        exit();
    }
    
    /**
     * Attach the selected attachment specified by $image_id to the current post specified by $post_id
     * @param int $image_id - the attachment ID
     * @param int $post_id - the post ID the image will be attached to. If not set or empty, the attachment
     * will be removed fromk its current owner anyway
     * @param bool $not_so_hard - if set to true, then in case of empty post id, the attachment won't be
     * removed from its owner
     * @return bool
     * @access pivate
     * @since 1.0
    **/
    private function eddardTheTakeOver( $image_id=null, $post_id=null, $not_so_hard=false ) {
        
        if( true == empty( $image_id ) ) {
            return false;
        }
        
        if( true == empty( $post_id ) ) {
            $post_id = 0;
        }
        
        if( 0 == $post_id && true == $not_so_hard ) {
            return false;
        }
        
        $result = wp_update_post( array(
            'ID' => $image_id,
            'post_parent' => $post_id,
        ) );
        
        if( 0 != $result ) {
            return true;
        }
        
        return false;
    }
    
}

?>