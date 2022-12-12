<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Vote extends \Delibera\Modules\ModuleBase
{

    /**
     * List of of topic status
     *
     * @var array
     */
    public $situacao = array('emvotacao');

    /**
     *
     * @var array list of module flows
     */
    protected $flows = array('emvotacao');

    /**
     * Name of module deadline metadata
     *
     * @var String
     */
    protected $prazo_meta = 'prazo_votacao';

    /**
     * Config days to make new deadline
     *
     * @var array
     */
    protected $days = array('dias_votacao');

    /**
     * Display priority
     *
     * @var int
     */
    public $priority = 4;

    public function __construct()
    {
        parent::__construct();
        add_action('admin_print_scripts', array($this, 'adminScripts'));
        add_action('wp_enqueue_scripts', array($this, 'js'));
        add_filter('delibera_unfilter_duplicate', array($this, 'unfilterDuplicate'));

        add_action('wp_ajax_delibera_vote_callback', array($this, 'voteCallback'));
        //add_action('wp_ajax_nopriv_delibera_vote_callback', array($this, 'voteCallback'));
    }

    /**
     * Register Tax for the module
     */
    public function registerTax()
    {
        if (term_exists('emvotacao', 'situacao', null) == false) {
            delibera_insert_term(
                'Regime de Votação', 'situacao', array(
                    'description' => 'Pauta com encaminhamentos em Votacao',
                    'slug'        => 'emvotacao',
                ),
                array(
                    'qtrans_term_pt' => 'Regime de Votação',
                    'qtrans_term_en' => 'Voting',
                    'qtrans_term_es' => 'Sistema de Votación',
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Delibera\Modules\ModuleBase::initModule()
     */
    public function initModule($post_id)
    {
        wp_set_object_terms($post_id, 'emvotacao', 'situacao', false);
        $this->newDeadline($post_id);
    }

    /**
     * Append configurations
     *
     * @param array $opts
     */
    public function getMainConfig($opts)
    {
        $opts['dias_votacao']         = '5';
        $opts['tipo_votacao']         = 'checkbox';
        $opts['show_based_proposals'] = 'S';
        return $opts;
    }

    /**
     * Array to show on config page
     *
     * @param array $rows
     */
    public function configPageRows($rows, $opt)
    {
        $rows[] = array(
            "id"      => "dias_votacao",
            "label"   => __('Dias para votação de encaminhamentos:', 'delibera'),
            "content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="' . htmlspecialchars_decode($opt['dias_votacao']) . '" autocomplete="off" />'
        );
        $id     = 'tipo_votacao';
        $value  = htmlspecialchars_decode($opt[$id]);
        $rows[] = array(
            "id"      => $id,
            "label"   => __('Tipo da votação:', 'delibera'),
            //"content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="' . htmlspecialchars_decode($opt['dias_votacao']) . '" autocomplete="off" />'
            "content" => '
				<select name="' . $id . '" id="' . $id . '" autocomplete="off" >
					<option value="checkbox" ' . ($value == 'checkbox' ? 'selected="selected"' : '') . '>' . __('Multipla escolha', 'delibera') . '</option>
					<option value="radio" ' . ($value == 'radio' ? 'selected="selected"' : '') . '>' . __('Opção única', 'delibera') . '</option>
					<option value="pairwise" ' . ($value == 'pairwise' ? 'selected="selected"' : '') . '>' . __('Disputa entre propostas', 'delibera') . '</option>
					<!-- <option value="dropdown" ' . ($value == 'dropdown' ? 'selected="selected"' : '') . '>' . __('Dropdown', 'delibera') . '</option> -->
				</select>
			'
        );
        $rows[] = array(
            "id"      => "show_based_proposals",
            "label"   => __('Votar em propostas que tiveram outras propostas derivadas?', 'delibera'),
            "content" => '<input type="checkbox" name="show_based_proposals" id="show_based_proposals" value="S" ' . (htmlspecialchars_decode($opt['show_based_proposals']) == 'S' ? 'checked="checked"' : '') . ' /><p class="description">' . __('Mostrar para os participantes como opção de votação as propostas originais e não somente a que foi baseada na etapa de relatoria', 'delibera'). '</p>'
        );
        return $rows;
    }

    /**
     * Label to apply to button
     *
     * @param unknown $situation
     */
    public function situationButtonText($situation)
    {
        if ($situation == 'emvotacao') {
            return __('Votar', 'delibera');
        }

        return $situation;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Delibera\Modules\ModuleBase::generateDeadline()
     */
    public function generateDeadline($options_plugin_delibera)
    {
        $dias_votacao = intval(htmlentities($options_plugin_delibera['dias_votacao']));

        $prazo_votacao_sugerido = strtotime("+$dias_votacao days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('emvotacao')));

        return date('d/m/Y', $prazo_votacao_sugerido);
    }

    /**
     * Post Meta Fields display
     *
     * @param \WP_Post $post
     * @param array    $custom                  post custom fields
     * @param array    $options_plugin_delibera Delibera options array
     * @param WP_Term  $situacao
     * @param bool     $disable_edicao
     */
    public function topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao)
    {
        /*global $DeliberaFlow;
        $flow = $DeliberaFlow->get($post->ID);
        $discussao = array_search('discussao', $haystack)*/

        $prazo_votacao        = $this->generateDeadline($options_plugin_delibera);
        $tipo_votacao         = $options_plugin_delibera['tipo_votacao'];
        $show_based_proposals = array_key_exists("show_based_proposals", $custom) ? $custom["show_based_proposals"][0] : 'S';

        if (!($post->post_status == 'draft'
            || $post->post_status == 'auto-draft'
            || $post->post_status == 'pending')
   ) {

            $prazo_votacao = array_key_exists("prazo_votacao", $custom) ? $custom["prazo_votacao"][0] : $prazo_votacao;
            $tipo_votacao  = array_key_exists("tipo_votacao", $custom) ? $custom["tipo_votacao"][0] : $tipo_votacao;
        }

        ?>
        <p>
            <label class="label_prazo_votacao"><?php _e('Prazo para Votações', 'delibera') ?>:</label>
            <input <?php echo $disable_edicao ?> name="prazo_votacao" class="prazo_votacao widefat hasdatepicker" value="<?php echo $prazo_votacao; ?>"/>
        </p>
        <p>
            <label class="label_tipo_votacao"><?php _e('Tipo de Votação', 'delibera') ?>:</label>
            <select name="tipo_votacao" id="tipo_votacao" class="tipo_votacao widefat" autocomplete="off" >
                <option value="checkbox" <?php echo $tipo_votacao == 'checkbox' ? 'selected="selected"' : ''; ?>><?php _e('Multipla escolha', 'delibera'); ?></option>
                <option value="radio" <?php echo $tipo_votacao == 'radio' ? 'selected="selected"' : ''; ?>><?php _e('Opção única', 'delibera'); ?></option>
                <option value="pairwise" <?php echo $tipo_votacao == 'pairwise' ? 'selected="selected"' : ''; ?>><?php _e('Pairwise', 'delibera'); ?></option>
                <!-- <option value="dropdown" <?php echo $tipo_votacao == 'dropdown' ? 'selected="selected"' : ''; ?>><?php _e('Dropdown', 'delibera'); ?></option> -->
            </select>
        </p>
        <p>
            <label class="label_show_based_proposals" title="<?php _e('Mostrar para os participantes como opção de votação as propostas originais e não somente a que foi baseada na etapa de relatoria', 'delibera'); ?>" ><?php _e('Votar em propostas que tiveram outras propostas derivadas?', 'delibera') ?>:
                <input <?php echo $disable_edicao ?> name="show_based_proposals" type="checkbox" value="S" class="show_based_proposals widefat delibera-admin-checkbox" <?php echo $show_based_proposals == 'S' ? 'checked="checked"' : ''; ?> />
            </label>
        </p>

        <div class="delibera_comment_list_panel" style="display: none;">
            <label class="label_opcoes_votacao"><?php _e('Opções de votação', 'delibera') ?>:</label>
            <textarea class="delibera_comment_input_list" ></textarea><a class="btn_delibera_comment_createList" class="button" onclick="delibera_add_comment_input(this);return false;" href="#delibera_comment_input_list"><?php _e('Adicionar opção', 'delibera') ?></a>
            <ul class="delibera_comment_add_current">
        <?php
        foreach (delibera_get_comments_encaminhamentos($post->ID) as $comment)
        {
                ?>
                <p><textarea id="vote-comment-id-<?php echo $comment->comment_ID; ?>" name="delibera_comment_add_list[]">
                <?php echo get_comment_text($comment->comment_ID); ?></textarea><a href="#" class="delibera_comment_input_bt_remove delibera-icon-cancel"></a>
                <input type="hidden" name="delibera_comment_add_list_ids[]" value="<?php echo $comment->comment_ID; ?>"></p>
                <?php
        }
        ?>
            </ul>
        </div>

        <?php
    }

    public function adminScripts()
    {
        $screen   = get_current_screen();
        $screenid = $screen->id;
        if (strpos($screenid, 'page_delibera') !== false || $screenid == 'pauta') {
            wp_enqueue_script('delibera-module-vote-admin', WP_PLUGIN_URL . '/delibera/modules/vote/assets/js/admin-vote.js', array('jquery'));
        }
    }

    public function js()
    {
        wp_enqueue_script('delibera-module-vote', WP_PLUGIN_URL . '/delibera/modules/vote/assets/js/vote.js', array('jquery'));
    }

    public function publishPauta($postID, $opt)
    {
        $events_meta                                   = array();
        $events_meta['delibera_numero_comments_votos'] = 0;

        foreach ($events_meta as $key => $value) // Buscar dados
        {
            if (get_post_meta($postID, $key, true)) // Se já existe
            {
                update_post_meta($postID, $key, $value); // Atualiza
            } else {
                add_post_meta($postID, $key, $value, true); // Senão, cria
            }
        }

    }

    function checkPostData($errors, $opt, $autosave)
    {
        $value  = $_POST['prazo_votacao'];
        $valida = delibera_tratar_data($value);
        if (!$autosave && (empty($value) ||  $valida === false || $valida < 1)) {
            $errors[] = __("É necessário definir corretamente o prazo para votação", "delibera");
        }
        return $errors;
    }

    /**
     * Retorna pautas em Validação
     *
     * @param array $filtro
     */
    public static function getEmvotacao($filtro = array())
    {
        return self::getPautas($filtro);
    }

    public function savePostMetas($events_meta, $opt, $post_id = false)
    {
        if (array_key_exists('prazo_votacao', $_POST)) {
            $events_meta['prazo_votacao'] = sanitize_text_field($_POST['prazo_votacao']);
        }
        if (array_key_exists('tipo_votacao', $_POST)) {
            $events_meta['tipo_votacao'] = sanitize_text_field($_POST['tipo_votacao']);
        }
        $events_meta['show_based_proposals'] = array_key_exists('show_based_proposals', $_POST) ? sanitize_text_field($_POST['show_based_proposals']) : 'N';

        global $post, $current_user;
        if (!is_object($post)) {
            if ($post_id) {
                $post = get_post($post_id);
            } elseif (array_key_exists('post_id', $_POST)) {
                $post = get_post($_POST['post_id']);
            }
        }

        if (array_key_exists('delibera_comment_add_list', $_POST)) {
            if (is_array($_POST['delibera_comment_add_list'])) {
                wp_get_current_user();

                $index             = 0;
                $comment_saved_ids = array_key_exists('delibera_comment_add_list_ids', $_POST) ? $_POST['delibera_comment_add_list_ids'] : array();

                /**
                 * Colect all comments for delete who is deleted at front
                 */
                $all_saved_vote_options = delibera_get_comments_encaminhamentos($post->ID);
                if (!is_array($all_saved_vote_options)) {
                    $all_saved_vote_options = array();
                }
                $all_saved_vote_options = array_object_value_recursive('comment_ID', $all_saved_vote_options);

                foreach ($_POST['delibera_comment_add_list'] as $vote_option)
                {
                    $vote_option = explode(',', $vote_option, 2); // ajax save
                    if (count($vote_option) == 1) {
                        $vote_option = array('', $vote_option[0]);
                    }
                    if ($vote_option[0] == '' && !array_key_exists($index, $comment_saved_ids)) // has ajax info or post info
                    {
                        $commentdata = array(
                            'comment_post_ID'      => (int) $post->ID,
                            'comment_author'       => $current_user->dispay_name,
                            'comment_author_email' => $current_user->user_mail,
                            'comment_author_url' => '',
                            'comment_content'    => wp_kses_data((string) $vote_option[1]),
                            'comment_type'       => '',
                            'comment_parent'     => 0,
                            'user_id'            => (int) $current_user->ID,
                            'comment_author_IP'  => preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']),
                            'comment_agent'      => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 254) : '',
                            'comment_date'       => current_time('mysql'),
                            'comment_date_gmt'   => current_time('mysql', 1),
                            'comment_approved'   => 1
                        );
                        $commentdata = wp_filter_comment($commentdata);


                        //Insert new comment and get the comment ID
                        $comment_id = wp_insert_comment($commentdata);
                        if ($comment_id) {
                            add_comment_meta($comment_id, 'delibera_comment_tipo', 'encaminhamento', true);
                            $nencaminhamentos = get_post_meta($comment_id, 'delibera_numero_comments_encaminhamentos', true);
                            $nencaminhamentos++;
                            update_post_meta($comment_id, 'delibera_numero_comments_encaminhamentos', $nencaminhamentos);
                        }
                    }
                    else
                    {
                        $comment_tmp_id = -1;
                        if ($vote_option[0] != '') {
                            $comment_tmp_id = substr($vote_option[0], strlen('vote-comment-id-'));
                        } else {
                            $comment_tmp_id = $comment_saved_ids[$index];
                        }
                        $comment = get_comment($comment_tmp_id, ARRAY_A);
                        if (is_array($comment)) {
                            $comment['comment_content'] = wp_kses_data((string) $vote_option[1]);
                            wp_update_comment($comment);
                            $all_saved_vote_options = array_diff($all_saved_vote_options, array($comment_tmp_id));
                        } else {
                            //TODO parse comment error
                        }
                    }
                    $index++;
                }
                foreach ($all_saved_vote_options as $comment_delete_id)
                {
                    wp_delete_comment($comment_delete_id);
                }
            }
        }
        return $events_meta;
    }

    public function createPautaAtFront($opt)
    {
        $data_externa = $this->treatFixedDateToEndExtTopic($opt);
        if ($data_externa) {
            $_POST['prazo_votacao'] = $data_externa;
        } else {
            $_POST['prazo_votacao'] = $this->generateDeadline($opt);
        }
    }

    /**
     * Faz a apuração dos votos e toma as devidas ações:
     *    Empate: Mais prazo;
     *    Vencedor: Marco com resolucao e marca o encaminhamento.
     *
     * @param interger $postID
     * @param array    $votos
     */
    function computaVotos($postID, $votos = null)
    {
        if (is_null($votos)) // Ocorre no fim do prazo de votação
        {
            $votos = delibera_get_comments_votacoes($postID);
        }
        $encaminhamentos       = delibera_get_comments_encaminhamentos($postID);
        $encaminhamentos_votos = array();
        foreach ($encaminhamentos as $encaminhamento)
        {
            $encaminhamentos_votos[$encaminhamento->comment_ID] = 0;
        }

        foreach ($votos as $voto_comment)
        {
            $voto = get_comment_meta($voto_comment->comment_ID, 'delibera_votos', true);
            foreach ($voto as $voto_para)
            {
                if (array_key_exists($voto_para, $encaminhamentos_votos)) {
                    $encaminhamentos_votos[$voto_para]++;
                } else {
                    $encaminhamentos_votos[$voto_para] = 1;
                }
            }
        }
        $maisvotado = array(-1, -1);
        $iguais     = array();

        foreach ($encaminhamentos_votos as $encaminhamentos_voto_key => $encaminhamentos_voto_valor)
        {
            if ($encaminhamentos_voto_valor > $maisvotado[1]) {
                $maisvotado[0] = $encaminhamentos_voto_key;
                $maisvotado[1] = $encaminhamentos_voto_valor;
                $iguais        = array();
            }
            elseif ($encaminhamentos_voto_valor == $maisvotado[1]) {
                $iguais[] = $encaminhamentos_voto_key;
            }
            delete_comment_meta($encaminhamentos_voto_key, 'delibera_comment_numero_votos');
            add_comment_meta($encaminhamentos_voto_key, 'delibera_comment_numero_votos', $encaminhamentos_voto_valor, true);
        }

        // nao finaliza a votacao caso haja um empate, exceto quando o administrador clicar no botão "Forçar fim do prazo"
        if (count($iguais) > 0 && !(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delibera_forca_fim_prazo_action')) // Empate
        {
            $this->newDeadline($postID, false);
        } else {
            update_comment_meta($maisvotado[0], 'delibera_comment_tipo', 'resolucao');
            add_post_meta($postID, 'data_resolucao', date('d/m/Y H:i:s'), true);
            ////delibera_notificar_situacao($postID);
            if (has_action('votacao_concluida')) {
                do_action('votacao_concluida', $post);
            }
            \Delibera\Flow::next($postID);
        }
    }

    /**
     * Verifica se o número de votos é igual ao número de representantes para deflagar fim da votação
     *
     * @param integer $postID
     */
    public function validaVotos($postID)
    {
        global $wp_roles, $wpdb;
        $users_count = 0;
        foreach ($wp_roles->roles as $nome => $role)
        {
            if (is_array($role['capabilities']) && array_key_exists('votar', $role['capabilities']) && $role['capabilities']['votar'] == 1) {
                $result = $wpdb->get_results("SELECT count(*) as n FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND meta_value LIKE '%$nome%' ");
                $users_count += $result[0]->n;
            }
        }

        $votos = delibera_get_comments_votacoes($postID);

        $votos_count = count($votos);

        if ($votos_count >= $users_count) {
            $this->computaVotos($postID, $votos);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Delibera\Modules\ModuleBase::deadline()
     */
    public static function deadline($args)
    {
        $post_id = $args['post_ID'];
        $current = \Delibera\Flow::getCurrentModule($post_id);
        if ($current instanceof \Delibera\Modules\Vote ) // Check if the vote was not completed before deadline
        {
            $current->computaVotos($post_id);
        }
    }

    /**
     * Remove WordPress duplicate comment filter
     *
     * @param  array $tipos
     * @return array
     */
    public function unfilterDuplicate($tipos)
    {
        $tipos[] = 'voto';
        return $tipos;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Delibera\Modules\ModuleBase::getCommentListLabel()
     */
    public function getCommentListLabel()
    {
        return __('Votação da Pauta', 'delibera');
    }

    /**
     * update probabilities array of a topic
     *
     * @param  int   $post_id
     * @param  array $probabilities
     * @param  int   $id1
     * @param  int   $id2
     * @return array
     */
    public static function updateProbabilities($post_id = false, $probabilities = false, $id1, $id2)
    {
        if ($post_id == false) {
            $post_id = get_the_ID();
        }
        if ($probabilities == false) {
            $probabilities = self::getProbabilities($post_id);
        }

        $num_options = count($probabilities);
        foreach ($probabilities['probability'] as $index => $probability)
        {
            if (($probabilities['id'][$index] == $id1 || $probabilities['id'][$index] == $id2) && $probabilities['probability'][$index] > 1) {
                $probabilities['probability'][$index]--;
            }
            elseif ($probabilities['probability'][$index] < $num_options) {
                $probabilities['probability'][$index]++;
            }
            $probabilities['cumulative'][$index] = $index > 0 ?
            $probabilities['cumulative'][$index - 1] + $probabilities['probability'][$index]:
            $probabilities['probability'][$index]
            ;
        }
        update_post_meta($post_id, '_delibera_vote_options_probabilities', $probabilities);
        return $probabilities;
    }

    /**
     * Create the probabilities array
     *
     * @param  int $post_id
     * @return array
     */
    public static function createProbabilitiesOpionsArray($post_id = false)
    {
        if ($post_id == false) {
            $post_id = get_the_ID();
        }

        $options = delibera_get_comments_encaminhamentos($post_id);

        shuffle($options); // lets random the options too

        $probabilities = array('id' => array(), 'probability' => array(), 'cumulative' => array());
        if (count($options) == 0) {
            return $probabilities;
        }

        $num_options = count($options);
        foreach ($options as $option)
        {
            $probabilities['id'][]          = $option->comment_ID;
            $probabilities['probability'][] = $num_options;
            $probabilities['cumulative'][]  = count($probabilities['cumulative']) > 0 ?
            $probabilities['cumulative'][count($probabilities['cumulative']) - 1]+$num_options :
            $num_options
            ;
        }
        $probabilities['cumulative'][count($probabilities['cumulative']) - 1]++;
        update_post_meta($post_id, '_delibera_vote_options_probabilities', $probabilities);
        return $probabilities;
    }

    /**
     * return probabilities array from database or create a new one
     *
     * @param  int $post_id
     * @return array
     */
    public static function getProbabilities($post_id = false)
    {
        if ($post_id == false) {
            $post_id = get_the_ID();
        }

        $options_probabilities = get_post_meta($post_id, '_delibera_vote_options_probabilities', true);
        if (empty($options_probabilities) || !is_array($options_probabilities)) {
            $options_probabilities = self::createProbabilitiesOpionsArray($post_id);
        }
        return $options_probabilities;
    }

    /**
     * Update the probabilities using comulative method
     *
     * @param  array $probabilities
     * @return array
     */
    public static function updateCumulativeProbability($probabilities)
    {
        foreach ($probabilities['probability'] as $index => $probability)
        {
            $probabilities['cumulative'][$index] = $index > 0 ?
            $probabilities['cumulative'][$index - 1] + $probability:
            $probability
            ;
        }
        $probabilities['cumulative'][count($probabilities['cumulative']) - 1]++;
        return $probabilities;
    }

    /**
     * Remove a vote option from the options probability array
     *
     * @param  array $probabilities
     * @param  int   $index
     * @return array
     */
    public static function deleteProbability($probabilities, $index)
    {
        array_splice($probabilities['cumulative'], $index, 1);
        array_splice($probabilities['probability'], $index, 1);
        array_splice($probabilities['id'], $index, 1);
        $probabilities = self::updateCumulativeProbability($probabilities);
        return $probabilities;
    }

    /**
     * Return a comment id pair to pairwise
     *
     * @param  int $post_id
     * @return array
     */
    public static function getAPair($post_id = false)
    {
        if ($post_id == false) {
            $post_id = get_the_ID();
        }

        $probabilities = self::getProbabilities($post_id);
        $options_probabilities = $probabilities;
        $options_probabilities2 = $probabilities;
        if (count($options_probabilities['id']) > 1) // need at least a pair
        {
            $max = $options_probabilities['cumulative'][count($options_probabilities['cumulative']) -1] - 1;//echo "max: $max\n";
            $min = 0;
            $rand1 = mt_rand($min, $max);//echo "rand1: $rand1\n";
            $index1 = -1;
            $prev_cumulative_val = 0;
            //print_r($options_probabilities);
            foreach ($options_probabilities['cumulative'] as $index => $cumulative_val)
            {
                if ($rand1 >= $prev_cumulative_val && $rand1 < $cumulative_val) {
                    $index1 = $index;
                    //echo "Index1: $index\n";
                    $options_probabilities2 = self::deleteProbability($options_probabilities2, $index);
                    break;
                }
                $prev_cumulative_val = $cumulative_val;
            }

            $prev_cumulative_val = 0;
            $index2              = -1;
            $max                 = $options_probabilities2['cumulative'][count($options_probabilities2['cumulative'])-1] - 1;//echo "max2: $max\n";
            $rand2               = mt_rand($min, $max);//echo "rand2: $rand2\n";
            //print_r($options_probabilities2);
            foreach ($options_probabilities2['cumulative'] as $index => $cumulative_val)
            {
                if ($rand2 >= $prev_cumulative_val && $rand2 < $cumulative_val) {
                    $index2 = $index;
                    //echo "Index2: $index\n";
                    break;
                }
                $prev_cumulative_val = $cumulative_val;
            }
            if ($index1 == -1 || $index2 == -1) {
                return false;
            }

            return array($options_probabilities['id'][$index1], $options_probabilities2['id'][$index2]);
        }
        return false;
    }

    /**
     * Update PairWise Statistics
     *
     * @param int $post_id
     * @param int $vote    comment type vote id of voted comment
     * @param int $id1     comment type vote id of option 1
     * @param int $id2     comment type vote id of option 2
     */
    public static function updatePairStats($post_id = false, $vote, $id1, $id2)
    {
        if ($post_id == false) {
            $post_id = get_the_ID();
        }

        $looser = $vote == $id1 ? $id2 : $id1;

        $pairstats = get_post_meta($post_id, '_delibera_vote_pair_stats', true);
        if (!is_array($pairstats)) {
            $pairstats = array();
        }
        if (!array_key_exists($vote, $pairstats)) {
            $pairstats[$vote] = array();
        }
        if (!array_key_exists($looser, $pairstats[$vote])) {
            $pairstats[$vote][$looser] = 0;
        }
        $pairstats[$vote][$looser]++;
        update_post_meta($post_id, '_delibera_vote_pair_stats', $pairstats);
    }

    /**
     * Hook executado quando algum usuário escole um opção de voto
     *
     * @package Pauta\Vote
     */
    public function voteCallback()
    {
        if (check_admin_referer('delibera_vote_callback', 'nonce') && is_user_logged_in() && current_user_can('vote')) {
            $post_id = esc_attr($_REQUEST['post_id']);
            $situacao = delibera_get_situacao($post_id);
            if ($situacao->slug == 'emvotacao') {
                $user = get_current_user();
                $_POST['delibera_comment_tipo'] = 'voto';
                if (class_exists('\wpCommentAttachment')) {
                    remove_filter('comment_text', array(new \wpCommentAttachment, 'displayAttachment'), 10, 3); // remove problematic filter
                }
                $ret  = delibera_new_comment($post_id, 'Voto de ' . $user, 'voto', 0);
                $pair = explode(',', esc_attr($_REQUEST['pair']));
                self::updateProbabilities($post_id, false, $pair[0], $pair[1]);
                self::updatePairStats($post_id, esc_attr($_REQUEST['delibera_voto']), $pair[0], $pair[1]);
                $proposals = delibera_get_comments_encaminhamentos($post_id);
                echo delibera_generateProposalPair($proposals, '', array(), $post_id);
            }
        }
        die();
    }

    /**
     * Return Vote type
     *
     * @param  int $post_id
     * @return string
     */
    public static function getVoteType($post_id = false)
    {
        if (false === $post_id) {
            $post_id = get_the_ID();
        }

        $tipo = get_post_meta($post_id, 'tipo_votacao', true);

        return $tipo ? $tipo : 'checkbox';
    }

    /**
     * Create a new Vote
     *
     * @param int $comment_id
     */
    public static function newVote($comment_id = false)
    {
        add_comment_meta($comment_id, 'delibera_comment_tipo', 'voto', true);

        $votos = array();

        if ('pairwise' === self::getVoteType()) {
            $pair = esc_attr($_REQUEST['delibera-pair']);
            $pair = explode(',', $pair);
            self::updateProbabilities($post_id, false, $pair[0], $pair[1]);
            self::updatePairStats($post_id, $comment_id, $pair[0], $pair[1]);
        }

        foreach ($_POST as $postkey => $postvar)
        {
            if (substr($postkey, 0, strlen('delibera_voto')) == 'delibera_voto') {
                $votos[] = $postvar;
            }
        }

        add_comment_meta($comment_id, 'delibera_votos', $votos, true);

        $comment = get_comment($comment_id);
        //delibera_valida_votos($comment->comment_post_ID); TODO use module version

        $nvotos = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_votos', true);
        $nvotos++;
        update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_votos', $nvotos);

        if (has_action('delibera_novo_voto')) {
            do_action('delibera_novo_voto', $comment_id, $comment, $votos);
        }
    }

    /**
     * Return vote count
     *
     * @param  int $post_id
     * @return number
     */
    public static function getVoteCount($post_id = false)
    {
        if (false === $post_id) {
            $post_id = get_the_ID();
        }

        $votes = get_post_meta($post_id, 'delibera_numero_comments_votos', true);

        return empty($votes) ? 0 : intval($votes);
    }
}
$DeliberaVote = new \Delibera\Modules\Vote();
