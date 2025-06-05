<?php
/**
 * Plugin Name: WP Timeline Pro
 * Plugin URI:  https://example.com/wp-timeline-pro
 * Description: Cria timelines personalizadas e ricas em recursos com shortcodes para WordPress. Inclui opções de personalização de fontes, cores, animações, gerenciamento de itens e widget Elementor.
 * Version:     1.2.0
 * Author:      Seu Nome Aqui
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-timeline-pro
 * Domain Path: /languages
 */

// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define constantes do plugin
define( 'WTP_VERSION', '1.2.0' ); // Versão atualizada
define( 'WTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Inclui os arquivos necessários
require_once WTP_PLUGIN_DIR . 'includes/cpt.php';
require_once WTP_PLUGIN_DIR . 'includes/admin-metaboxes.php';
require_once WTP_PLUGIN_DIR . 'includes/shortcodes.php';
require_once WTP_PLUGIN_DIR . 'includes/enqueue.php';
require_once WTP_PLUGIN_DIR . 'includes/ajax-handlers.php';

/**
 * Função para registrar o widget do Elementor.
 */
function wtp_register_elementor_widget( $widgets_manager ) {
    // Verifica se o Elementor está ativo
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}

	require_once WTP_PLUGIN_DIR . 'includes/elementor-widget.php';
	$widgets_manager->register( new \WTP_Timeline_Elementor_Widget() );
}
add_action( 'elementor/widgets/register', 'wtp_register_elementor_widget' ); // Hook correto para Elementor 3.5+
// Para versões mais antigas do Elementor, o hook era 'elementor/widgets/widgets_registered'.
// 'elementor/widgets/register' é o mais atual.

/**
 * Função para rodar na ativação do plugin.
 */
function wtp_activate_plugin() {
    wtp_register_timeline_cpt();
    wtp_register_timeline_item_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wtp_activate_plugin' );

/**
 * Função para rodar na desativação do plugin.
 */
function wtp_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wtp_deactivate_plugin' );

/**
 * Carrega o text domain para tradução.
 */
function wtp_load_textdomain() {
    load_plugin_textdomain( 'wp-timeline-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wtp_load_textdomain' );

/**
 * Adiciona um link para as configurações do plugin na página de plugins.
 */
function wtp_add_settings_link( $links ) {
    $settings_link = '<a href="edit.php?post_type=wtp_timeline">' . __( 'Gerenciar Timelines', 'wp-timeline-pro' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wtp_add_settings_link' );

/**
 * Verifica se o Elementor está ativo e exibe um aviso caso não esteja.
 * Isso é útil se o widget for uma parte central do plugin.
 */
function wtp_check_elementor_active() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e( 'O plugin WP Timeline Pro requer o Elementor para funcionar corretamente com seu widget. Por favor, instale e ative o Elementor.', 'wp-timeline-pro' ); ?></p>
            </div>
            <?php
        });
    }
}
// Poderíamos chamar wtp_check_elementor_active() em 'admin_init', mas como o widget só é registrado
// se o Elementor estiver carregado, o aviso pode não ser estritamente necessário a menos que
// outras funcionalidades dependam criticamente do Elementor.
// Por enquanto, o registro condicional do widget é suficiente.

?>
