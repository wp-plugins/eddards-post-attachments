<?php
/**
 * Plugin Name: Eddard's Post Attachments
 * Version: 1.0
 * Author: Eddard
 * Description: Eddard's post attachment plugin makes post attachement handling slightly easier and help you don't loose your head.
 * License: GPLv2 or later
Copyright 2015 Eddard <eddard.wp@gmail.com>
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/
@include_once( 'eddard-post.class.php' );

if( true == is_admin() ) {
    
    if( true == class_exists( 'EddardPost' ) ) {
    
        $EddardPostAttachments = new EddardPost();
        add_action( 'init', array( &$EddardPostAttachments, 'eddardLocalizePlugin' ) );
        add_action( 'admin_enqueue_scripts', array( &$EddardPostAttachments, 'eddardAdminEnqueue' ) );
        add_action( 'init', array( &$EddardPostAttachments, 'eddardMceRegisterPlugin' ) );
        add_action( 'edit_form_after_title', array( &$EddardPostAttachments, 'eddardMceWorkingField' ) );
        
        add_action( 'wp_ajax_eddardRefreshList', array( &$EddardPostAttachments, 'eddardRefreshList' ) );
        add_action( 'wp_ajax_eddardRemoveAttachment', array( &$EddardPostAttachments, 'eddardRemoveAttachment' ) );
        add_action( 'wp_ajax_eddardTakeOverAttachmentImage', array( &$EddardPostAttachments , 'eddardTakeOverAttachmentImage' ) );
    } 
        
}


?>