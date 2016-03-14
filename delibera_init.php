<?php

function delibera_Add_custom_Post()
{
	$labels = array
	(
		'name' => __('Pautas','delibera'),
	    'singular_name' => __('Pauta','delibera'),
	    'add_new' => __('Adicionar Nova','delibera'),
	    'add_new_item' => __('Adicionar nova pauta ','delibera'),
	    'edit_item' => __('Editar Pauta','delibera'),
	    'new_item' => __('Nova Pauta','delibera'),
	    'view_item' => __('Visualizar Pauta','delibera'),
	    'search_items' => __('Procurar Pautas','delibera'),
	    'not_found' =>  __('Nenhuma Pauta localizada','delibera'),
	    'not_found_in_trash' => __('Nenhuma Pauta localizada na lixeira','delibera'),
	    'parent_item_colon' => '',
	    'menu_name' => __('Pautas','delibera')

	);

	$args = array
	(
		'label' => __('Pautas','delibera'),
		'labels' => $labels,
		'description' => __('Pauta de discussão','delibera'),
		'public' => true,
		'publicly_queryable' => true, // public
		//'exclude_from_search' => '', // public
		'show_ui' => true, // public
		'show_in_menu' => true,
		'menu_position' => 5,
		// 'menu_icon' => '',
		'capability_type' => array('pauta','pautas'),
		'map_meta_cap' => true,
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'author', 'excerpt', 'trackbacks', 'revisions', 'comments'),
		'register_meta_box_cb' => 'delibera_pauta_custom_meta', // função para chamar na edição
		'taxonomies' => array('post_tag'), // Taxionomias já existentes relaciondas, vamos criar e registrar na sequência
		'permalink_epmask' => 'EP_PERMALINK ',
		'has_archive' => true, // Opção de arquivamento por slug
		'rewrite' => true,
		'query_var' => true,
		'can_export' => true//, // veja abaixo
		//'show_in_nav_menus' => '', // public
		//'_builtin' => '', // Core
		//'_edit_link' => '' // Core

	);

	register_post_type("pauta", $args);
}

