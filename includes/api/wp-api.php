<?php

// PHP 5.3 and later:
namespace Delibera\Includes;

class WpApi
{
	public function __construct()
	{
		add_action('rest_api_init', array($this, 'delibera_register_api_fields'));
		add_action( 'rest_api_init', function () {
			register_rest_route( 'wp/v2', '/pautas/(?P<id>\d+)/like', array(
				'methods' => 'POST',
				'callback' => array($this, 'delibera_like_pauta_api'),
			) );
			register_rest_route( 'wp/v2', '/pautas/(?P<id>\d+)/unlike', array(
				'methods' => 'POST',
				'callback' => array($this, 'delibera_unlike_pauta_api'),
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/like', array(
				'methods' => 'POST',
				'callback' => array($this, 'delibera_like_comment_api'),
			) );
			register_rest_route( 'wp/v2', '/comments/(?P<id>\d+)/unlike', array(
				'methods' => 'POST',
				'callback' => array($this, 'delibera_unlike_comment_api'),
			) );
		} );
		
		add_action('rest_insert_pauta', array($this, 'deliberaApiCreate', 10, 2));
		add_filter('rest_pre_insert_pauta', array($this, 'deliberaApiPreInsertPauta', 10, 2));
		
		add_action( 'generate_rewrite_rules', array( &$this, 'rewrite_rules' ), 10, 1 );
		add_filter( 'query_vars', array( &$this, 'rewrite_rules_query_vars' ) );
		add_filter( 'template_include', array( &$this, 'rewrite_rule_template_include' ) );
		
	}
	
	function delibera_register_api_fields()
	{
		register_rest_field('pauta', 'situacao', 
				array(
					'get_callback' => 'slug_get_situacao',
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'user_name',
				array(
					'get_callback' => 'delibera_api_user_name',
					'update_callback' => null,
					'schema' => null
				));
		register_rest_field('pauta', 'avatar',
				array(
					'get_callback' => 'delibera_api_avatar',
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
	 * @param WP_REST_Request $request
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
	 * @param WP_REST_Request $request
	 *        	Current request
	 *
	 * @return mixed
	 */
	function delibera_api_user_name($object, $field_name, $request)
	{
		$pauta = get_post($object['id']);
		$user = get_author_name($pauta->post_author);
		return $user;
	}
	
	/**
	 * Get the value of the "situação" field
	 *
	 * @param array $object
	 *        	Details of current .
	 * @param string $field_name
	 *        	Name of field.
	 * @param WP_REST_Request $request
	 *        	Current request
	 *
	 * @return mixed
	 */
	function delibera_api_avatar($object, $field_name, $request)
	{
		$pauta = get_post($object['id']);
		$avatar = get_avatar_url($pauta->post_author);
		return $avatar;
	}
	
	function delibera_like_pauta_api($data)
	{
		if(is_object($data))
		{
			return delibera_curtir($data->get_param('id'));
		}
		return "ops, need id";
	}
	
	function delibera_unlike_pauta_api($data)
	{
		if(is_object($data))
		{
			return delibera_discordar($data->get_param('id'));
		}
		return "ops, need id";
	}
	
	function delibera_like_comment_api($data)
	{
		if(is_object($data))
		{
			return delibera_curtir($data->get_param('id'), 'comment');
		}
		return "ops, need id";
	}
	
	function delibera_unlike_comment_api($data)
	{
		if(is_object($data))
		{
			return delibera_discordar($data->get_param('id'), 'comment');
		}
		return "ops, need id";
	}
	
	/**
	 *
	 * @param WP_Post $post
	 * @param WP_REST_Request $request
	 */
	function deliberaApiCreate($post, $request)
	{
		$args = $request->get_params();
		$args['post_id'] = $post->ID;
		
		deliberaCreateTopic($args);
		return $post;
	}
	
	/**
	 *
	 * @param WP_Post $prepared_post
	 * @param WP_REST_Request $request
	 */
	function deliberaApiPreInsertPauta($prepared_post, $request)
	{
		if(empty($prepared_post->post_name))
		{
			$prepared_post->post_name = sanitize_title($prepared_post->post_title);
		}
		return $prepared_post;
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
}

global $DeliberaApi;
$DeliberaApi= new \Delibera\Includes\WpApi();