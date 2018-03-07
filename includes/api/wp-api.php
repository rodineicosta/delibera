<?php

// PHP 5.3 and later:
namespace Delibera\Includes;

class WpApi
{
	public function __construct()
	{
		add_action('rest_api_init', array($this, 'register_api_fields'));
		add_action( 'rest_api_init', function () {
			register_rest_route( 'wp/v2', '/pautas/(?P<id>\d+)/like', array(
				'methods' => \WP_REST_Server::EDITABLE,
				'callback' => array($this, 'likePauta'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route( 'wp/v2', '/pautas/(?P<id>\d+)/unlike', array(
				'methods' => \WP_REST_Server::EDITABLE,
				'callback' => array($this, 'unlikePauta'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/like', array(
				'methods' => \WP_REST_Server::EDITABLE,
				'callback' => array($this, 'like_comment_api'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/unlike', array(
				'methods' => \WP_REST_Server::EDITABLE,
				'callback' => array($this, 'unlike_comment_api'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/isLiked', array(
				'methods' => 'GET',
				'callback' => array($this, 'isPautaLiked'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/isUnliked', array(
				'methods' => 'GET',
				'callback' => array($this, 'isPautaUnliked'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/isLiked', array(
				'methods' => 'GET',
				'callback' => array($this, 'isCommentLiked'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/isUnliked', array(
				'methods' => 'GET',
				'callback' => array($this, 'isCommentUnliked'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/getLikes', array(
				'methods' => 'GET',
				'callback' => array($this, 'getPautaLikes'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/getUnlikes', array(
				'methods' => 'GET',
				'callback' => array($this, 'getPautaUnlikes'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/comments/(?P<id>\d+)/getLikes', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCommentLikes'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/comments/(?P<id>\d+)/getUnlikes', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCommentUnlikes'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/situacao', array(
				'methods' => 'GET',
				'callback' => array($this, 'getPautaSituacao'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/interactions', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCommentCount'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/getCommentList', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCommentList'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('wp/v2', '/pautas/(?P<id>\d+)/getCommentListHtml', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCommentListHtml'),
				
				'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				)
			) );
			register_rest_route('/wp/v2', '/pauta/metas', array(
				'methods' => 'GET',
				'callback' => array($this, 'getMetas'),
			) );
		} );
		
		add_action('rest_insert_pauta', array($this, 'apiCreatePauta'), 10 , 3);
		add_filter('rest_pre_insert_pauta', array($this, 'apiPreInsertPauta'), 10, 2);
		
		add_action('rest_insert_comment', array($this, 'apiCreateComment'), 10 , 3);
		add_filter('rest_pre_insert_comment', array($this, 'apiPreInsertComment'), 10, 2);
		
		add_action( 'generate_rewrite_rules', array( &$this, 'rewrite_rules' ), 10, 1 );
		add_filter( 'query_vars', array( &$this, 'rewrite_rules_query_vars' ) );
		add_filter( 'template_include', array( &$this, 'rewrite_rule_template_include' ) );
		
		//add_action('rest_insert_pauta', array($this, 'rest_insert_pauta'));
		
	}
	
	function register_api_fields()
	{
		register_rest_field('pauta', 'situacao', 
				array(
					'get_callback' => array($this, 'slug_get_situacao'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'user_name',
				array(
					'get_callback' =>  array($this, 'api_user_name'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'avatar',
				array(
					'get_callback' =>  array($this, 'api_avatar'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'likes',
				array(
					'get_callback' =>  array($this, 'getPautaLikes'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'unlikes',
				array(
					'get_callback' =>  array($this, 'getPautaUnlikes'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'liked',
				array(
					'get_callback' =>  array($this, 'isPautaLiked'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'unliked',
				array(
					'get_callback' =>  array($this, 'isPautaUnliked'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('comment', 'liked',
				array(
					'get_callback' =>  array($this, 'isCommentLiked'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('comment', 'unliked',
				array(
					'get_callback' =>  array($this, 'isCommentUnliked'),
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'interactions',
				array(
					'get_callback' =>  array($this, 'getCommentCount'),
					'update_callback' => null,
					'schema' => null
				));
		
	}
	
	/**
	 * Get the value of the "situação" field
	 *
	 * @param array $object
	 *        	Details of current .
	 * @param string $field_name
	 *        	Name of field.
	 * @param \WP_REST_Request $request
	 *        	Current request
	 *        	
	 * @return mixed
	 */
	function slug_get_situacao($object, $field_name, $request)
	{
		return delibera_get_situacao( $object[ 'id' ] )->slug;
	}
	
	/**
	 * Get the value of the "user_name" field
	 *
	 * @param array $object
	 *        	Details of current .
	 * @param string $field_name
	 *        	Name of field.
	 * @param \WP_REST_Request $request
	 *        	Current request
	 *
	 * @return mixed
	 */
	function api_user_name($object, $field_name, $request)
	{
		$pauta = get_post($object['id']);
		$user = get_author_name($pauta->post_author);
		return $user;
	}
	
	/**
	 * Get the avatar URL
	 *
	 * @param array $object
	 *        	Details of current .
	 * @param string $field_name
	 *        	Name of field.
	 * @param \WP_REST_Request $request
	 *        	Current request
	 *
	 * @return mixed
	 */
	function api_avatar($object, $field_name, $request)
	{
		$pauta = get_post($object['id']);
		$avatar = get_avatar_url($pauta->post_author);
		return $avatar;
	}
	
	function likePauta($data)
	{
		if(is_object($data))
		{
			return delibera_curtir($data->get_param('id'));
		}
		return "ops, need id";
	}
	
	function unlikePauta($data)
	{
		if(is_object($data))
		{
			return delibera_discordar($data->get_param('id'));
		}
		return "ops, need id";
	}
	
	function like_comment_api($data)
	{
		if(is_object($data))
		{
			return delibera_curtir($data->get_param('id'), 'comment');
		}
		return "ops, need id";
	}
	
	function unlike_comment_api($data)
	{
		if(is_object($data))
		{
			return delibera_discordar($data->get_param('id'), 'comment');
		}
		return "ops, need id";
	}
	
	/**
	 *
	 * @param \WP_Post $post
	 * @param \WP_REST_Request $request
	 * @param bool $creating
	 */
	function apiCreatePauta($post, $request, $creating)
	{
		$args = $request->get_params();
		$args['post_id'] = $post->ID;
		
		\deliberaCreateTopic($args);
		return $post;
	}
	
	/**
	 *
	 * @param \WP_Post $prepared_post
	 * @param \WP_REST_Request $request
	 */
	function apiPreInsertPauta($prepared_post, $request)
	{
		if(empty($prepared_post->post_name))
		{
			$prepared_post->post_name = sanitize_title($prepared_post->post_title);
		}
		return $prepared_post;
	}
	
	/**
	 *
	 * @param \WP_Comment $comment
	 * @param \WP_REST_Request $request
	 * @param bool $creating
	 */
	function apiCreateComment($comment, $request, $creating)
	{
		
	}
	
	/**
	 * 
	 * @param \WP_Comment $prepared_comment
	 * @param \WP_REST_Request $request
	 * @return \WP_Comment
	 */
	function apiPreInsertComment($prepared_comment, $request)
	{
		$type = $request->get_param('delibera_comment_tipo');
		if(!is_null($type))
		{
			$_POST['delibera_comment_tipo'] = $type;
		}
		$delibera_encaminha = $request->get_param('delibera_encaminha');
		if(!is_null($delibera_encaminha))
		{
			$_POST['delibera_encaminha'] = $delibera_encaminha;
		}
		$delibera_validacao = $request->get_param('delibera_validacao');
		if(!is_null($delibera_validacao))
		{
			$_POST['delibera_validacao'] = $delibera_validacao;
		}
		$delibera_baseouseem = $request->get_param('delibera_baseouseem');
		if(!is_null($delibera_baseouseem))
		{
			$_POST['delibera-baseouseem'] = $delibera_baseouseem;
		}
		$delibera_pair = $request->get_param('delibera_pair');
		if(!is_null($delibera_pair))
		{
			$_POST['delibera-pair'] = $delibera_pair;
		}
		
		return $prepared_comment;
	}
	
	////// Callback de login
	
	function rewrite_rules( &$wp_rewrite )
	{
		$new_rules = array(
			'delibera-api-login-callback/?' => "index.php?delibera_api_callback=1",
		);
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}
	
	function rewrite_rules_query_vars( $public_query_vars )
	{
		$public_query_vars[] = "delibera_api_callback";
		return $public_query_vars;
	}
	
	function rewrite_rule_template_include( $template )
	{
		global $wp_query;
		if ( $wp_query->get( 'delibera_api_callback' ) ) {
			// Retorno após fazer autenticação via oauth utilizando a API
			// ver método handle_callback_redirect() da classe WP_REST_OAuth1_UI do plaugin Rest Oauth
			//wp_logout();
			?>
			<script type="text/javascript">
			<!--
				function CloseMySelf(sender) {
				    try {
				    	window.opener.postMessage(window.location.href, '*');
				    }
				    catch (err) {}
				    window.close();
				    return false;
				}
				window.onload = function() {
					CloseMySelf(window);
				};
			//-->
			</script>
			<?php
			die;
		}
		return $template;
	}
	
	function isPautaLiked($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		return delibera_ja_curtiu($id, $user_id, $ip, 'pauta');
	}
	
	function isPautaUnliked($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		return delibera_ja_discordou($id, $user_id, $ip, 'pauta');
	}
	
	function isCommentLiked($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		return delibera_ja_curtiu($id, $user_id, $ip, 'comment');
	}
	
	function isCommentUnliked($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		return delibera_ja_discordou($id, $user_id, $ip, 'comment');
	}
	
	function getPautaLikes($data, $field_name = '', $request = null)
	{
		if(is_object($data))
		{
			return delibera_numero_curtir($data->get_param('id'), 'pauta');
		}
		if(is_array($data))
		{
			return delibera_numero_curtir($data['id'], 'pauta');
		}
		return "ops, need id";
	}
	
	function getPautaUnlikes($data, $field_name = '', $request = null)
	{
		if(is_object($data))
		{
			return delibera_numero_discordar($data->get_param('id'), 'pauta');
		}
		if(is_array($data))
		{
			return delibera_numero_discordar($data['id'], 'pauta');
		}
		return "ops, need id";
	}
	
	function getCommentLikes($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		return delibera_numero_curtir($id, 'comment');
	}
	
	function getCommentUnlikes($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		return delibera_numero_discordar($id, 'comment');
	}
	
	function getPautaSituacao($data)
	{
		if(is_object($data))
		{
			$situacao = delibera_get_situacao($data->get_param('id'));
			if( is_object($situacao) )
			{
				return $situacao->slug;
			}
			return '';
		}
		return "ops, need id";
	}
	
	function getCommentCount($data, $field_name = '', $request = null)
	{
		$id = false;
		if(is_object($data))
		{
			$id = $data->get_param('id');
		}
		elseif(is_array($data))
		{
			$id = $data['id'];
		}
		else
		{
			return "ops, need id";
		}
		
		return delibera_comment_number_filtro(0, $id);
	}
	
	function getCommentList($data)
	{
		if(is_object($data))
		{
			$post_id = $data->get_param('id');
			
			$args = array(
				'post_id' => $post_id,
			);
			$comments = get_comments($args);
			return delibera_get_comments_filter($comments);
		}
		return "ops, need id";
	}
	
	function getCommentListHtml($data)
	{
		if(is_object($data))
		{
			$post_id = $data->get_param('id');
			global $wp_query;
			
			$args = array(
				'p'         => $post_id, // ID of a page, post, or custom type
				'post_type' => 'pauta'
			);
			$wp_query = new \WP_Query($args);
			
			ob_start();
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					$comments = get_comments();
					$comments = delibera_get_comments_filter($comments);
					delibera_wp_list_comments(array(), $comments);
				}
			}
			
			$html = ob_get_contents();
			ob_end_clean();
			return array('html' => $html);
		}
		return "ops, need id";
	}
	
	function getMetas($data = array())
	{
		return \delibera_get_metas();
	}
	
}

global $DeliberaApi;
$DeliberaApi= new \Delibera\Includes\WpApi();
