<?php if ( have_posts() ) while ( have_posts() ) : the_post();
$status_pauta = delibera_get_situacao($post->ID)->slug;
//echo $status_pauta;
global $DeliberaFlow;
$flow = $DeliberaFlow->get(get_the_ID());
$current_module = $DeliberaFlow->getCurrentModule(get_the_ID());

$temas = wp_get_post_terms(get_the_ID(), 'tema');
?>
<div class="pauta-content <?php echo $status_pauta; ?>">

	<div class="banner-ciclo status-ciclo">
		<h3 class="title"><?php _e('Estágio da pauta', 'delibera'); ?></h3>
		<ul class="ciclos"><?php
		$i = 1;
		foreach ($flow as $situacao)
		{
			switch($situacao)
			{
				case 'validacao':
					if($status_pauta != "validacao")
					{?>
						<a href="<?php echo get_the_permalink()."?delibera-view-stage=validacao" ?>" class="delibera-ciclos-validacao-link" ><li class="validacao inactive"><?php echo $i; ?><br><?php _e('Validação', 'delibera'); ?></li></a><?php
					}
					else
					{?>
						<li class="validacao"><?php echo $i; ?><br><?php _e('Validação', 'delibera'); ?></li></a><?php
					}
				break;
				case 'discussao':
					if($status_pauta != "discussao")
					{?>
						<a href="<?php echo get_the_permalink()."?delibera-view-stage=discussao" ?>" class="delibera-ciclos-validacao-link" ><li class="discussao inactive"><?php echo $i; ?><br><?php _e('Discussão', 'delibera'); ?></li></a><?php
					}
					else
					{?>
						<li class="discussao"><?php echo $i; ?><br><?php _e('Discussão', 'delibera'); ?></li><?php
					}
				break;
				case 'relatoria':
				case 'eleicao_relator':
					if($status_pauta != "relatoria" && $status_pauta != "eleicao_relator" )
					{?>
						<a href="<?php echo get_the_permalink()."?delibera-view-stage=relatoria" ?>" class="delibera-ciclos-validacao-link" ><li class="relatoria inactive"><?php echo $i; ?><br><?php _e('Relatoria', 'delibera'); ?></li></a><?php
					}
					else
					{?>
						<li class="relatoria"><?php echo $i; ?><br><?php _e('Relatoria', 'delibera'); ?></li><?php
					}
				break;
				case 'emvotacao':
					if($status_pauta != "emvotacao")
					{?>
						<a href="<?php echo get_the_permalink()."?delibera-view-stage=emvotacao" ?>" class="delibera-ciclos-validacao-link" ><li class="emvotacao inactive"><?php echo $i; ?><br><?php _e('Votação', 'delibera'); ?></li></a><?php
					}
					else
					{?>
						<li class="emvotacao"><?php echo $i; ?><br><?php _e('Votação', 'delibera'); ?></li><?php
					}
				break;
				case 'naovalidada':
				case 'comresolucao':
					if($status_pauta != "naovalidada" && $status_pauta != "comresolucao" )
					{?>
						<a href="<?php echo get_the_permalink()."?delibera-view-stage=comresolucao" ?>" class="delibera-ciclos-validacao-link" ><li class="comresolucao inactive"><?php echo $i; ?><br><?php _e('Resolução', 'delibera'); ?></li></a><?php
					}
					else
					{?>
						<li class="comresolucao"><?php echo $i; ?><br><?php _e('Resolução', 'delibera'); ?></li><?php
					}
				break;
			}
			$i++;
		}?>
	</ul>
</div>



<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div id="leader">
		<?php if (!empty($temas)) : ?>
			<ul class="meta meta-temas">
				<li class="delibera-tema-entry-title"><?php _e('Tema(as)', 'delibera'); ?>:</li>
				<?php $size = count($temas) - 1; ?>
				<?php foreach ($temas as $key => $tema) : ?>
				<li>
					<a href="<?php echo get_term_link($tema);?>"><?php echo $tema->name; ?></a>
					<?php echo ($key != $size) ? ',' : ''; ?>
				</li>
				<?php endforeach; ?>
		    </ul>
		<?php endif; ?>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<div class="entry-prazo">

			<?php
			if ( \Delibera\Flow::getDeadlineDays( $post->ID ) <= -1 )
			_e( 'Prazo encerrado', 'delibera' );
			else
			printf( _n( 'Por mais <br><span class="numero">1</span> dia', 'Por mais <br><span class="numero">%1$s</span> dias', \Delibera\Flow::getDeadlineDays( $post->ID ), 'delibera' ), number_format_i18n( \Delibera\Flow::getDeadlineDays( $post->ID ) ) );
			?>
		</div><!-- .entry-prazo -->
		<!--div class="entry-meta">
		<div class="entry-situacao">
		<?php printf( __( 'Situação da pauta', 'delibera' ).': %s', delibera_get_situacao($post->ID)->name );?>
	</div .entry-situacao -->
	<div class="entry-blame">
		<div class="entry-author">
			<?php echo get_avatar( get_post(), 85 ); ?>
			<span class="author-text"><?php _e( 'Por', 'delibera' ); ?></span>
			<span class="author vcard">
				<a class="url fn n" href="<?php echo \Delibera\Member\MemberPath::getAuthorPautasUrl(get_the_author_meta( 'ID' )) ; ?>" title="<?php printf( __( 'Ver o perfil de %s', 'delibera' ), get_the_author() ); ?>">
					<?php the_author(); ?>
				</a>
			</span>
		</div><!-- .entry-author -->
		<div class="entry-date">
			<?php the_date(); ?>
			<div class="entry-time">
				<?php the_time(); ?>
			</div>
		</div>
	</div> <!-- entry-blame -->
	<div class="entry-seguir button">
		<?php echo delibera_gerar_seguir($post->ID); ?>
	</div>
	<div class="entry-print button">
		<a title="Versão simplificada para impressão" href="?delibera_print=1" class=""><i class="delibera-icon-print"></i></a>
	</div><!-- .entry-print -->

	<div class="entry-attachment">
	</div><!-- .entry-attachment -->
	
	</div><!-- #leader -->

	<div class="entry-content">
		<?php the_content(); ?>
	</div><!-- .entry-content -->

	<?php
	echo '<div id="delibera-comment-botoes-'.get_the_ID().'" class="delibera-comment-botoes">';
	echo '<div class="group-button-like">
	<!--span class="label">O que achou?</span-->';
	echo delibera_gerar_curtir($post->ID, 'pauta');
	echo delibera_gerar_discordar($post->ID, 'pauta');
	echo '</div>';
	social_buttons(get_permalink(), get_the_title());
	?>

</div>
</div><!-- #post-## -->
<h2 class="discussion-title"><?php echo $current_module->getCommentListLabel(); ?></h2>
	<?php
		comments_template( '', true );
		do_action('delibera-comments-list', $post);
	?>
</div>
<?php endwhile; // end of the loop. ?>
