<?php

use Delibera\Includes\SideComments\CTLT_WP_Side_Comments;

/**
 * Gera um arquivo XLS com as opiniões e propostas de
 * encaminhamento feitos pelos usuários nas pautas
 */

// contorna problema com links simbolicos no ambiente de desenvolvimento
$wp_root = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../../';

$is_direct_call = substr($_SERVER['SCRIPT_FILENAME'], - strlen('delibera_relatorio_xls.php')) == 'delibera_relatorio_xls.php';

if($is_direct_call)
{
	require_once($wp_root . 'wp-load.php');
}

if (!current_user_can('manage_options')) {
    die('Você não deveria estar aqui');
}

$pautas_query = false;

if($is_direct_call)
{
	$query_args = array(
		'post_type' => 'pauta',
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	
	$pautas_query = new WP_Query($query_args);
}
else
{
	global $wp_query;
	$pautas_query = $wp_query;
}

$comments = array();
$sessions = array();

/* @var $pauta WP_POST */

if($pautas_query->have_posts())
{
	global $post;
	while ($pautas_query->have_posts())
	{
		$pautas_query->the_post();
		$pauta = $post;	
		
		$situacao = delibera_get_situacao($pauta->ID);
		$comment_fake = new stdClass();
		$comment_fake->comment_date = $pauta->post_date;
		$comment_fake->pauta_title = get_the_title($pauta->ID);
		$comment_fake->pauta_status = $situacao->name;
		$comment_fake->type = 'Pauta';
		$comment_fake->link = get_permalink($pauta);
		$comment_fake->comment_post_ID = $pauta->ID;
		$comment_fake->concordaram = (int) get_post_meta($pauta->ID, 'delibera_numero_curtir', true);
		$comment_fake->discordaram = (int) get_post_meta($pauta->ID, 'delibera_numero_discordar', true);
		$comment_fake->votes_count = (int) get_post_meta($pauta->ID, "delibera_numero_comments_votos", true);
		$comment_fake->comment_author = get_the_author();
		$comment_fake->comment_author_email = get_the_author_meta('email', $pauta->post_author);
		$comment_fake->comment_content = get_the_content();
		$temas =  wp_get_object_terms($pauta->ID, 'tema', array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'names'));
		$comment_fake->temas = is_array($temas) ? implode(', ', $temas) : '';
		$tags = wp_get_object_terms($pauta->ID, 'post_tag', array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'names'));
		$comment_fake->tags = is_array($tags) ? implode(', ',  $tags) : '';
		$cats = wp_get_object_terms($pauta->ID, 'category', array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'names'));
		$comment_fake->cats = is_array($cats) ? implode(', ',  $cats) : '';
		$comment_fake->delibera_dates = \Delibera\Flow::getDeadlineDates($pauta->ID);
		
		$pauta_sessions = class_exists('\Delibera\Includes\SideComments\CTLT_WP_Side_Comments') ? \Delibera\Includes\SideComments\CTLT_WP_Side_Comments::getPostSectionsList($pauta->ID) : array();
		$sessions[$pauta->ID] = $pauta_sessions;
		
		$comment_fake->session = count($pauta_sessions);
		
		$comment_tmp = delibera_get_comments($pauta->ID);
	    $comments = array_merge(
	        $comments,
	    	array($comment_fake),
	        $comment_tmp
	    );
	    
	}
	$comments_dates = array();
	foreach ($comments as $key => $comment) //TODO with this get bigger, we will have memory problem, better read pauta, comments and write, read next...
	{
		$comments_dates[strtotime($comment->comment_date)] = $key;
		if($comment->type == 'Pauta') continue;
		
		$situacao_pauta = delibera_get_situacao($comment->comment_post_ID);
		$situacao_comment = delibera_get_comment_situacao($comment->comment_ID);
		$situacao_name = is_object($situacao_comment) ? $situacao_comment->name : $situacao_pauta->name;
		
	    $comment->pauta_title = get_the_title($comment->comment_post_ID);
	    $comment->pauta_status = $situacao_name;
	    $comment->type = delibera_get_comment_type_label($comment, false, false);
	    $comment->link = get_comment_link($comment);
	    $comment->concordaram = (int) get_comment_meta($comment->comment_ID, 'delibera_numero_curtir', true);
	    $comment->discordaram = (int) get_comment_meta($comment->comment_ID, 'delibera_numero_discordar', true);
	    $comment->votes_count = (int) get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true);
	    $comment->session = get_comment_meta( $comment->comment_ID, 'side-comment-section', true );
	    $comment->temas = '';
	    $comment->tags = '';
	    $comment->cats = '';
	}
	ksort($comments_dates);
}

