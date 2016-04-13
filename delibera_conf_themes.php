<?php
/**
 * Inicializa gerenciamento dos temas do plugin.
 */

/**
 * Controla os distintos temas do Delibera disponíveis.
 *
 * Os temas do Delibera podem ser salvos em dois lugares distintos.
 * Na pasta themes dentro da pasta do plugin. Cada tema deve estar dentro
 * de uma sub pasta cujo o nome é o nome do tema. Um tema do Delibera
 * também pode ser salvo dentro de uma pasta chamada delibera dentro
 * do tema atual do Wordpress.
 * @package Tema
 */
class DeliberaThemes
{
//     /**
//      * Diretório onde ficam os temas
//      * dentro do plugin
//      * @var string
//      */
//     public $baseDir;

//     /**
//      * URL do diretório onde ficam
//      * os temas dentro do plugin
//      * @var string
//      */
//     public $baseUrl;

//     /**
//      * Caminho para o diretório
//      * do tema padrão
//      * @var string
//      */
//     public $defaultThemePath;

//     /**
//      * URL para o diretório do
//      * tema padrão
//      * @var string
//      */
//     public $defaultThemeUrl;

//     /**
//      * Caminho para o tema do Delibera
//      * dentro do tema atual do WP.
//      * @var string
//      */
//     public $wpThemePath;

//     /**
//      * Url para o diretório do tema do Delibera
//      * dentro do tema atual do WP
//      * @var string
//      */
//     public $wpThemeUrl;

//     /**
//      * Nome do tema atual do Wordpress
//      * @var string
//      */
//     public $wpThemeName;

    /**
     * Define variáveis obrigatórias para funcionamento correto
     */
    function __construct()
    {
    	add_filter('archive_template', array($this, 'archiveTemplate'));
    	add_filter('single_template', array($this, 'singleTemplate'));
    	add_action('admin_print_styles', array($this, 'adminPrintStyles'));
    	add_action('wp_enqueue_scripts', array($this, 'publicStyles'), 100);
    	
    	add_filter('comments_template', array($this, 'commentsTemplate'));
    	
    }

    /**
     * Retorna o diretório de um tema.
     * Se não um nome de tema for passado como
     * parâmetro, retorna o diretório do tema atual.
     *
     * @param string $themeName
     * @return string
     */
    public function getThemeDir($themeName = '')
    {
		return plugin_dir_path(__FILE__).'/themes/generic';
    }

    /**
     * Retorna a URL para o diretório
     * principal do tema atual.
     *
     * @return string
     */
    public function getThemeUrl()
    {
        return plugin_dir_url(__FILE__).'/themes/generic';
    }

    /**
     * Retorna o caminho no sistema de arquivos
     * para um arquivo no tema atual. Se o arquivo
     * não existir, retorna o caminho para o arquivo
     * no tema padrão.
     *
     * @param string $file_name
     * @return string
     */
    public function themeFilePath($fileName)
    {
        $filePath = $this->getThemeDir() . '/' . $fileName;

        if (file_exists($filePath))
        {
            return $filePath;
        }
        return false;
    }

    /**
     * Retorna a url para um arquivo no tema atual.
     * Se o arquivo não existir, retorna o caminho
     * para o arquivo no tema padrão.
     *
     * @param string $file_name
     * @return string
     */
    public function themeFileUrl($fileName)
    {
        $filePath = $this->getThemeDir() . '/' . $fileName;

        if (file_exists($filePath)) {
            return $this->getThemeUrl() . '/' . $fileName;
        }
        return false;
    }

    /**
     * Inclui os arquivos do tema relacionados com
     * a listagem de pautas e retorna o template
     * a ser usado.
     *
     * @param string $archiveTemplate
     * @return string
     */
    public function archiveTemplate($archiveTemplate)
    {
        global $post;

        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta'))
        {
        	if(file_exists(get_stylesheet_directory()."/archive-pauta.php"))
        	{
        		$archive_template = get_stylesheet_directory()."/archive-pauta.php";
        	}
        	else
        	{
        		$archiveTemplate = $this->themeFilePath('archive-pauta.php');
        	}
        }

        return $archiveTemplate;
    }

