<?php

function delibera_get_comment_type($comment)
{
    $comment_ID = $comment;
    if (is_object($comment_ID)) {
        $comment_ID = $comment->comment_ID;
    }
    return get_comment_meta($comment_ID, "delibera_comment_tipo", true);
}

/**
 * Retorna o nome "amigável" do tipo de um comentário.
 *
 * @param  object $comment
 * @param  string $tipo
 * @param  bool   $echo
 * @return string
 */
function delibera_get_comment_type_label($comment, $tipo = false, $echo = true)
{
    if ($tipo === false) {
        $tipo = get_comment_meta($comment->comment_ID, "delibera_comment_tipo", true);
    }
    switch ($tipo)
    {
    case 'validacao':
        if ($echo) {
            _e('Validação', 'delibera');
        }
        return __('Validação', 'delibera');
        break;
    case 'encaminhamento_selecionado':
    case 'encaminhamento':
        if ($echo) {
            _e('Proposta', 'delibera');
        }
        return __('Proposta', 'delibera');
        break;
    case 'voto':
        if ($echo) {
            _e('Voto', 'delibera');
        }
        return __('Voto', 'delibera');
        break;
    case 'resolucao':
        if ($echo) {
            _e('Resolução', 'delibera');
        }
        return __('Resolução', 'delibera');
        break;
    case 'discussao':
        if ($echo) {
            _e('Opinião', 'delibera');
        }
        return __('Opinião', 'delibera');
    default:
        break;
    }
}

/**
 * Retorna uma string com a quantidade de comentários
 * associados a pauta do tipo correspondente a situação
 * atual.
 *
 * @param  int $postId
 * @return string (exemplo: "5 votos")
 */
function delibera_get_comments_count_by_type($postId)
{
    $situacao = delibera_get_situacao($postId);

    switch ($situacao->slug) {
    case 'validacao':
        $count = count(delibera_get_comments_validacoes($postId));

        if ($count == 0) {
            $label = __('Nenhuma validação', 'delibera');
        } else if ($count == 1) {
            $label = __('1 validação', 'delibera');
        } else {
            $label = sprintf(__('%d validações', 'delibera'), $count);
        }

        return $label;
    case 'discussao':
        $count = count(delibera_get_comments_discussoes($postId));

        if ($count == 0) {
            $label = __('Nenhum comentário', 'delibera');
        } else if ($count == 1) {
            $label = __('1 comentário', 'delibera');
        } else {
            $label = sprintf(__('%d comentários', 'delibera'), $count);
        }

        return $label;
    case 'emvotacao':
        $count = count(delibera_get_comments_votacoes($postId));

        if ($count == 0) {
            $label = __('Nenhum voto', 'delibera');
        } else if ($count == 1) {
            $label = __('1 voto', 'delibera');
        } else {
            $label = sprintf(__('%d votos', 'delibera'), $count);
        }

        return $label;
    }
}

function delibera_get_comments_types()
{
    return array('validacao', 'discussao', 'encaminhamento', 'encaminhamento_selecionado', 'voto', 'resolucao');
}

function delibera_get_comments_link()
{
    global $post;

    return get_permalink($post->ID) . '#delibera-comments';
}

function delibera_get_comment_link($comment_pass = false)
{
    global $comment;
    if (is_object($comment_pass)) {
        $comment = $comment_pass;
    }

    if (!isset($comment)) {
        return str_replace('#comment', '#delibera-comment', get_comments_link());
    }

    return str_replace('#comment', '#delibera-comment', get_comment_link($comment));
}

/**
 * where to return after comment post
 *
 * @param  string     $location return to
 * @param  WP_Comment $comment
 * @return unknown|mixed
 */
function delibera_comment_post_redirect($location, $comment)
{
    global $post;
    return (is_object($post) &&
             property_exists($post, 'post_type') &&
             $post->post_type == 'pauta')
        ? preg_replace("/#comment-([\d]+)/", "#delibera-comment-" . $comment->comment_ID, $location) : $location;
}
add_filter('comment_post_redirect', 'delibera_comment_post_redirect', 10, 2);

/**
 * Comentário em listagem (Visualização)
 *
 * @param string $commentText
 */
