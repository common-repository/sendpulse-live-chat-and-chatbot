<?php
/**
 * SendPulse - Live Chat and Chatbot
 *
 * Free live chat and chatbot plugin by SendPulse helps you communicate with users on your website to motivate them to convert, and to support customers after purchase.
 * Create automated flows to introduce your brand and products, answer users’ questions, motivate users to make purchases, and accept payments right in your live chat.
 * ChatGPT integration, autoreplies and an Android/iOS mobile app will help you talk to customers even when you are not at your computer.
 *
 * Plugin Name: SendPulse - Live Chat and Chatbot
 * Description: Free live chat and chatbot plugin by SendPulse. Add live chats to your WordPress-powered website seamlessly to engage your site visitors and help solve their issues in real time.
 * Turn website visitors into customers with live chat agents, automated flows and ChatGPT.
 * Version: 1.1
 * Author: SendPulse
 * Author URI: https://sendpulse.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sendpulse-livechat
 * Domain Path: languages
*/

defined('ABSPATH') || exit;
define('SENDPULSE_LIVECHAT_PLUGIN_VERSION', '1.0');
define('SENDPULSE_LIVECHAT_PLUGIN_TEXTDOMAIN', 'sendpulse-livechat');
define('SENDPULSE_LIVECHAT_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('SENDPULSE_LIVECHAT_PLUGIN_BASE_DIR', plugin_dir_path(__FILE__));

function sendpulse_livechat_load_textdomain() {
    load_plugin_textdomain(SENDPULSE_LIVECHAT_PLUGIN_TEXTDOMAIN, false, dirname(SENDPULSE_LIVECHAT_PLUGIN_BASE_NAME) . '/languages/');
}

add_action('plugins_loaded', 'sendpulse_livechat_load_textdomain');

/**
 * Add a menu item in the Settings menu
 */
function sendpulse_livechat_menu() {
    add_options_page(
        'SendPulse LiveChat Settings',
        __('SendPulse Live Chat', 'sendpulse-livechat'),
        'manage_options',
        'sendpulse-livechat-settings',
        'sendpulse_livechat_settings_page'
    );
}

add_action('admin_menu', 'sendpulse_livechat_menu');

/**
 * Create the settings page
 */
function sendpulse_livechat_settings_page() {
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Setting up SendPulse Live Chat Plugin', 'sendpulse-livechat');?></h2>
        <form method="post" action="options.php">
            <?php
                settings_fields('sendpulse_livechat_options');
                do_settings_sections('sendpulse-livechat-settings');
                submit_button();
            ?>
        </form>

        <?php
        // Check if SendPulse LiveChat inline JavaScript code is blank
        $inline_js = get_option('sendpulse_livechat_inline_js');
        if (empty($inline_js)) {
            ?>
            <div class="notice notice-info" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="flex: 1;">
                    <h3><?php esc_html_e('Connect your live chat created with SendPulse', 'sendpulse-livechat');?></h3>
                    <p><?php esc_html_e('Instantly chat with visitors on your WordPress-based website.', 'sendpulse-livechat');?></p>
                    <p>
                        <?php
                            printf(
                                /* translators: %s: URL */
                                wp_kses(
                                    sprintf(
                                        'Add a new live chat created with SendPulse to your site or <a href="%1$s" target="_blank">connect the existing one</a>. If you don\'t have a SendPulse account, <a href="%2$s" target="_blank">sign up for free</a> to start using the plugin.',
                                        esc_url('https://login.sendpulse.com/messengers/'),
                                        esc_url('https://sendpulse.com/register')
                                    ),
                                    array(
                                        'a' => array(
                                            'href'  => array(),
                                            'target' => array(),
                                        ),
                                    )
                                ),
                                'sendpulse-livechat'
                            );
                        ?>
                    </p>
                </div>

                <?php
                    printf(
                        /* translators: %s: URL */
                        wp_kses(
                            sprintf(
                                '<a class="button button-primary" href="%1$s" target="_blank">%2$s</a>',
                                esc_url('https://login.sendpulse.com/messengers/connect/live-chat/'),
                                esc_html('Connect live chat','sendpulse-livechat')
                            ),
                            array(
                                'a' => array(
                                    'class' => array(),
                                    'href'  => array(),
                                    'target' => array(),
                                ),
                            )
                        ),
                        'sendpulse-livechat'
                    );
                ?>

            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Register settings and fields
 */
function sendpulse_livechat_register_settings() {
    register_setting('sendpulse_livechat_options', 'sendpulse_livechat_inline_js', 'sendpulse_livechat_sanitize_textarea_field');

    add_settings_section(
        'sendpulse_livechat_js_section',
        __('SendPulse LiveChat', 'sendpulse-livechat'),
        'sendpulse_livechat_js_section_callback',
        'sendpulse-livechat-settings',
        array(
            'after_section' => sprintf(__('For more information about live chats, check the <a href="%s" target="_blank">SendPulse Knowledge Base</a>.', 'sendpulse-livechat'),
                'https://sendpulse.com/knowledge-base/chatbot/livechat'
            ),
        )
    );

    add_settings_field(
        'sendpulse_livechat_inline_js',
        __('Live chat installation code', 'sendpulse-livechat'),
        'sendpulse_livechat_inline_js_callback',
        'sendpulse-livechat-settings',
        'sendpulse_livechat_js_section'
    );
}

add_action('admin_init', 'sendpulse_livechat_register_settings');

function sendpulse_livechat_js_section_callback() {
    echo '<div>' . esc_html__('To connect your live chat, copy its installation code from your SendPulse account. Once pasted and saved, it will be added before the closing </body> tag.', 'sendpulse-livechat') . '</div>';
    echo '<div>' . esc_html(__('After successful installation, your live chat will appear on your website. To customize your widget appearance, go to your SendPulse account.', 'sendpulse-livechat')) . '</div>';
}

/**
 * Callback for inline JavaScript code field
 */
function sendpulse_livechat_inline_js_callback() {
    $inline_js = get_option('sendpulse_livechat_inline_js');
    if(!empty($inline_js)) {
        $decoded_js = html_entity_decode($inline_js);
        echo '<textarea name="sendpulse_livechat_inline_js" rows="5" cols="50">' . esc_textarea($decoded_js) . '</textarea>';
    } else {
        echo '<textarea name="sendpulse_livechat_inline_js" rows="5" cols="50">' . esc_textarea($inline_js) . '</textarea>';
    }
}

/**
 * Sanitization callback for inline JavaScript code
 */
function sendpulse_livechat_sanitize_textarea_field($input) {
    return esc_js($input);
}

function sendpulse_livechat_inline_script() {
    $inline_js = get_option('sendpulse_livechat_inline_js');
    if (!empty($inline_js)) {
        $decoded_js = html_entity_decode($inline_js);

        // Define allowed HTML tags and attributes for the script content
        $allowed_tags = array(
            'script' => array(
                'src' => array(),
                'data-live-chat-id' => array(),
                'async' => array(),
            ),
        );

        // Sanitize the script content
        $sanitized_script = wp_kses($decoded_js, $allowed_tags);

        // Return the sanitized script tag
        return $sanitized_script;
    }
    return ''; // Return an empty string if no script is available
}

function sendpulse_livechat_output_inline_script() {
    add_action('wp_footer', 'sendpulse_livechat_output_inline_script_callback');
}

function sendpulse_livechat_output_inline_script_callback() {
    echo sendpulse_livechat_inline_script();
}

add_action('init', 'sendpulse_livechat_output_inline_script');