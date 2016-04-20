<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Result extends \Delibera\Modules\ModuleBase
{
	/**
	 *
	 * @var array List of of topic status
	 */
	public $situacao = array('comresolucao');
	
	/**
	 *
	 * @var array list of module flows
	 */
	protected $flows = array('comresolucao');
	
	/**
	 *
	 * @var String Name of module deadline metadata
	 */
	protected $prazo_meta = '';
	
	/**
	 *
	 * @var array List of pair shotcode name => method
	 */
	protected $shortcodes = array('delibera_lista_de_resolucoes' => 'replaceResolucoes' );
	
	/**
	 * Module comments types
	 * @var array
	 */
	protected $comment_types = array('resolucao');

	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		if(term_exists('comresolucao', 'situacao', null) == false)
		{
			delibera_insert_term('Resolução', 'situacao', array(
					'description'=> 'Pauta com resoluções aprovadas',
					'slug' => 'comresolucao',
				),
				array(
					'qtrans_term_pt' => 'Resolução',
					'qtrans_term_en' => 'Resolution',
					'qtrans_term_es' => 'Resolución',
				)
			);
		}
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::initModule()
	 */
	public function initModule($post_id)
	{
		wp_set_object_terms($post_id, 'comresolucao', 'situacao', false);
	}

	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		return $rows;
	}
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	public function situationButtonText($situation)
	{
		if($situation == 'comresolucao')
		{
			return '';//__('', 'delibera');
		}
		
		return $situation;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::generateDeadline()
	 */
	public function generateDeadline($options_plugin_delibera)
	{
		//No deadline
		return false;
	}
	
	/**
	 * 
	 * Post Meta Fields display
	 * 
	 * @param \WP_Post $post
	 * @param array $custom post custom fields
	 * @param array $options_plugin_delibera Delibera options array
	 * @param WP_Term $situacao
	 * @param bool $disable_edicao
	 * 
	 */
	public function topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao)
	{
		
	}
	
	/**
	 * When the topic is published
	 * @param int $postID
	 * @param array $opt delibera configs
	 * @param bool $alterar has been altered
	 */
	public function publishPauta($postID, $opt)
	{
		
	}
	
	/**
	 * Validate topic required data 
	 * @param array $errors erros report array
	 * @param array $opt Delibera configs
	 * @param bool $autosave is autosave?
	 * @return array erros report array append if needed
	 */
	public function checkPostData($errors, $opt, $autosave)
	{
		
		return $errors;
	}
	
	/**
	 *
	 * Retorna pautas já resolvidas
	 * @param array $filtro
	 */
	public static function getResolucoes($filtro = array())
	{
		return self::getPautas($filtro);
	}
	
	/**
	 * Shortcut for list of Results
	 * @param array $matches regext results
	 * @return string
	 */
	public function replaceResolucoes($matches)
	{
		$temp = explode(',', $matches[1]); // configurações da shorttag
	    $count = count($temp);
	
	    $param = array(); // TODO Tratar Parametros
	
	    $html = $this->getResolucoes($param);
		$wp_posts = $html;
	    global $post;
	    $old = $post;
	    echo '<div id="lista-de-pautas">';
	    foreach ( $wp_posts as $wp_post )
	    {
			$post = $wp_post;
			include 'delibera_loop_pauta.php';
		}
		echo '</div>';
		$post = $old;
	
		return ''; // Retornar código da representação
	}
	
	/**
	 * Save topic metadata
	 * @param array $events_meta
	 * @param array $opt Delibera configs
	 * 
	 * @return array events_meta to be save on the topic
	 */
	public function savePostMetas($events_meta, $opt, $post_id = false)
	{
		return $events_meta;
	}
	
	/**
	 * Treat postback of frotend topic
	 * @param array $opt Delibera configs
	 */
	public function createPautaAtFront($opt)
	{
		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::deadline()
	 */
	public static function deadline($args)
	{
		$post_id = $args['post_ID'];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::getCommentTypeLabel()
	 */
	public function getCommentTypeLabel($tipo = false, $echo = true, $count = false)
	{
		if($count !== false)
		{
			if ($count == 0) {
				$label = __('Nenhuma resolução', 'delibera');
			} else if ($count == 1) {
				$label = __('1 resolução', 'delibera');
			} else {
				$label = sprintf(__('%d resoluções', 'delibera'), $count);
			}
			if($echo) echo $label;
			return $label;
		}
		else
		{
			if($echo)  _e('Resolução', 'delibera');
			return __('Resolução', 'delibera');
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::commentText()
	 */
	public function commentText($commentText, $post, $comment, $tipo)
	{
		$total = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_votos', true);
		$nvotos = get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true);
		$commentText = '
			<div id="delibera-comment-text-'.$comment->comment_ID.'" class="comentario_coluna1 delibera-comment-text">
				'.$commentText.'
			</div>
			<div class="comentario_coluna2 delibera-comment-text">
				'.$nvotos.($nvotos == 1 ? " ".__('Voto','delibera') : " ".__('Votos','delibera') ).
						'('.( $nvotos > 0 && $total > 0 ? (($nvotos*100)/$total) : 0).'%)
			</div>
		';
		return $commentText;
	}
	
}
$DeliberaResult = new \Delibera\Modules\Result();


