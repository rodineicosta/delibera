<?php

/**
 * Based on http://www.foxrunsoftware.net/articles/wordpress/add-custom-bulk-action/
 * 
 */


// PHP 5.3 and later:
namespace CTLT_WP_Side_Comments\Admin;

class BulkPrint
{
	public function __construct()
	{
		//Bulk actions
		
		add_action('admin_print_scripts', array( $this, 'admin_scripts'));
		
		add_action('load-edit.php',         array( $this, 'bulk_action'));
		//add_action('admin_notices',         array( $this, 'admin_notices'));
	}
	
	/**
	 * add Bulk Action to post list
	 */
	function admin_scripts()
	{
		global $post_type;
		
		$currentScreen = get_current_screen();
		
		if( $currentScreen->id == 'edit-page' && ( $post_type == 'post' || $post_type == 'page') )
		{
			wp_enqueue_script('ctlt-side-comments-bulk-print', CTLT_WP_SIDE_COMMENTS_PLUGIN_URL."/print/js/admin.js", array('jquery'), '1.0', true);
			wp_localize_script('ctlt-side-comments-bulk-print', 'ctlt_bulk_print', array('actions' => 
				array(
					'print' => array( 'label' => __('Print', 'wp-side-comments') ),
					'export' => array( 'label' => __('CSV by paragraph', 'wp-side-comments') ),
					'export_day' => array( 'label' => __('CSV by day', 'wp-side-comments') ),
					'export_user' => array( 'label' => __('CSV by user', 'wp-side-comments') ),
				)
			));
	   	}
	}
			
			
	/**
	 * handle the Bulk Action
	 * 
	 * Based on the post http://wordpress.stackexchange.com/questions/29822/custom-bulk-action
	 */
	function bulk_action()
	{
		global $typenow;
		$post_type = $typenow;
		
		if($post_type == 'post' || $post_type == 'page')
		{
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
			$action = $wp_list_table->current_action();
			
			$allowed_actions = array('print', "export", 'export_day', 'export_user');
			if(!in_array($action, $allowed_actions)) return;
			
			// security check
			check_admin_referer('bulk-posts');
			
			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
			if(isset($_REQUEST['post'])) {
				$post_ids = array_map('intval', $_REQUEST['post']);
			}
			
			if(empty($post_ids)) return;
			
			global $wp_query;
			
			switch($action)
			{
				case 'export':
					$wp_query = new \WP_Query( array(
						'post__in' => $post_ids,
						'orderby' => 'title',
						'order' => 'ASC',
						'post_type' => $post_type,
						'wp_side_comments_print_csv' => 1,
					));
				break;
				case 'export_day':
					$wp_query = new \WP_Query( array(
						'post__in' => $post_ids,
						'orderby' => 'title',
						'order' => 'ASC',
						'post_type' => $post_type,
						'wp_side_comments_print_csv' => 2,
					));
				break;
				case 'export_user':
					$wp_query = new \WP_Query( array(
						'post__in' => $post_ids,
						'orderby' => 'title',
						'order' => 'ASC',
						'post_type' => $post_type,
						'wp_side_comments_print_csv' => 3,
					));
				break;
				case 'print':
					$wp_query = new \WP_Query( array(
							'post__in' => $post_ids,
							'orderby' => 'title',
							'order' => 'ASC',
							'post_type' => $post_type,
					));
				break;
				default: return;
			}
			
			include(CTLT_WP_SIDE_COMMENTS_PLUGIN_PATH .'print/print.php');
			exit();
		}
	}
	
}

$BulkPrint = new \CTLT_WP_Side_Comments\Admin\BulkPrint();