function delibera_comment_text($commentText)
{
    global $comment, $post, $delibera_comments_padrao;
    if (get_post_type($post) == "pauta" && $delibera_comments_padrao !== true) {
        $commentId   = isset($comment) ? $comment->comment_ID : false;
        $commentText = delibera_comment_text_filtro($commentText, $commentId);
        $tipo        = get_comment_meta($commentId, "delibera_comment_tipo", true);
        $total       = 0;
        $nvotos      = 0;
        switch ($tipo)
        {
        case 'validacao':
        {
            $validacao      = get_comment_meta($comment->comment_ID, "delibera_validacao", true);
            $commentTextTmp = $commentText;
            $commentText    = '<div class="painel_validacao delibera-comment-text">';
            switch ($validacao)
            {
            case 'S':
                $commentText .= '<label class="delibera-aceitou-view">' . __('Aceitou', 'delibera') . '</label>';
                break;
            case 'A':
                $commentText .= '<label class="delibera-abstencao-view">' . __('Abstenção', 'delibera') . '</label>';
                break;
            case 'N':
            default:
                $commentText .=    '<label class="delibera-rejeitou-view">' . __('Rejeitou', 'delibera') . '</label>';
                break;
            }
            if (get_post_meta($comment->comment_post_ID, 'delibera_validation_show_comment', true) == 'S') {
                $commentText .= '<div class="delibera-comment-validacao-text">';
                $commentText .= $commentTextTmp;
                $commentText .= '</div>';
            }
            $commentText .= '</div>';
            }
            break;
            case 'discussao':
            case 'encaminhamento':
            case 'relatoria':
            {
                $situacao = delibera_get_situacao($comment->comment_post_ID);
                if ($situacao->slug == 'discussao' || $situacao->slug == 'relatoria') {
                    if ($tipo == "discussao") {
                        $class_comment = "discussao delibera-comment-text";
                    } else {
                        $class_comment = "encaminhamento delibera-comment-text";
                    }
                    $commentText = "<div id=\"delibera-comment-text-" . $comment->comment_ID . "\" class='" . $class_comment . "'>" . $commentText . "</div>";
                }
                elseif ($situacao->slug == 'comresolucao' && !defined('PRINT')) {
                    $total       = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_votos', true);
                    $nvotos      = get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true);
                    $commentText = '
                            <div id="delibera-comment-text-' . $comment->comment_ID . '" class="comentario_coluna1 delibera-comment-text">
                                ' . $commentText . '
                            </div>
                            <div class="comentario_coluna2 delibera-comment-text">
                                ' . $nvotos.($nvotos == 1 ? " " . __('Voto', 'delibera') : " " . __('Votos', 'delibera')).
                    '(' . number_format_i18n($nvotos > 0 && $total > 0 ? (($nvotos*100)/$total) : 0, 2) . '%)
                            </div>
                        ';
                }
                if (has_filter('delibera_mostra_discussao')) {
                    $commentText = apply_filters('delibera_mostra_discussao', $commentText, $total, $nvotos, $situacao->slug);
                }
            }
            break;
            case 'resolucao':
            {
                $total       = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_votos', true);
                $nvotos      = get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true);
                $commentText = '
                        <div class="comentario_coluna1 delibera-comment-text">
                            ' . $commentText . '
                        </div>
                        <div class="comentario_coluna2 delibera-comment-text">
                            ' . $nvotos.($nvotos == 1 ? " " . __('Voto', 'delibera') : " " . __('Votos', 'delibera')).
                            '(' . number_format_i18n($nvotos > 0 && $total > 0 ? (($nvotos*100)/$total) : 0, 2) . '%)
                        </div>
                    ';
            }
            break;
            case 'voto':
            {
                $commentText = '
                    <div class="comentario_coluna1 delibera-comment-text">
                        ' . $commentText . '
                    </div>
                    ';
            }
            break;
        }
        if (has_filter('delibera_mostra_discussao')) {
            $commentText = apply_filters('delibera_mostra_discussao', $commentText, $tipo, $total, $nvotos);
        }
        return $commentText;
    } else {
        return '<div class="delibera-comment-text">' . $commentText . '</div>';
    }
}

add_filter('comment_text', 'delibera_comment_text');