function delibera_Add_custom_taxonomy()
{
	$labels = array
	(
		'name' => __('Temas', 'delibera'),
	    'singular_name' => __('Tema', 'delibera'),
		'search_items' => __('Procurar por Temas','delibera'),
		'all_items' => __('Todos os Temas','delibera'),
		'parent_item' => __( 'Tema Pai','delibera'),
		'parent_item_colon' => __( 'Tema Pai:','delibera'),
		'edit_item' => __('Editar Tema','delibera'),
		'update_item' => __('Atualizar um Tema','delibera'),
		'add_new_item' => __('Adicionar Novo Tema','delibera'),
	    'add_new' => __('Adicionar Novo','delibera'),
	    'new_item_name' => __('Novo Tema','delibera'),
	    'view_item' => __('Visualizar Tema','delibera'),
	    'not_found' =>  __('Nenhum Tema localizado','delibera'),
	    'not_found_in_trash' => __('Nenhum Tema localizado na lixeira','delibera'),
	    'menu_name' => __('Temas','delibera')
	);

	$args = array
	(
		'label' => __('Temas','delibera'),
		'labels' => $labels,
		'public' => true,
		'capabilities' => array('assign_terms' => 'edit_pautas',
								'edit_terms' => 'edit_pautas'),
		//'show_in_nav_menus' => true, // Public
		// 'show_ui' => '', // Public
		'hierarchical' => true,
		//'update_count_callback' => '', //Contar objetos associados
		'rewrite' => true,
		//'query_var' => '',
		//'_builtin' => '' // Core
	);

	register_taxonomy('tema', array('pauta'), $args);



	$labels = array
	(
		'name' => __('Situações','delibera'),
	    'singular_name' => __('Situação', 'delibera'),
		'search_items' => __('Procurar por Situação','delibera'),
		'all_items' => __('Todas as Situações','delibera'),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __('Editar Situação','delibera'),
		'update_item' => __('Atualizar uma Situação','delibera'),
		'add_new_item' => __('Adicionar Nova Situação','delibera'),
	    'add_new' => __('Adicionar Nova', 'delibera'),
	    'new_item_name' => __('Nova Situação','delibera'),
	    'view_item' => __('Visualizar Situação','delibera'),
	    'not_found' =>  __('Nenhuma Situação localizado','delibera'),
	    'not_found_in_trash' => __('Nenhuma Situação localizada na lixeira','delibera'),
	    'menu_name' => __('Situações','delibera')
	);

	$args = array
	(
		'label' => __('Situações','delibera'),
		'labels' => $labels,
		'public' => false,
		'show_in_nav_menus' => true, // Public
		//'show_ui' => true, // Public
		'hierarchical' => false//,
		//'update_count_callback' => '', //Contar objetos associados
		//'rewrite' => '', //
		//'query_var' => '',
		//'_builtin' => '' // Core
	);

	register_taxonomy('situacao', array('pauta'), $args);

	// Se precisar trocar os nomes dos terms denovo
	/*$term = get_term_by('slug', 'comresolucao', 'situacao');
	wp_update_term($term->term_id, 'situacao', array('name' => 'Resolução'));
	$term = get_term_by('slug', 'emvotacao', 'situacao');
	wp_update_term($term->term_id, 'situacao', array('name' => 'Regime de Votação'));
	$term = get_term_by('slug', 'discussao', 'situacao');
	wp_update_term($term->term_id, 'situacao', array('name' => 'Pauta em discussão'));
	$term = get_term_by('slug', 'validacao', 'situacao');
	wp_update_term($term->term_id, 'situacao', array('name' => 'Proposta de Pauta'));
	$term = get_term_by('slug', 'naovalidada', 'situacao');
	wp_update_term($term->term_id, 'situacao', array('name' => 'Pauta Recusada'));*/

	$opt = delibera_get_config();

	if(taxonomy_exists('situacao'))
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
		if(term_exists('emvotacao', 'situacao', null) == false)
		{
			delibera_insert_term('Regime de Votação', 'situacao', array(
					'description'=> 'Pauta com encaminhamentos em Votacao',
					'slug' => 'emvotacao',
				),
				array(
					'qtrans_term_pt' => 'Regime de Votação',
					'qtrans_term_en' => 'Voting',
					'qtrans_term_es' => 'Sistema de Votación',
				)
			);
		}
		if(isset($opt['relatoria']) && $opt['relatoria'] == 'S')
		{
			if($opt['eleicao_relator'] == 'S')
			{
				if(term_exists('eleicaoredator', 'situacao', null) == false)
				{
					delibera_insert_term('Regime de Votação de Relator', 'situacao', array(
							'description'=> 'Pauta em Eleição de Relator',
							'slug' => 'eleicaoredator',
						),
						array(
							'qtrans_term_pt' => 'Regime de Votação de Relator',
							'qtrans_term_en' => 'Election of Rapporteur',
							'qtrans_term_es' => 'Elección del Relator',
						)
					);
				}
			}

			if(term_exists('relatoria', 'situacao', null) == false)
			{
				delibera_insert_term('Relatoria', 'situacao', array(
						'description'=> 'Pauta com encaminhamentos em Relatoria',
						'slug' => 'relatoria',
					),
					array(
						'qtrans_term_pt' => 'Relatoria',
						'qtrans_term_en' => 'Rapporteur',
						'qtrans_term_es' => 'Relator',
					)
				);
				}
		}
		if(term_exists('discussao', 'situacao', null) == false)
		{
			delibera_insert_term('Pauta em discussão', 'situacao', array(
					'description'=> 'Pauta em Discussão',
					'slug' => 'discussao',
				),
				array(
					'qtrans_term_pt' => 'Pauta em discussão',
					'qtrans_term_en' => 'Agenda en discusión',
					'qtrans_term_es' => 'Topic under discussion',
				)
			);
		}
		if(isset($opt['validacao']) && $opt['validacao'] == 'S')
		{
			if(term_exists('validacao', 'situacao', null) == false)
			{
				delibera_insert_term('Proposta de Pauta', 'situacao', array(
						'description'=> 'Pauta em Validação',
						'slug' => 'validacao',
					),
					array(
						'qtrans_term_pt' => 'Proposta de Pauta',
						'qtrans_term_en' => 'Proposed Topic',
						'qtrans_term_es' => 'Agenda Propuesta',
					)
				);
			}
			if(term_exists('naovalidada', 'situacao', null) == false)
			{
				delibera_insert_term('Pauta Recusada', 'situacao', array(
						'description'=> 'Pauta não Validação',
						'slug' => 'naovalidada',
					),
					array(
						'qtrans_term_pt' => 'Pauta Recusada',
						'qtrans_term_en' => 'Rejected Topic',
						'qtrans_term_es' => 'Agenda Rechazada',
					)
				);
			}
		}
	}

	if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'delibera_taxs.php'))
	{
		require_once __DIR__.DIRECTORY_SEPARATOR.'delibera_taxs.php';
	}

}


