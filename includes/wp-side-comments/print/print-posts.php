<?php
/*
+----------------------------------------------------------------+
|																 |
|	WordPress 2.7 Plugin: WP-Print 2.50							 |
|	Copyright (c) 2008 Lester "GaMerZ" Chan						 |
|																 |
|	File Written By:											 |
|	- Lester "GaMerZ" Chan										 |
|	- http://lesterchan.net										 |
|																 |
|	File Information:											 |
|	- Printer Friendly Post/Page Template						 |
|	- wp-content/plugins/wp-print/print-posts.php				 |
|																 |
+----------------------------------------------------------------+
*/
if(intval(get_query_var('wp_side_comments_print_csv')) > 0)
{
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.date('Ymd').'_wp_side_comments_report.csv');
	
	$output = fopen('php://output', 'w');
	
	if (have_posts())
	{
		$allcomments = array();
			
		switch (intval(get_query_var('wp_side_comments_print_csv')))
		{
			case 1:
			default:
				fputcsv($output, array(__('Parágrafos', 'wp-side-comments'), __('Número de cometários', 'wp-side-comments'), __('Autores', 'wp-side-comments')), ';');
				while (have_posts())
				{
					the_post();
					$regex = '|(<p)[^>]*(>)|';
					$paragraphs = preg_split($regex, print_content(false));
					
					global $CTLT_WP_Side_Comments;
					if(!is_object($CTLT_WP_Side_Comments))
					{
						$CTLT_WP_Side_Comments = new CTLT_WP_Side_Comments();
					}
					
					$sidecomments = $CTLT_WP_Side_Comments->getCommentsData(get_the_ID());
					if(is_array($sidecomments) && array_key_exists('comments', $sidecomments) && is_array($sidecomments['comments']))
					{
						$sidecomments = $sidecomments['comments'];
					}
					else
					{
						$sidecomments = array();
					}
					
					for($i = 1; $i < count($paragraphs); $i++)
					{
						$authors = array();
						//echo print_r();
						$comments = array();
						if(array_key_exists($i, $sidecomments))
						{
							$comments = $sidecomments[$i];
						}
						foreach ($comments as $comment)
						{
							$authors[] = $comment['authorName'];
						}
						
						$authors = array_unique($authors);
						sort($authors);
						
						fputcsv($output , array(
								wp_trim_words(strip_tags($paragraphs[$i]), 5, ' ...'),
								count($comments),
								implode(', ', $authors) ,
						), ';');
					}
				}
			break;
			case 2:
				fputcsv($output, array(__('Data', 'wp-side-comments'), __('Número de cometários', 'wp-side-comments')), ';');
				while (have_posts())
				{
					the_post();
					
					$getCommentArgs = array(
						'post_id' => get_the_ID(),
						'status' => 'approve'
					);
					$comments = get_comments( $getCommentArgs );
					
					$allcomments = array_merge($allcomments, $comments);
				}
				$commentsDates = array();
				foreach ($allcomments as $comment)
				{
					$date = date('Y/m/d', strtotime($comment->comment_date));
					if(!array_key_exists($date, $commentsDates)) $commentsDates[$date] = 0;
					$commentsDates[$date] += 1;
				}
				ksort($commentsDates);
				foreach ($commentsDates as $key => $value)
				{
					fputcsv($output , array(
							$key,
							$value,
					), ';');
				}
			break;
			case 3:
				
				while (have_posts())
				{
					the_post();
						
					$getCommentArgs = array(
							'post_id' => get_the_ID(),
							'status' => 'approve'
					);
					$comments = get_comments( $getCommentArgs );
						
					$allcomments = array_merge($allcomments, $comments);
				}
				$commentsUsers = array();
				$allmetas = array();
				global $wpdb;
				$defaults_metas = array(
						'nickname' => false,
						'first_name' => false,
						'last_name' => false,
						'description' => false,
						'rich_editing' => false,
						'comment_shortcuts' => false,
						'admin_color' => false,
						'use_ssl' => false,
						'show_admin_bar_front' => false,
						$wpdb->prefix.'capabilities' => false,
						$wpdb->prefix.'user_level' => false,
						'dismissed_wp_pointers' => false,
						'show_welcome_panel' => false,
						'session_tokens' => false,
						$wpdb->prefix.'dashboard_quick_press_last_post_id' => false,
						'closedpostboxes_page' => false,
						'metaboxhidden_page' => false,
						$wpdb->prefix.'accept_the_terms_of_site' => false,
						'primary_blog' => false
						
				);
				foreach ($allcomments as $comment)
				{
					$user = get_comment_author($comment->comment_ID);
					$user_id = $comment->user_id;
					if(!array_key_exists($user, $commentsUsers))
					{
						$commentsUsers[$user] = array('count' => 0);
						if($user_id > 0)
						{
							$metas = get_user_meta($user_id, '', true);
							if(is_array($metas))
							{
								$metas = array_diff_key($metas, $defaults_metas);
							
								$allmetas = array_merge($allmetas, array_keys($metas));
								$commentsUsers[$user]['metas'] = $metas;
								continue;
							}
						}
						$commentsUsers[$user]['metas'] = array();
					}
					$commentsUsers[$user]['count'] += 1;
				}
				
				ksort($allmetas, SORT_NATURAL);
				
				$header = array(__('Usuário', 'wp-side-comments'), __('Número de cometários', 'wp-side-comments'));
				$header = array_merge($header, $allmetas);
				fputcsv($output, $header, ';');
				foreach ($commentsUsers as $key => $value)
				{
					$row = array(
						$key,
						$value['count'],
					);
					$index = 0;
					foreach ($header as $col)
					{
						if($index < 2)
						{
							$index++;
							continue;
						}
						if(array_key_exists($col, $value['metas']))
						{
							$row[] = $value['metas'][$col][0];
						}
						else 
						{
							$row[] = '';
						}
					}
					fputcsv($output , $row, ';');
				}
			break;
		}
	}
	//fclose($output);
	die();
}