function delibera_comment_text_filtro($text, $comment_id = false, $show = true)
{
    $opt     = delibera_get_config();
    $tamanho = $opt['numero_max_palavras_comentario'];
    if ($opt['limitar_tamanho_comentario'] === 'S' && strlen($text) > $tamanho) {
        if ($comment_id === false) {
            $comment_id = get_comment_ID();
        }
        $string_temp = wordwrap($text, $tamanho, '##!##');
        $cut         = strpos($string_temp, '##!##');

        $text = delibera_show_hide_button($comment_id, $text, $cut, $show);
    }
    return $text;
}

function delibera_show_hide_button($comment_id, $text, $cut, $show)
{
    $comment_text = $text;
    $label        = __('Continue lendo este comentário', 'delibera');
    if ($show === true) {
        $showhide = '
			<div id="showhide_comment' . $comment_id . '" class="delibera-slide-text" style="display:none" >
		';
        $showhide_button = '
			<div id="showhide_button' . $comment_id . '" class="delibera-slide" onclick="delibera_showhide(\'' . $comment_id . '\');" >' . $label . '</div>
		';
        $part = '<div id="showhide-comment-part-text-' . $comment_id . '" class="delibera-slide-part-text" >';
        $part .= truncate($text, $cut, '&hellip;');
        $part .= '</div>';

        $comment_text = $part . $showhide . $text . "</div>" . $showhide_button;
    } else {
        $link = '<a class="delibera_leia_mais_link" href="' . delibera_get_comment_link($comment_id) . '">' . $label . "</a>";
        $comment_text = truncate($text, $cut, '&hellip;') . '<br/>
		' . $link;
    }
    return $comment_text;
}

function delibera_comments_open($open, $post_id)
{
    if (is_user_logged_in()) {
        if ('pauta' == get_post_type($post_id)) {
            return $open && delibera_can_comment($post_id);
        }
    }
    return $open;
}
add_filter('comments_open', 'delibera_comments_open', 10, 2);

/**
 * Verifica se é possível fazer comentários, se o usuário tiver poder para tanto
 *
 * @param mixed $postID
 */
function delibera_comments_is_open($postID = null)
{
    if (is_null($postID)) {
        $postID = get_the_ID();
    }

    $situacoes_validas = array('validacao' => true, 'discussao' => true, 'emvotacao' => true, 'elegerelator' => true,'relatoria'=>true);
    $situacao          = delibera_get_situacao($postID);

    if (array_key_exists($situacao->slug, $situacoes_validas)) {
        return $situacoes_validas[$situacao->slug];
    }

    return false;
}

function delibera_comment_form_action($postID)
{
    if (is_pauta()) {
        global $comment_footer;
        echo $comment_footer;
        echo "</div>";
        /**
         * @var \WP_Term $situacao
         */
        if (function_exists('ecu_upload_form') && $situacao->slug != 'relatoria' && $situacao->slug != 'discussao') {
            echo '<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery("#ecu_uploadform").replaceWith("");
				});
				</script>';
        }
    }
}
add_action('comment_form', 'delibera_comment_form_action');

/**
 * Salvar custom fields do comentário
 *
 * @param int $comment_id
 */
function delibera_save_comment_metas($comment_id)
{
    $tipo = get_comment_meta($comment_id, "delibera_comment_tipo", true);

    if ($tipo == false || $tipo == "") {
        if (array_key_exists("delibera_comment_tipo", $_POST)) {
            $tipo = $_POST['delibera_comment_tipo'];
        }
    }

    delibera_curtir_comment_meta($comment_id);

    delibera_discordar_comment_meta($comment_id);

    $comment  = get_comment($comment_id);
    $situacao = delibera_get_situacao($comment->comment_post_ID);

    add_comment_meta($comment_id, 'delibera_situacao', $situacao->slug, true); // save situacao when comment has been created for better history

    switch($tipo)
    {
        case "validacao":
        {
            add_comment_meta($comment_id, 'delibera_validacao', $_POST['delibera_validacao'], true);
            add_comment_meta($comment_id, 'delibera_comment_tipo', 'validacao', true);

            if ($_POST['delibera_validacao'] == "S") {
                $validacoes = get_post_meta($comment->comment_post_ID, 'numero_validacoes', true);
                $validacoes++;
                update_post_meta($comment->comment_post_ID, 'numero_validacoes', $validacoes); // Atualiza
                delibera_valida_validacoes($comment->comment_post_ID);
            }
            $nvalidacoes = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_validacoes', true);
            $nvalidacoes++;
            update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_validacoes', $nvalidacoes);
        }
        break;
        case 'discussao':
        case 'encaminhamento':
        {
            $encaminhamento = $_POST['delibera_encaminha'];
            \Delibera\Modules\Discussion::treatCommentType($comment, $encaminhamento);

        }
        break;
        case 'voto':
        {
            \Delibera\Modules\Vote::newVote($comment_id);
        }
        break;
        default:
        {
            $npadroes = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_padroes', true);
            $npadroes++;
            update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_padroes', $npadroes);
        }
        break;
    }
    if (array_search($tipo, delibera_get_comments_types()) !== false) {
        wp_set_comment_status($comment_id, 'approve');
        delibera_notificar_novo_comentario($comment);
        do_action('delibera_nova_interacao', $comment_id);
    }
}
add_action('comment_post', 'delibera_save_comment_metas', 1);

