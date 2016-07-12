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
								$comments = get_comments(array('user_id' => $user->ID, 'number' => 3));
								
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
						<div class="comment-view-all">
							<a class="link-view-all-comments" href="<?php echo get_site_url()?>/delibera/<?php echo deliberaEncryptor('encrypt', $user->ID); ?>/comentarios">
								<?php _e('Ver Todos' , 'delibera'); ?>
							</a>
						</div>
					</div> <!-- comments-col -->
					<div class="medias-col">
						<div class="medias-list">
							<span class="medias-list-title"><?php _e('Documentos'); ?></span>
							<?php
								$unsupported_mimes  = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
								$all_mimes          = get_allowed_mime_types();
								$accepted_mimes     = array_diff( $all_mimes, $unsupported_mimes );
								$args = array(
									'author'			=> $user->ID,
									'post_status'		=> 'publish',
									'posts_per_page'	=> 3,
									'post_type'			=> 'attachment',
									'paged'				=> get_query_var( 'paged' ),
									'post_mime_type'	=> $accepted_mimes
								);
								$medias = new WP_Query($args);
								
								/* @var $media WP_Post */
								if($medias->have_posts())
								while ($medias->have_posts())
								{
									$medias->the_post();
									global $post;
									$media = $post;
									?>
									<div class="entry-media">
										<div class="entry-date">
											<span class="media-cats"><?php echo get_the_category_list(); ?></span>
											<span class="media-date"><?php the_date() ?></span>
										</div>
										<div class="media-post-title">
											<?php the_title() ?>
										</div><?php
										if(is_user_logged_in()) // TODO delete file postback/callback
										{?>
											<div class="media-delete">
												<?php echo wp_get_attachment_link( get_the_ID(), '' , false, false, __("Delete"));  ?>
											</div><?php
										}?>
										<div class="media-download">
											<?php echo wp_get_attachment_link( get_the_ID(), '' , false, false, __("Download"));  ?>
										</div>
									</div><?php
								}
							?>
						</div>
						<div class="media-view-all">
							<a class="link-view-all-medias" href="<?php echo get_post_type_archive_link('attachment'); ?>">
								<?php _e('Ver Todos' , 'delibera'); ?>
							</a>
						</div>
					</div> <!-- medias-col -->
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
