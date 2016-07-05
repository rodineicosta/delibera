<?php
/*
Template Name: Author Page
*/
get_header();
$id = $wp_query->query_vars['delibera_author'];
$user = get_user_by( 'id' , deliberaEncryptor('decrypt',$id) );

$per_page = isset( $_GET['per-page'] ) ?	esc_html( $_GET['per-page'] ) : '20' ;
$search = isset( $_GET['search'] ) ?	esc_html( $_GET['search'] ) : '' ;
$order	= isset( $_GET['order-by'] ) ?	esc_html( $_GET['order-by'] ) : '' ;

global $user_display;
?>
<div id="container">
	<div id="main-content" role="main">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div class="delibera-autor-page">
					<div class="entry-author">
						<?php echo get_avatar( $user->ID, 85 ); ?>
						<span class="author-name">
							<?php echo $user->display_name; ?>
						</span>
						<span class="author-position">
							<?php get_user_meta($user->ID, 'conselho', true); ?>
						</span>
						<span class="author-bio">
							<?php echo $user->description; ?>
						</span>
					</div><!-- .entry-author -->
					<div class="comments-col">
						<div class="comments-list">
							<span class="comments-list-title"><?php _e('Comments'); ?></span>
							<?php
								$comments = get_comments(array('user_id' => $user->ID));
								
								/* @var $comment WP_Comment */
								foreach ($comments as $comment)
								{?>
									<div class="entry-comment">
										<div class="entry-date">
											<span class="comment-em"><?php _e('em', 'delibera'); ?></span>
											<span class="comment-date"><?php echo mysql2date( get_option( 'date_format' ), $comment->comment_date ); ?></span>
										</div>
										<div class="comment-post-title">
											<?php echo get_the_title($comment->comment_post_ID); ?>
										</div>
										<span class="comment-label"><?php _e('Comment'); ?></span>
										<div class="comment-content">
											<?php echo $comment->comment_content; ?>
										</div>
									</div><?php
								}
							?>
						</div>
					</div>
				</div><!-- .delibera-autor-page -->
			</main><!-- #main -->
		</div>
	</div><!-- #content -->
</div><!-- #container -->
<?php
get_footer();
?>
</body>
</html>