function delibera_pre_edit_comment($dados)
{
    $comment_id = 0;
    if (array_key_exists('comment_ID', $_POST)) {
        $comment_id = $_POST['comment_ID'];
    }
    else
    {
        global $comment;
        if (isset($comment->comment_ID)) {
            $comment_id = $comment->comment_ID;
        } else {
            wp_die(__('Você não pode Editar esse tipo de comentário', 'delibera'));
        }
    }

    $tipo = get_comment_meta($comment_id, "delibera_comment_tipo", true);
    if (array_search($tipo, delibera_get_comments_types()) !== false) {
        wp_die(__('Você não pode Editar esse tipo de comentário', 'delibera'));
    }
}
//add_filter('comment_save_pre', 'delibera_pre_edit_comment'); //TODO Verificar edição

// require_once __DIR__.DIRECTORY_SEPARATOR . 'delibera_template.php';

function delibera_get_comments_padrao($args = array(), $file = '/comments.php')
{
    global $delibera_comments_padrao;
    $delibera_comments_padrao = true;
    comments_template($file);
    $delibera_comments_padrao = false;
}

/**
 * Retorna comentários do Delibera de acordo com o tipo.
 *
 * @param  int          $post_id
 * @param  string|array $tipo    um tipo ou um array de tipos
 * @return array
 */
function delibera_get_comments($post_id, $tipo = array(), $args = array())
{
    if (is_string($tipo)) {
        $tipo = array($tipo);
    }

    $args = array_merge(array('post_id' => $post_id), $args);
    $comments = get_comments($args);
    $ret      = array();
    foreach ($comments as $comment)
    {
        $comment_tipo = get_comment_meta($comment->comment_ID, 'delibera_comment_tipo', true);
        if ((strlen($comment_tipo) > 0 && count($tipo) == 0 /* do not filter, but is delibera comment */) || in_array($comment_tipo, $tipo)) {
            $ret[] = $comment;
        }
    }
    return $ret;
}

