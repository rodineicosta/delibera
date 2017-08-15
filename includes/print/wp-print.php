<?php
/*
Plugin Name: WP-Print
Plugin URI: http://lesterchan.net/portfolio/programming/php/
Description: Displays a printable version of your WordPress blog's post/page.
Version: 2.50
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net
*/


/*  
	Copyright 2009  Lester Chan  (email : lesterchan@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//PHP 5.3 and later:
namespace Delibera\Includes\WP_Print;

### Function: Print Public Variables
add_filter('query_vars', '\Delibera\Includes\WP_Print\print_variables');
function print_variables($public_query_vars) {
	$public_query_vars[] = 'delibera_print';
	$public_query_vars[] = 'delibera_printpage';
    $public_query_vars[] = 'number-options';
    $public_query_vars[] = 'delibera_print_csv';
    $public_query_vars[] = 'delibera_print_parent';
    $public_query_vars[] = 'delibera_print_xls';
	return $public_query_vars;
}

function removeAddtoanyLinks()
{
	remove_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
	remove_filter( 'the_excerpt', 'A2A_SHARE_SAVE_add_to_content', 98 );
}

### Function: Load WP-Print
function delibera_print()
{
	removeAddtoanyLinks();
	if(
		intval(get_query_var('delibera_print')) == 1 ||
		intval(get_query_var('delibera_printpage')) == 1 ||
		intval(get_query_var('delibera_print_csv')) > 0 ||
		intval(get_query_var('delibera_print_xls')) > 0
	)
	{
		global $wp_query;
		
		if(intval(get_query_var('delibera_print_parent')) == 1)
		{
			$post = get_post();
			
			$current_post_id = get_the_ID();
			
			global $parent;
			$parent = $post->post_parent;
			if( $parent == 0 )
			{
				$parent = $current_post_id;
			}
			
			add_filter( 'posts_where' , '\Delibera\Includes\WP_Print\delibera_print_posts_where' );
			
			//$pages = get_pages( array( 'parent' => $parent, 'sort_column' => 'title', 'sort_order' => 'asc', 'number' => '6' ) );
			$wp_query = new \WP_Query( array(
				'post_parent' => $parent,
				'orderby' => 'title',
				'order' => 'ASC',
				'post_type' => get_post_type(),
				'post_status' => 'publish',
				'include' => $parent,
				'delibera_print_csv' => get_query_var('delibera_print_csv', false),
				'delibera_print_xls' => get_query_var('delibera_print_xls', false),
			));
		}
		
		//TODO FIX posts_per_page
		/*$args = array_merge( $wp_query->query_vars, array( 'post_type' => 'product' ) );
		 query_posts( $args );
		 
		 $wp_query->set('posts_per_page', get_query_var('number-options'));
		 query_posts($wp_query->query_vars);*/
		
		include(plugin_dir_path(__FILE__) .'/print.php');
		exit();
	}
}
add_action('template_redirect', '\Delibera\Includes\WP_Print\delibera_print', 5);