function delibera_init()
{
	add_action('admin_menu', 'delibera_config_menu');

	delibera_Add_custom_Post();

	delibera_Add_custom_taxonomy();

	global $delibera_comments_padrao;
	$delibera_comments_padrao = false;

}
add_action('init','delibera_init');

// Scripts

function delibera_scripts()
{
	global $post;

	if (is_pauta()) {
		wp_enqueue_script('jquery-expander', WP_CONTENT_URL.'/plugins/delibera/js/jquery.expander.js', array('jquery'));
		wp_enqueue_script('delibera', WP_CONTENT_URL.'/plugins/delibera/js/scripts.js', array('jquery-expander'));
		wp_enqueue_script('delibera-seguir', WP_CONTENT_URL . '/plugins/delibera/js/delibera_seguir.js', array('delibera'));
		wp_enqueue_script('delibera-concordar', WP_CONTENT_URL . '/plugins/delibera/js/delibera_concordar.js', array('delibera'));

		$situation = delibera_get_situacao($post->ID);

		$data = array(
			'post_id' => $post->ID,
			'ajax_url' => admin_url('admin-ajax.php'),
		);

		if (is_object($situation)) {
			$data['situation'] = $situation->slug;
		}

		wp_localize_script('delibera', 'delibera', $data);
	}
}
add_action( 'wp_print_scripts', 'delibera_scripts' );

function delibera_print_styles()
{
	if (is_pauta()) {
		wp_enqueue_style('jquery-ui-custom', plugins_url() . '/delibera/css/jquery-ui-1.9.2.custom.min.css');
	}

	wp_enqueue_style('delibera_style', WP_CONTENT_URL.'/plugins/delibera/css/delibera.css');
}
add_action('admin_print_styles', 'delibera_print_styles');

function delibera_admin_scripts()
{
	if(is_pauta())
	{
		wp_enqueue_script('jquery-ui-datepicker-ptbr', WP_CONTENT_URL.'/plugins/delibera/js/jquery.ui.datepicker-pt-BR.js', array('jquery-ui-datepicker'));
		wp_enqueue_script('delibera-admin',WP_CONTENT_URL.'/plugins/delibera/js/admin_scripts.js', array( 'jquery-ui-datepicker-ptbr'));
	}

	if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'delibera-notifications')
	{
		wp_enqueue_script('delibera-admin-notifica',WP_CONTENT_URL.'/plugins/delibera/js/admin_notifica_scripts.js', array('jquery'));
	}
}
add_action( 'admin_print_scripts', 'delibera_admin_scripts' );

// Fim Scripts

function delibera_footer() {

    echo '<div id="mensagem-confirma-voto" style="display:none;"><p>'.__('Sua contribuição foi registrada no sistema','delibera').'</p></div>';

}
add_action('wp_footer', 'delibera_footer');


function delibera_loaded() {
	// load plugin translations
	load_plugin_textdomain('delibera', false, dirname(plugin_basename( __FILE__ )).'/languages');
}
add_action('plugins_loaded','delibera_loaded');

$conf = delibera_get_config();
if(array_key_exists('plan_restriction', $conf) && $conf['plan_restriction'] == 'S')
{
	require_once __DIR__.DIRECTORY_SEPARATOR.'delibera_plan.php';
}

/*
 * Get page by slug
 */
function get_page_by_slug($page_slug, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;
	$page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s", $page_slug, $post_type ) );
	if ( $page )
		return get_page($page, $output);
	return null;
}

/**
 * Retorna a lista de idiomas disponível. Se o plugin
 * qtrans estiver habilitado retorna os idiomas dele, se
 * não usa o idioma definido no wp-config.php
 *
 * @return array
 */
function delibera_get_available_languages() {
    $langs = array(get_locale());

    if(function_exists('qtrans_enableLanguage'))
    {
        global $q_config;
        $langs = $q_config['enabled_languages'];
    }

    return $langs;
}