function delibera_wp_list_comments($args = array(), $comments = null)
{
    global $post;
    global $delibera_comments_padrao;

    if (get_post_type($post) == "pauta") {
        $situacao = delibera_get_situacao($post->ID);

        if ($delibera_comments_padrao === true) {
            $args['post_id'] = $post->ID;
            $args['walker']  = new Delibera_Walker_Comment_padrao();
            $comments        = get_comments($args);
            $ret             = array();
            foreach ($comments as $comment)
            {
                $tipo_tmp = get_comment_meta($comment->comment_ID, 'delibera_comment_tipo', true);
                if (strlen($tipo_tmp) <= 0 || $tipo_tmp === false) {
                    $ret[] = $comment;
                }
            }
            wp_list_comments($args, $ret);
        } elseif ($situacao->slug == 'validacao') {
            //comment_form();
            $args['walker'] = new Delibera_Walker_Comment();
            //$args['callback'] = 'delibera_comments_list';
            ?>
            <div class="delibera_lista_validacoes">
                <?php wp_list_comments($args, $comments); ?>
            </div>
            <?php
        } elseif ($situacao->slug == 'comresolucao') {
            $args['walker'] = new Delibera_Walker_Comment();
            wp_list_comments($args, $comments);

            $encaminhamentos = delibera_get_comments_encaminhamentos($post->ID);
            $discussoes      = delibera_get_comments_discussoes($post->ID);
            ?>
            <div class="delibera_encaminhamentos_inferior">
                <?php wp_list_comments($args, $encaminhamentos); ?>
            </div>

            <div id="comments" class="delibera_opinioes_inferior">
                <hr>
                <h2 class="comments-title bottom"><?php _e('Histórico da pauta', 'delibera'); ?></h2>
                <?php wp_list_comments($args, $discussoes); ?>
            </div>
            <?php
        } elseif ($situacao->slug == 'emvotacao') {
            $tipo = \Delibera\Modules\Vote::getVoteType();

            if ($tipo == 'pairwise') {
                $votos = \Delibera\Modules\Vote::getVoteCount();
                //echo $votos;
                //TODO put that HTML on the theme
                ?>
                <li class="comment even thread-even depth-1 delibera-comment-div-emvotacao-pairwise" >
                    <div class="delibera-comment-body delibera-comment-emvotacao delibera-comment-emvotacao-pairwise">
                        <div class="comentario_coluna1 delibera-comment-text">
                            <?php _e('Número de votos até o momento', 'delibera'); ?>
                        </div>
                        <div class="comentario_coluna2 delibera-comment-text">
                            <span class="delibera-result-number delibera-result-number-votes">
                                <?php echo $votos; ?>
                            </span>
                            <?php echo _n('Voto', 'Votos', $votos, 'delibera'); ?>
                        </div>
                    </div>
                </li>     <?php
            } else {
                $args['walker'] = new Delibera_Walker_Comment();
                wp_list_comments($args, $comments);
            }
        } else {
            $args['walker'] = new Delibera_Walker_Comment();
            //$args['callback'] = 'delibera_comments_list';
            wp_list_comments($args, $comments);
        }
    } else {
        wp_list_comments($args, $comments);
    }
}


/**
 * Retrieve a list of comments.
 *
 * The comment list can be for the blog as a whole or for an individual post.
 *
 * The list of comment arguments are 'status', 'orderby', 'comment_date_gmt',
 * 'order', 'number', 'offset', and 'post_id'.
 *
 * @since 2.7.0
 * @uses  $wpdb
 *
 * @param  mixed $args Optional. Array or string of options to override defaults.
 * @return array List of comments.
 */
function delibera_wp_get_comments($args = '')
{
    $query = new delibera_WP_Comment_Query();
    return $query->query($args);
}

function delibera_get_comments_validacoes($post_id)
{
    return delibera_get_comments($post_id, 'validacao');
}

function delibera_get_comments_discussoes($post_id)
{
    return delibera_get_comments($post_id, 'discussao');
}

function delibera_get_comments_encaminhamentos($post_id)
{
    return delibera_get_comments($post_id, 'encaminhamento');
}

/**
 * Retorna os encaminhamentos dos tipos 'encaminhamento' e
 * 'encaminhamento_selecionado' (aqueles que foram selecionados
 * pelo relator para ir para votação).
 *
 * @param  int $post_id
 * @return array
 */
function delibera_get_comments_all_encaminhamentos($post_id)
{
    return delibera_get_comments($post_id, array('encaminhamento', 'encaminhamento_selecionado'));
}

/**
 * Retorna os encaminhamentos do tipo 'encaminhamento_selecionado'
 * (aqueles que foram selecionados pelo relator para ir para votação).
 *
 * @param  int $post_id
 * @return array
 */
function delibera_get_comments_encaminhamentos_selecionados($post_id)
{
    return delibera_get_comments($post_id, 'encaminhamento_selecionado');
}


function delibera_get_comments_votacoes($post_id)
{
    return delibera_get_comments($post_id, 'voto');
}

function delibera_get_comments_resolucoes($post_id)
{
    if (has_filter('delibera_get_resolucoes')) {
        return apply_filters('delibera_get_resolucoes', delibera_get_comments($post_id, 'resolucao'));
    }
    return delibera_get_comments($post_id, 'resolucao');
}

/**
 * Busca comentários com o tipo em tipos
 *
 * @param array $comments lista de comentários a ser filtrada
 * @param array $tipos    tipos aceitos
 */
function delibera_comments_filter_portipo($comments, $tipos)
{
    $ret = array();

    foreach ($comments as $comment)
    {
        $tipo = get_comment_meta($comment->comment_ID, 'delibera_comment_tipo', true);
        if (array_search($tipo, $tipos) !== false) {
            $ret[] = $comment;
        }
    }
    return $ret;
}