### Function: Print Content
function print_content($display = true) {
	global $links_text, $link_number, $max_link_number, $matched_links,  $pages, $multipage, $numpages, $post;
	if (!isset($matched_links)) {
		$matched_links = array();
	}
	if(!empty($post->post_password) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password) {
		$content = get_the_password_form();
	} else {
		if($multipage) {
			for($page = 0; $page < $numpages; $page++) {
				$content .= $pages[$page];
			}
		} else {
			$content = $pages[0];
		}
		if(function_exists('email_rewrite')) {
			remove_shortcode('donotemail');
			add_shortcode('donotemail', 'email_donotemail_shortcode2');
		}
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		if(!print_can('images')) {
			$content = remove_image($content);
		}
		if(!print_can('videos')) {
			$content = remove_video($content);
		}
		if(print_can('links')) {
			preg_match_all('/<a(.+?)href=[\"\'](.+?)[\"\'](.*?)>(.+?)<\/a>/', $content, $matches);
			for ($i=0; $i < count($matches[0]); $i++) {
				$link_match = $matches[0][$i];
				$link_url = $matches[2][$i];
				if(stristr($link_url, 'https://')) {
					 $link_url =(strtolower(substr($link_url,0,8)) != 'https://') ?get_option('home') . $link_url : $link_url;
				} else if( stristr($link_url, 'mailto:')) {
					$link_url =(strtolower(substr($link_url,0,7)) != 'mailto:') ?get_option('home') . $link_url : $link_url;
				} else if( $link_url[0] == '#' ) {
					$link_url = $link_url; 
				} else {
					$link_url =(strtolower(substr($link_url,0,7)) != 'http://') ?get_option('home') . $link_url : $link_url;
				}
				$link_text = $matches[4][$i];
				
				if (!isset($link_text) && isset($link_url)) {
					$link_text = $link_url;
				}
				
				$new_link = true;
				$link_url_hash = md5($link_url);
				if (!isset($matched_links[$link_url_hash])) {
					$link_number = ++$max_link_number;
					$matched_links[$link_url_hash] = $link_number;
				} else {
					$new_link = false;
					$link_number = $matched_links[$link_url_hash];
				}
				$content = str_replace_one($link_match, "<a href=\"$link_url\" rel=\"external\">".$link_text.'</a> <sup>['.number_format_i18n($link_number).']</sup>', $content);
				if ($new_link) {
					if(preg_match('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/',$link_text)) {
						$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.__('Image', 'wp-print').': <b><span dir="ltr">'.$link_url.'</span></b></p>';
					} else {
						$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.$link_text.': <b><span dir="ltr">'.$link_url.'</span></b></p>';
					}
				}
			}
		}
	}
	if($display) {
		echo $content;
	} else {
		return $content;
	}
}


### Function: Print Categories
function print_categories($before = '', $after = '', $parents = '')
{
	
	$temp_cat = strip_tags(get_the_category_list(',', $parents));
	$temp_cat = explode(', ', $temp_cat);
	$temp_cat = implode($after.__(',', 'wp-print').' '.$before, $temp_cat);
	echo $before.$temp_cat.$after;
}


### Function: Print Comments Content
function print_comments_content($display = true) {
	global $links_text, $link_number, $max_link_number, $matched_links;
	if (!isset($matched_links)) {
		$matched_links = array();
	}
	$content  = get_comment_text();
	$content = apply_filters('comment_text', $content, get_comment());
	if(!print_can('images')) {
		$content = remove_image($content);
	}
	if(!print_can('videos')) {
		$content = remove_video($content);
	}
	if(print_can('links')) {
		preg_match_all('/<a(.+?)href=[\"\'](.+?)[\"\'](.*?)>(.+?)<\/a>/', $content, $matches);
		for ($i=0; $i < count($matches[0]); $i++) {
			$link_match = $matches[0][$i];
			$link_url = $matches[2][$i];
			if(stristr($link_url, 'https://')) {
				 $link_url =(strtolower(substr($link_url,0,8)) != 'https://') ?get_option('home') . $link_url : $link_url;
			} else if(stristr($link_url, 'mailto:')) {
				$link_url =(strtolower(substr($link_url,0,7)) != 'mailto:') ?get_option('home') . $link_url : $link_url;
			} else if($link_url[0] == '#') {
				$link_url = $link_url; 
			} else {
				$link_url =(strtolower(substr($link_url,0,7)) != 'http://') ?get_option('home') . $link_url : $link_url;
			}
			$link_text = $matches[4][$i];
			if (!isset($link_text) && isset($link_url)) {
				$link_text = $link_url;
			}
			$new_link = true;
			$link_url_hash = md5($link_url);
			if (!isset($matched_links[$link_url_hash])) {
				$link_number = ++$max_link_number;
				$matched_links[$link_url_hash] = $link_number;
			} else {
				$new_link = false;
				$link_number = $matched_links[$link_url_hash];
			}
			$content = str_replace_one($link_match, "<a href=\"$link_url\" rel=\"external\">".$link_text.'</a> <sup>['.number_format_i18n($link_number).']</sup>', $content);
			if ($new_link) {
				if(preg_match('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/',$link_text)) {
					$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.__('Image', 'wp-print').': <b><span dir="ltr">'.$link_url.'</span></b></p>';
				} else {
					$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.$link_text.': <b><span dir="ltr">'.$link_url.'</span></b></p>';
				}
			}
		}
	}
	if($display) {
		echo $content;
	} else {
		return $content;
	}
}

