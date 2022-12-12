<?php
/**
 * To fix https://github.com/redelivre/delibera/issues/139
 */

// PHP 5.3 and later:
namespace Delibera;

class DeliberaSiCaptcha
{
    public function __construct()
    {
        add_action('init', array($this, 'init'), 20);
    }

    public function init()
    {
        if (class_exists("siCaptcha")) {
            global $si_captcha_opt;
            global $wp_filter;
            if (is_user_logged_in()
                && is_array($si_captcha_opt)
                && array_key_exists('si_captcha_perm_level', $si_captcha_opt)
                && $si_captcha_opt['si_captcha_perm_level'] == 'read'
                && is_array($wp_filter)
                && array_key_exists('comment_form_logged_in_after', $wp_filter)
                && array_key_exists('1', $wp_filter['comment_form_logged_in_after'])
           ) {
                $si_image_captcha = '';
                foreach ($wp_filter['comment_form_logged_in_after']['1'] as $action => $callback)
                {
                    $function = $callback['function'];
                    if (is_array($function) && $function[1] == 'si_captcha_comment_form_wp3') {
                           $si_image_captcha = $function[0];
                           break;
                    }
                }

                //add_action('comment_form_logged_in_after', array(&$si_image_captcha, 'si_captcha_comment_form_wp3'), 1);
                if (!remove_action('comment_form_logged_in_after', array(&$si_image_captcha, 'si_captcha_comment_form_wp3'), 1)) {
                    //wp_die('Ops!!');
                }
                //add_filter('preprocess_comment', array(&$si_image_captcha, 'si_captcha_comment_post'), 1);
                if (!remove_action('preprocess_comment', array(&$si_image_captcha, 'si_captcha_comment_post'), 1)) {
                    //wp_die('Ops2!!');
                }
                //add_action('comment_form_after_fields', array(&$si_image_captcha, 'si_captcha_comment_form_wp3'), 1);
                if (!remove_action('comment_form_after_fields', array(&$si_image_captcha, 'si_captcha_comment_form_wp3'), 1)) {
                    //wp_die('Ops3!!');
                }
                //add_action('comment_form', array(&$si_image_captcha, 'si_captcha_comment_form'), 1);
                if (!remove_action('comment_form', array(&$si_image_captcha, 'si_captcha_comment_form'), 1)) {
                    //wp_die('Ops4!!');
                }
            }
        }
    }
}

$DeliberaSiCaptcha = new \Delibera\DeliberaSiCaptcha();
