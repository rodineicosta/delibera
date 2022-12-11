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

//PHP 5.3 and later:
namespace Delibera\Includes\WP_Print;

use Delibera\Includes\SideComments\CTLT_WP_Side_Comments as CTLT_WP_Side_Comments;

if(intval(get_query_var('delibera_print_csv')) > 0)
{
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.date('Ymd').'_delibera_report.csv');

	$output = fopen('php://output', 'w');

	if (have_posts())
	{
		$allcomments = array();

		switch (intval(get_query_var('delibera_print_csv')))
		{
			case 1: //num de comentários por parágrafo
			default:
				fputcsv($output, array(__('Parágrafos', 'delibera'), __('Número de cometários', 'delibera'), __('Autores', 'delibera')), ';');
				while (have_posts())
				{
					the_post();

					$paragraphs = CTLT_WP_Side_Comments::getPostSectionsList(get_the_ID());

					global $CTLT_WP_Side_Comments;
					if(!is_object($CTLT_WP_Side_Comments))
					{
						$CTLT_WP_Side_Comments = new CTLT_WP_Side_Comments();
					}

					$sidecomments = $CTLT_WP_Side_Comments->getCommentsPerSection(get_the_ID(), ARRAY_A);
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
							$authors[] = $comment['comment_author'];
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
			case 2: //num de comentários por dia
				fputcsv($output, array(__('Data', 'delibera'), __('Número de comentários', 'delibera')), ';');
				while (have_posts())
				{
					the_post();

					$getCommentArgs = array(
						'post_id' => get_the_ID(),
						'status' => 'approve'
					);
					$comments = get_comments( $getCommentArgs );

					$comments = delibera_comments_filter_portipo($comments, array('discussao', 'encaminhamento', 'resolucao'));

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
			case 3: //num de comentários por usuário

				while (have_posts())
				{
					the_post();

					$getCommentArgs = array(
							'post_id' => get_the_ID(),
							'status' => 'approve'
					);
					$comments = get_comments( $getCommentArgs );

					$comments = delibera_comments_filter_portipo($comments, array('discussao', 'encaminhamento', 'resolucao'));

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
							//$metas = get_user_meta($user_id, '', true); //TODO create option to show a defined user metas
							$metas = array();
							if(is_array($metas))
							{
								$metas = array_diff_key($metas, $defaults_metas);
								foreach ($metas as $key => $value)
								{
									if( substr($key, 0, 1) == '_' ) unset($metas[$key]);
								}

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

				$header = array(__('Usuário', 'delibera'), __('Número de cometários', 'delibera'));
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

if(intval(get_query_var('delibera_print_xls')) > 0)
{
	if (have_posts())
	{
		switch (intval(get_query_var('delibera_print_xls')))
		{
			case 1:
				require WP_PLUGIN_DIR.'/delibera/delibera_relatorio_xls.php';
			break;
		}
	}
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
		<link rel="stylesheet" href="<?php echo plugins_url('delibera/includes/print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if('rtl' == $text_direction): ?>
		<?php if(@file_exists(TEMPLATEPATH.'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('delibera/includes/print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
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
					<p id="BlogDate"><?php _e('Postado por', 'delibera'); ?> <u><?php the_author(); ?></u> <?php _e('em', 'delibera'); ?> <?php the_time(sprintf(__('%s @ %s', 'delibera'), get_option('date_format'), get_option('time_format'))); ?> <?php _e('na', 'delibera'); ?> <?php print_categories('<u>', '</u>'); ?> | <u><a href='#comments_controls'><?php print_comments_number(); ?></a></u></p>
					<div id="BlogContent"><?php
						print_content();?>
						<hr class="Divider" style="text-align: center;" /><?php
						if(
							class_exists('\Delibera\Includes\SideComments\CTLT_WP_Side_Comments') &&
							CTLT_WP_Side_Comments::hasSideCommentSection(get_the_ID()))
						{
							$sidecomments_print = true;
							$paragraphs = CTLT_WP_Side_Comments::getPostSectionsList(get_the_ID());

							$sidecomments = CTLT_WP_Side_Comments::getCommentsPerSection(get_the_ID());

							if(is_array($sidecomments) && array_key_exists('comments', $sidecomments) && is_array($sidecomments['comments']))
							{
								$sidecomments = $sidecomments['comments'];
							}
							else
							{
								$sidecomments = array();
							}
							for($i = 1; $i <= count($paragraphs); $i++)
							{
								echo sprintf( '<p class="commentable-section" data-section-id="%d">', $i).$paragraphs[$i];
								if(array_key_exists($i, $sidecomments))
								{
									$comments = $sidecomments[$i];
									$comment_template = print_template_comments();
									require $comment_template;
								}
								?>
								<hr class="Divider" style="text-align: center;" /><?php
							}
							$sidecomments_print = false;
						}
						?>
					</div>
			<hr class="Divider" style="text-align: center;" />
			<?php if(print_can('comments')): ?>
				<?php comments_template(); ?>
			<?php endif; ?>
			<p><?php _e('Pauta impressa de', 'delibera'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
			<p><?php _e('URL da pauta', 'delibera'); ?>: <strong dir="ltr"><?php the_permalink(); ?></strong></p>
			<?php if(print_can('links')): ?>
				<p><?php print_links(); ?></p>
			<?php endif;
			endwhile; ?>
			<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link"><?php _e('Click', 'delibera'); ?> <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click aqui para imprimir.', 'delibera'); ?>"><?php _e('aqui', 'delibera'); ?></a> <?php _e('para imprimir.', 'delibera'); ?></p>
		<?php else: ?>
				<p><?php _e('Não há pautas relacionadas a esse critério.', 'delibera'); ?></p>
		<?php endif; ?>
	</div>
</div>
</body>
</html>