function delibera_comment_number($postID, $filter)
{
	return 0;
}

### Function: Print Comments
function print_comments_number($comments = false)
{
	global $post;
	$comment_text = '';
	$comment_status = $post->comment_status;
	if($comment_status == 'open' || (is_array($comments) && count($comments) > 0 ))
	{
		$num_comments = 0;
		if($comments)
		{
			$num_comments = count($comments);
		}
		else
		{
			$num_comments = delibera_comment_number($post->ID, 'todos');
		}
		if($num_comments == 0) {
			$comment_text = __('Sem Interações', 'delibera');
		} else {
			$comment_text = sprintf(_n('%s Interação', '%s Interações', $num_comments, 'delibera'), number_format_i18n($num_comments));
		}
	} else {
		$comment_text = __('Interações Desativadas', 'delibera');
	}
	if(!empty($post->post_password) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password) {
		_e('Interações Escondidas', 'delibera');
	} else {
		echo $comment_text;
	}
}


### Function: Print Links
function print_links($text_links = '') {
	global $links_text;
	if(empty($text_links)) {
		$text_links = __('URLs na pauta:', 'wp-print');
	}
	if(!empty($links_text)) { 
		echo $text_links.$links_text; 
	}
}

### Function: Add Print Comments Template
function print_template_comments($file = '') {
	if(file_exists(TEMPLATEPATH.'/print-comments.php')) {
		$file = TEMPLATEPATH.'/print-comments.php';
	} else {
		$file = plugin_dir_path(__FILE__).'print-comments.php';
	}
	return $file;
}

### Function: Print Page Title
function print_pagetitle($page_title) {
	$page_title .= ' &raquo; '.__('Print', 'wp-print');
	return $page_title;
}


### Function: Can Print?
function print_can($type) {
	
	return true;
}


### Function: Remove Image From Text
function remove_image($content) {
	//$content= preg_replace('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/', '',$content);
	return $content;
}


### Function: Remove Video From Text
function remove_video($content) {
	$content= preg_replace('/<object[^>]*?>.*?<\/object>/', '',$content);
	$content= preg_replace('/<embed[^>]*?>.*?<\/embed>/', '',$content);
	return $content;
}


### Function: Replace One Time Only
function str_replace_one($search, $replace, $content){
	if ($pos = strpos($content, $search)) {
		return substr($content, 0, $pos).$replace.substr($content, $pos+strlen($search));
	} else {
		return $content;
	}
}

function delibera_get_print_link($texto = false, $imagem = false)
{
	if($texto == false) $texto = __("imprimir", 'delibera');
	$server = $_SERVER['SERVER_NAME'];
	$endereco = $_SERVER ['REQUEST_URI'];
	$url = "http".(array_key_exists('HTTPS', $_SERVER))."://".$server.$endereco;
	$e = strpos($url, '?') !== false ? '&' : '?';
	$html = '';
	if($imagem !== false)
	{
		$html = '
			<a href="'.$url.$e.'delibera_print=1" class="delibera-print-link"><img class="delibera-print-link-img" src="'.TEMPLATEPATH.DIRECTORY_SEPARATOR.$imagem.'" alternative="'.$texto.'" /></a>
		';
	}
	elseif($texto !== false && $imagem === false)
	{
		$html = '
			<a href="'.$url.$e.'delibera_print=1" class="delibera-print-link"><span class="delibera-print-link-label" >'.$texto.'</span></a>
		';
	}
	return $html;
}

function delibera_manage_posts_columns($columns)
{
	return array_merge( $columns,
			array( 'delibera_print' => __( 'Print', 'delibera' ) ) );
}
add_filter( 'manage_posts_columns' , '\Delibera\Includes\WP_Print\delibera_manage_posts_columns' );
add_filter( 'manage_pages_columns' , '\Delibera\Includes\WP_Print\delibera_manage_posts_columns' );