header('Pragma: public');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
header('Content-Disposition: attachment; filename='.date('Ymd_His').'_'.__('relatorio', 'delibera').'.xls');

ob_start();

$situacoes = get_terms('situacao', array('hide_empty' => false));
?>
<table>
    <tr>
    	<td><?php _e("Data", 'delibera'); ?></td>
		<td><?php _e("Título da Pauta", 'delibera'); ?></td>
		<td><?php _e("Situação", 'delibera'); ?></td>
		<td><?php _e("Nome do Autor", 'delibera'); ?></td>
		<td><?php _e("E-mail", 'delibera'); ?></td>
		<td><?php _e("Tipo (Pauta ou tipo de comentário)", 'delibera'); ?></td>
		<td><?php _e("Sessão/Parágrafo", 'delibera'); ?></td>
		<td><?php _e("Conteúdo", 'delibera'); ?></td>
		<td><?php _e("Link", 'delibera'); ?></td>
		<td><?php _e("Concordaram", 'delibera'); ?></td>
		<td><?php _e("Discordaram", 'delibera'); ?></td>
		<td><?php _e("Votos", 'delibera'); ?></td>
		<td><?php _e("Tema(as)", 'delibera'); ?></td>
		<td><?php _e("Palavra(as) chave", 'delibera'); ?></td>
		<td><?php _e("Categoria(as)", 'delibera'); ?></td>
		<td><?php _e("Fluxo", 'delibera'); ?></td>
		<?php
			foreach ($situacoes as $situacao)
			{
				/** @var $situacao WP_Term **/ 
				?>
				<td><?php echo $situacao->name; ?></td><?php
			}
		?>
    </tr><?php
    echo utf8_decode(ob_get_clean());
    foreach ($comments_dates as $comment_index) :
	    ob_start();
    	$comment = $comments[$comment_index];
    	?>
	    <tr>
	    	<td><?php echo $comment->comment_date; ?></td>
	        <td><?php echo $comment->pauta_title; ?></td>
	        <td><?php echo $comment->pauta_status; ?></td>
	        <td><?php echo $comment->comment_author; ?></td>
	        <td><?php echo $comment->comment_author_email; ?></td>
	        <td><?php echo $comment->type; ?></td>
	        <td><?php 
	        	if(empty($comment->session))
	        	{
	        		_e('Geral', 'delibera');
	        	}
	        	elseif(array_key_exists($comment->session, $sessions[$comment->comment_post_ID]))
	        	{
	        		echo wp_trim_words(strip_tags($sessions[$comment->comment_post_ID][$comment->session]), 5, ' ...');
	        	}
	        	else
	        	{
	        		echo $comment->session;
	        	}?>
	        </td>
	        <td><?php //echo $comment->comment_content;
	        	echo wp_trim_words(strip_tags($comment->comment_content), 5, ' ...')
	        ?></td>
	        <td><?php echo $comment->link; ?></td>
	        <td><?php echo $comment->concordaram; ?></td>
	        <td><?php echo $comment->discordaram; ?></td>
	        <td><?php echo $comment->votes_count; ?></td>
	        <td><?php echo $comment->temas; ?></td>
	        <td><?php echo $comment->tags; ?></td>
	        <td><?php echo $comment->cats; ?></td><?php
	        if(isset($comment->delibera_dates))
	        {?>
	        	<td><?php echo implode(',', array_keys($comment->delibera_dates)); ?></td><?php
	        	
	        	foreach ($situacoes as $situacao)
				{
					/** @var $situacao WP_Term **/
					if (array_key_exists($situacao->slug, $comment->delibera_dates))
					{?>
						<td><?php echo $comment->delibera_dates[$situacao->slug] == -1 ? '' : $comment->delibera_dates[$situacao->slug]; ?></td><?php
					}
					else
					{?>
						<td></td><?php
					}
				}
	        }?>
	    </tr><?php
	    echo utf8_decode(ob_get_clean());
	endforeach; ?>
</table>