/**
 * Filtro que retorna Comentário filtrados pela a situação da pauta
 *
 * @param  array $comments
 * @param  int   $postID
 * @return array Comentários filtrados
 */
function delibera_get_comments_filter($comments)
{
    global $delibera_comments_padrao;

    if ($delibera_comments_padrao === true) {
        return $comments;
    }

    $ret = array();

    if (count($comments) > 0) {
        if (get_post_type($comments[0]->comment_post_ID) == "pauta") {
            $situacao = delibera_get_situacao($comments[0]->comment_post_ID);
            switch ($situacao->slug)
            {
                case 'validacao':
                {
                    $ret = delibera_comments_filter_portipo($comments, array('validacao'));
                }
                break;
                case 'discussao':
                {
                    $ret = delibera_comments_filter_portipo($comments, array('discussao', 'encaminhamento'));
                }
                break;
                case 'relatoria':
                {
                    $ret = delibera_comments_filter_portipo($comments, array('discussao', 'encaminhamento'));
                }
                break;
                case 'emvotacao':
                {
                    $ret = delibera_comments_filter_portipo($comments, array('voto'));
                }
                break;
                case 'comresolucao':
                {
                    $ret = delibera_comments_filter_portipo($comments, array('resolucao'));
                }
                break;
            }
            return $ret;
        }
    }
    return $comments;
}
add_filter('comments_array', 'delibera_get_comments_filter');

function delibera_comment_number($postID, $tipo)
{
    switch($tipo)
    {
        case 'validacao':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_validacoes', true));
            break;
        case 'discussao':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_discussoes', true));
            break;
        case 'encaminhamento':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_encaminhamentos', true));
            break;
        case 'voto':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_votos', true));
            break;
            /*case 'resolucao':
                return doubleval(get_post_meta($postID, 'delibera_numero_comments_resolucoes', true)); TODO Número de resoluções, baseado no mínimo de votos, ou marcação especial
            break;*/
        case 'todos':
            return get_post($postID)->comment_count;
            break;
        default:
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_padroes', true));
            break;
    }
}

function delibera_comment_number_filtro($count, $postID)
{
    if (!is_pauta()) {
        return $count;
    }
    $situacao = delibera_get_situacao($postID);

    if (!$situacao) {
        return;
    }

    switch($situacao->slug)
    {
        case 'validacao':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_validacoes', true));
            break;
        case 'discussao':
        case 'comresolucao':
            return doubleval(
                get_post_meta($postID, 'delibera_numero_comments_encaminhamentos', true) +
                    get_post_meta($postID, 'delibera_numero_comments_discussoes', true)
            );
        break;
        case 'relatoria':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_encaminhamentos', true));
            break;
        case 'emvotacao':
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_votos', true));
            break;
        default:
            return doubleval(get_post_meta($postID, 'delibera_numero_comments_padroes', true));
            break;
    }
}
add_filter('get_comments_number', 'delibera_comment_number_filtro', 10, 2);

/**
 * Sempre que um usuário valida uma pauta
 * verifica se o número mínimo de validações foi
 * atingido e se sim muda a situação da pauta de
 * "emvotacao" para "discussao".
 *
 * @param  int $postID
 * @return null
 */
function delibera_valida_validacoes($postID)
{
    $validacoes     = get_post_meta($postID, 'numero_validacoes', true);
    $min_validacoes = get_post_meta($postID, 'min_validacoes', true);

    $situacao = delibera_get_situacao($postID);

    if ($validacoes >= $min_validacoes && $situacao->slug == 'validacao') // check situacao to avoid same time final validation to avoid topic advancing 2 stages
    {
        //wp_set_object_terms($post, 'discussao', 'situacao', false); //Mudar situação para Discussão
        \Delibera\Flow::next($postID);
        if (has_action('delibera_validacao_concluida')) {
            do_action('delibera_validacao_concluida', $postID);
        }
    } else {
        if (has_action('delibera_validacao')) {
            do_action('delibera_validacao', $postID);
        }
    }
}

/* Faz os testes de permissões para garantir que nenhum engraçadinho
 * está injetando variáveis maliciosas.
 * TODO: Incluir todas as variáveis a serem verificadas aqui
 */