function delibera_display_posts_print( $column, $post_id )
{
	if ($column == 'delibera_print')
	{
		$link = get_the_permalink($post_id);
		$has_query_vars = strpos($link, '?') === false ? '?' : '&';
		echo '<a href="'.get_the_permalink($post_id).$has_query_vars.'delibera_print_xls=1&delibera_print_parent=1" target="_blank" title="'.__('Exportar XLS com dados da pauta','delibera').'" ><span class="delibera-icon-file-excel" ></span></a>';
		echo '<a href="'.get_the_permalink($post_id).$has_query_vars.'delibera_print=1&delibera_print_parent=1" target="_blank" title="'.__('Imprimir textos com comentários por parágrafo.','delibera').'" ><span class="delibera-icon-print" onclick="" ></span></a>';
		echo '<a href="'.get_the_permalink($post_id).$has_query_vars.'delibera_print_csv=1&delibera_print_parent=1" target="_blank" title="'.__('Exportar CSV com número de comentários por parágrafo','delibera').'" ><span class="delibera-icon-chat-empty" ></span></a>';
		echo '<a href="'.get_the_permalink($post_id).$has_query_vars.'delibera_print_csv=2&delibera_print_parent=1" target="_blank" title="'.__('Exportar CSV com número de comentários por dia','delibera').'" ><span class="delibera-icon-calendar" ></span></a>';
		echo '<a href="'.get_the_permalink($post_id).$has_query_vars.'delibera_print_csv=3&delibera_print_parent=1" target="_blank" title="'.__('Exportar CSV com número de comentários por usuário','delibera').'" ><span class="delibera-icon-users" ></span></a>';
	}
}
add_action( 'manage_posts_custom_column' , '\Delibera\Includes\WP_Print\delibera_display_posts_print', 10, 2 );
add_action( 'manage_pages_custom_column' , '\Delibera\Includes\WP_Print\delibera_display_posts_print', 10, 2 );

function delibera_scripts()
{
	//wp_enqueue_style( 'delibera-print-fonts', plugin_dir_url(__FILE__).'/fonts/css/delibera-print.css' );
	wp_enqueue_style( 'delibera-print', plugin_dir_url(__FILE__).'/admin.css' );
}
add_action( 'admin_enqueue_scripts', '\Delibera\Includes\WP_Print\delibera_scripts' );

function delibera_print_posts_where( $where )
{
	global $parent;
	
	$where = " AND ( ID = ".$parent." OR ".substr($where, 4).")";
	
	return $where;
}

/**
 * Get meta data by meta object type
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, term, or user).
 * @return object|false Meta object or false.
 */
function delibera_getMetadataByType( $meta_type = 'post' )
{
	global $wpdb;
	
	if ( ! $meta_type ) {
		return false;
	}
	
	$table = _get_meta_table( $meta_type );
	if ( ! $table ) {
		return false;
	}
	
	$meta = $wpdb->get_col( " SELECT DISTINCT meta_key FROM $table WHERE meta_key NOT LIKE '\_%' ");
	
	if ( empty( $meta ) )
	{
		return false;
	}
	
	if($meta_type == 'user')
	{
		$meta = array_filter($meta, function($meta)
		{
			global $wpdb;
			$defaults_metas = array(
				'nickname',
				'first_name',
				'last_name',
				'description',
				'rich_editing',
				'comment_shortcuts',
				'admin_color',
				'use_ssl',
				'show_admin_bar_front',
				$wpdb->prefix.'capabilities',
				$wpdb->prefix.'user_level',
				'dismissed_wp_pointers',
				'show_welcome_panel',
				'session_tokens',
				$wpdb->prefix.'dashboard_quick_press_last_post_id',
				'closedpostboxes_page',
				'metaboxhidden_page'
			);
			return !in_array($meta, $defaults_metas);
		});
	}
	
	return $meta;
}

require dirname(__FILE__)."/print-bulk.php";

?>