global $text_direction; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="Robots" content="noindex, nofollow" />
	<?php if(@file_exists(TEMPLATEPATH.'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo plugins_url('wp-side-comments/print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if('rtl' == $text_direction): ?>
		<?php if(@file_exists(TEMPLATEPATH.'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('wp-side-comments/print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
		<?php endif; ?>
	<?php endif; ?>
</head>
<body>
<p style="text-align: center;"><strong>- <?php bloginfo('name'); ?> - <span dir="ltr"><?php bloginfo('url')?></span> -</strong></p>
<div class="Center">
	<div id="Outline">
		<?php if (have_posts()): ?>
			<?php while (have_posts()): the_post(); ?>
					<p id="BlogTitle"><?php the_title(); ?></p>
					<p id="BlogDate"><?php _e('Postado por', 'wp-side-comments'); ?> <u><?php the_author(); ?></u> <?php _e('em', 'wp-side-comments'); ?> <?php the_time(sprintf(__('%s @ %s', 'wp-side-comments'), get_option('date_format'), get_option('time_format'))); ?> <?php _e('na', 'wp-side-comments'); ?> <?php print_categories('<u>', '</u>'); ?> | <u><a href='#comments_controls'><?php print_comments_number(); ?></a></u></p>
					<div id="BlogContent"><?php 
						$regex = '|(<p)[^>]*(>)|';
						$paragraphs = preg_split($regex, print_content(false));
						
						global $CTLT_WP_Side_Comments;
						if(!is_object($CTLT_WP_Side_Comments))
						{
							$CTLT_WP_Side_Comments = new CTLT_WP_Side_Comments();
						}
						
						$sidecomments = $CTLT_WP_Side_Comments->getCommentsData(get_the_ID());
						if(is_array($sidecomments) && array_key_exists('comments', $sidecomments) && is_array($sidecomments['comments']))
						{
							$sidecomments = $sidecomments['comments'];
						}
						else 
						{
							$sidecomments = array();
						}
						
						for($i = 1; $i < count($paragraphs); $i++)
						{
							echo sprintf( '<p class="commentable-section" data-section-id="%d">', $i).$paragraphs[$i];
							//echo print_r();
							if(array_key_exists($i, $sidecomments))
							{
								$comments = $sidecomments[$i];
								$comment_template = print_template_comments();
								require $comment_template;
							}
							?>
							<hr class="Divider" style="text-align: center;" /><?php
						}
						
						?>
					</div>
			<?php if(print_can('comments')): ?>
				<?php //comments_template(); ?>
			<?php endif; ?>
			<p><?php _e('Postagem impressa de', 'wp-side-comments'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
			<p><?php _e('URL da Postagem', 'wp-side-comments'); ?>: <strong dir="ltr"><?php the_permalink(); ?></strong></p>
			<?php if(print_can('links')): ?>
				<p><?php print_links(); ?></p>
			<?php endif;
			endwhile; ?>
			<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link"><?php _e('Click', 'wp-side-comments'); ?> <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click aqui para imprimir.', 'wp-side-comments'); ?>"><?php _e('aqui', 'wp-side-comments'); ?></a> <?php _e('para imprimir.', 'wp-side-comments'); ?></p>
		<?php else: ?>
				<p><?php _e('Não há Postagems relacionadas a esse critério.', 'wp-side-comments'); ?></p>
		<?php endif; ?>
	</div>
</div>
</body>
</html>