function delibera_valida_permissoes($comment_ID)
{
    if (get_post_type() == 'pauta' && !delibera_current_user_can_participate()) {
        if (array_key_exists('delibera_validacao', $_REQUEST) || array_key_exists('delibera_encaminha', $_REQUEST)) {
            wp_die("Nananina não! Você não tem que ter permissão pra votar.", "Tocooo!");
        }
    }
}
add_action('wp_blacklist_check', 'delibera_valida_permissoes');

function delibera_comment_reply_link_args($args, $comment, $post)
{
    $args['add_below'] = 'delibera-div-comment';
    return $args;
}
add_filter('comment_reply_link_args', 'delibera_comment_reply_link_args', 10, 3);

function delibera_duplicate_comment_id($dupe_id, $commentdata )
{
    $tipos = array();
    $tipos = apply_filters('delibera_unfilter_duplicate', $tipos);

    if (array_key_exists('delibera_comment_tipo', $_POST) && in_array($_POST['delibera_comment_tipo'], $tipos)) {
        return '';
    }
    return $dupe_id;
}
add_filter('duplicate_comment_id', 'delibera_duplicate_comment_id', 10, 2);

/**
 * Stop comment flood filter from filter votes and validations
 *
 * @param  bool $block
 * @param  int  $time_lastcomment Timestamp for last comment.
 * @param  int  $time_newcomment  Timestamp for new comment.
 * @return bool Whether comment should be blocked.
 */
function delibera_comment_flood_filter($block, $time_lastcomment, $time_newcomment)
{
    $situacao = delibera_get_situacao();
    if (is_object($situacao)) {
        if (in_array($situacao->slug, array('emvotacao', 'validacao'))) {
            return false;
        }
    }
    return $block;
}
add_filter('comment_flood_filter', 'delibera_comment_flood_filter', 10, 3);

/**
 * Return pauta situation when comment is created
 *
 * @param  integer $commentID
 * @return WP_Term|boolean
 */
function delibera_get_comment_situacao($commentID)
{
    return delibera_get_situacao_by_slug(get_comment_meta($commentID, 'delibera_situacao', true));
}

function delibera_new_comment($comment_post_ID, $comment, $delibera_comment_tipo = 'discussao', $comment_parent = 0, $errors = array())
{
    if (!array_key_exists('attachment', $_FILES)) // treat comment attachment plugins erros
    {
        $_FILES['attachment'] = array('size' => 0, 'error' => 0);
    }
    $user = wp_get_current_user();

    $comment_post_ID = isset($comment_post_ID) ? (int) $comment_post_ID : 0;
    $comment_content = (isset($comment)) ? trim($comment) : null;

    $post = get_post($comment_post_ID);

    if (empty($post->comment_status)) {
        do_action('comment_id_not_found', $comment_post_ID);
        exit;
    }

    // get_post_status() will get the parent status for attachments.
    $status = get_post_status($post);

    $status_obj = get_post_status_object($status);

    if (!comments_open($comment_post_ID)) {
        do_action('comment_closed', $comment_post_ID);
        $errors[] = __('Sorry, comments are closed for this item . ');
    } elseif ('trash' == $status) {
        do_action('comment_on_trash', $comment_post_ID);
        exit;
    } elseif (!$status_obj->public && !$status_obj->private) {
        do_action('comment_on_draft', $comment_post_ID);
        exit;
    } elseif (post_password_required($comment_post_ID)) {
        do_action('comment_on_password_protected', $comment_post_ID);
        exit;
    } else {
        do_action('pre_comment_on_post', $comment_post_ID);
    }

    // If the user is logged in
    if ($user->ID) {
        if (empty($user->display_name)) {
            $user->display_name=$user->user_login;
        }

        $comment_author       = esc_sql($user->display_name);
        $comment_author_email = esc_sql($user->user_email);
        $comment_author_url   = esc_sql($user->user_url);

    }

    $comment_approved = 1;
    $comment_type     = '';

    if ('' == $comment_content) {
        $errors[] = (__('<strong>ERROR</strong>: please type a comment . '));
    }

    $comment_parent = isset($comment_parent) ? absint($comment_parent) : 0;

    $commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID', 'comment_approved');

    $comment_id = wp_new_comment($commentdata);

    $ret             = new stdClass;
    $ret->comment_id = $comment_id;
    $ret->errors     = $errors;

    return $ret;
}