    /**
     * Inclui os arquivos do tema relacionados com
     * a página de uma pauta e retorna o template
     * a ser usado.
     *
     * @param string $singleTemplate
     * @return string
     */
    public function singleTemplate($singleTemplate)
    {
        global $post;

        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta'))
        {
        	if(file_exists(get_stylesheet_directory()."/single-pauta.php"))
        	{
        		$singleTemplate = get_stylesheet_directory()."/single-pauta.php";
        	}
        	else
        	{
        		$singleTemplate = $this->themeFilePath('single-pauta.php');
        	}
        }

        return $singleTemplate;
    }

    /**
     * Inclui os arquivos CSS
     *
     * @return null
     */
    public function publicStyles()
    {
        global $post, $wp_query;

        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta') || $wp_query->get('tpl') === 'nova-pauta')
        {
        	if(file_exists(get_stylesheet_directory()."/delibera_style.css"))
        	{
        		wp_enqueue_style('delibera_style', get_stylesheet_directory_uri()."/delibera_style.css");
        	}
        	else
        	{
        		wp_enqueue_style('delibera_style', $this->themeFileUrl('delibera_style.css'));
        	}
        }
    }

    /**
     * Adiciona o CSS do admin conforme o
     * tema.
     *
     * @return null
     */
    public function adminPrintStyles()
    {
        wp_enqueue_style('delibera_admin_style', $this->themeFileUrl('delibera_admin.css'));
    }

    /**
     * Carrega o arquivo de template do loop
     * de pautas para o tema atual. Se o arquivo
     * não existir usa o arquivo do tema padrão.
     *
     * @return null
     */
    public function archiveLoop()
    {
    	if(file_exists(get_stylesheet_directory()."/loop-pauta.php"))
    	{
    		load_template(get_stylesheet_directory()."/loop-pauta.php");
    	}
    	else
    	{
       		load_template($this->themeFilePath('delibera-loop-archive.php'), true);
    	}
    }

//     /**
//      * Retorna um array com os temas disponíveis.
//      *
//      * @return array
//      */
//     public function getAvailableThemes()
//     {
//         $themes = array();
//         $dirs = glob($this->baseDir . '*', GLOB_ONLYDIR);

//         foreach ($dirs as $dir) {
//             $themes[$dir] = basename($dir);
//         }

//         // adiciona o tema do delibera de dentro do tema atual do wp se um existir
//         if (file_exists($this->wpThemePath)) {
//             $themes[$this->wpThemePath] = $this->wpThemeName;
//         }

//         return $themes;
//     }

//     /**
//      * Gera o select box com os temas disponíveis
//      * para a interface de admin do Delibera.
//      *
//      * @param string $currentTheme o tema atual
//      * @return string
//      */
//     public function getSelectBox($currentTheme)
//     {
//         $themes = $this->getAvailableThemes();

//         $html = "<select name='theme' id='theme'>";

//         foreach ($themes as $themePath => $themeName) {
//             $html .= "<option value='{$themePath}'" . selected($themePath, $currentTheme, false) . ">{$themeName}</option>";
//         }

//         $html .= "</select>";

//         return $html;
//     }

    /**
     * Usa o template de comentário do Delibera
     * no lugar do padrão do Wordpress para as pautas
     *
     * @param string $path
     * @return string
     * @package Tema
     */
    function commentsTemplate($path)
    {
    	global $deliberaThemes;
    
    	if (get_post_type() == 'pauta') {
    		return $deliberaThemes->themeFilePath('delibera_comments.php');
    	}
    
    	return $path;
    }
}

$deliberaThemes = new DeliberaThemes;

// inclui arquivos específicos do tema
require_once($deliberaThemes->themeFilePath('functions.php'));
require_once($deliberaThemes->themeFilePath('delibera_comments_template.php'));