function delibera_config_menu()
{
	/*if (function_exists('add_menu_page'))
		add_menu_page( __('Delibera','delibera'), __('Delibera plugin','delibera'), 'manage_options', 'delibera-config', 'delibera_conf_page');*/

	$base_page = 'delibera-config';

	if (function_exists('add_menu_page'))
	{
		add_object_page( __('Delibera','delibera'), __('Delibera','delibera'), 'manage_options', $base_page, array(), WP_PLUGIN_URL."/delibera/images/delibera_icon.png");
		//add_submenu_page($base_page, __('Pesquisar Contatos','delibera'), __('Pesquisar Contatos','delibera'), 'manage_options', 'delibera-gerenciar', 'delibera_GerenciarContato' );
		//add_submenu_page($base_page, __('Criar Contato','delibera'), __('Criar Contato','delibera'), 'manage_options', 'delibera-criar', 'delibera_CriarContato' );
		//add_submenu_page($base_page, __('Importar Contatos','delibera'), __('Importar Contatos','delibera'), 'manage_options', 'delibera-importar', 'delibera_ImportarContato' );
		add_submenu_page($base_page, __('Configurações do Plugin','delibera'),__('Configurações do Plugin','delibera'), 'manage_options', 'delibera-config', 'delibera_conf_page');
		do_action('delibera_menu_itens', $base_page);
	}
}

/**
 *
 * Insere term no banco e atualizar línguas do qtranslate
 * @param string $label
 * @param string $tax Taxonomy
 * @param array $term EX: array('description'=> __('Español'),'slug' => 'espanol', 'slug' => 'espanol')
 * @param array $idiomas EX: array('qtrans_term_en' => 'United States of America', 'qtrans_term_pt' => 'Estados Unidos da América', 'qtrans_term_es' => 'Estados Unidos de América'
 */
function delibera_insert_term($label, $tax, $term, $idiomas = array())
{
	if(term_exists($term['slug'], $tax, null) == false)
	{
		wp_insert_term($label, $tax, $term);
		global $q_config;
		if(count($idiomas) > 0 && function_exists('qtrans_stripSlashesIfNecessary'))
		{
			if(isset($idiomas['qtrans_term_'.$q_config['default_language']]) && $idiomas['qtrans_term_'.$q_config['default_language']]!='')
			{
				$default = htmlspecialchars(qtrans_stripSlashesIfNecessary($idiomas['qtrans_term_'.$q_config['default_language']]), ENT_NOQUOTES);
				if(!isset($q_config['term_name'][$default]) || !is_array($q_config['term_name'][$default])) $q_config['term_name'][$default] = array();
				foreach($q_config['enabled_languages'] as $lang) {
					$idiomas['qtrans_term_'.$lang] = qtrans_stripSlashesIfNecessary($idiomas['qtrans_term_'.$lang]);
					if($idiomas['qtrans_term_'.$lang]!='') {
						$q_config['term_name'][$default][$lang] = htmlspecialchars($idiomas['qtrans_term_'.$lang], ENT_NOQUOTES);
					} else {
						$q_config['term_name'][$default][$lang] = $default;
					}
				}
				update_option('qtranslate_term_name',$q_config['term_name']);
			}
		}
	}
}

function delibera_convert_situacao_id_to_taxonomy_term_in_query(&$query)
{
	global $pagenow;
	$qv = &$query->query_vars;
	if (isset($qv['post_type']) &&
		$qv['post_type'] == 'pauta' &&
		$pagenow=='edit.php' &&
		isset($qv['situacao'])
	)
	{
		$situacao = get_term_by('id', $_REQUEST['situacao'], 'situacao');
		$qv['situacao'] = $situacao->slug;
	}
}
add_filter('parse_query','delibera_convert_situacao_id_to_taxonomy_term_in_query');

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'delibera_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function delibera_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		array(
			'name'      => 'mention-comments-authors',
			'slug'      => 'mention-comments-authors',
			'required'  => false
		),
		array(
			'name'      => 'comment-attachment',
			'slug'      => 'comment-attachment',
			'required'  => false
		),
    );

	$config = array(
		'id'           => 'delibera',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
    );

	tgmpa( $plugins, $config );
}