<?php
/**
 * Activation handler
 *
 * @package     EDD\ActivationHandler
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * EDD Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class EDD_Extension_Activation {

    public $plugin_name, $plugin_path, $plugin_file, $has_edd, $edd_base;

    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function __construct( $plugin_path, $plugin_file ) {
        // We need plugin.php!
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugins = get_plugins();

        // Set plugin directory
        $plugin_path = array_filter( explode( '/', $plugin_path ) );
        $this->plugin_path = end( $plugin_path );

        // Set plugin file
        $this->plugin_file = $plugin_file;

        // Set plugin name
        if( isset( $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] ) ) {
            $this->plugin_name = str_replace( 'Easy Digital Downloads - ', '', $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] );
        } else {
            $this->plugin_name = __( 'This plugin', 'edd' );
        }

        // Is EDD installed?
        foreach( $plugins as $plugin_path => $plugin ) {
            if( $plugin['Name'] == 'Easy Digital Downloads' ) {
                $this->has_edd = true;
                $this->edd_base = $plugin_path;
                break;
            }
        }
    }


    /**
     * Process plugin deactivation
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function run() {
        // Display notice
        add_action( 'admin_notices', array( $this, 'missing_edd_notice' ) );
    }


    /**
     * Display notice if EDD isn't installed
     *
     * @access      public
     * @since       1.0.0
     * @return      string The notice to display
     */
    public function missing_edd_notice() {
        if( $this->has_edd ) {
            $url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->edd_base ), 'activate-plugin_' . $this->edd_base ) );
            $link = '<a href="' . $url . '">' . __( 'activate it', 'edd-commission-thresholds' ) . '</a>';
        } else {
            $url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=easy-digital-downloads' ), 'install-plugin_easy-digital-downloads' ) );
            $link = '<a href="' . $url . '">' . __( 'install it', 'edd-commission-thresholds' ) . '</a>';
        }

        echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires Easy Digital Downloads! Please %s to continue!', 'edd-commission-thresholds' ), $link ) . '</p></div>';
    }
}


/**
 * EDD Commission Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class EDD_FES_Activation {
    public $plugin_name, $plugin_path, $plugin_file, $has_edd_fes;
    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function __construct( $plugin_path, $plugin_file ) {
        // We need plugin.php!
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $plugins = get_plugins();
        // Set plugin directory
        $plugin_path = array_filter( explode( '/', $plugin_path ) );
        $this->plugin_path = end( $plugin_path );
        // Set plugin file
        $this->plugin_file = $plugin_file;
        // Set plugin name
        if ( isset( $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] ) ) {
            $this->plugin_name = str_replace( 'Easy Digital Downloads - ', '', $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] );
        } else {
            $this->plugin_name = __( 'This plugin', 'edd' );
        }
        // Is EDD installed?
        foreach ( $plugins as $plugin_path => $plugin ) {
            if ( $plugin['Name'] == 'Easy Digital Downloads - Frontend Submissions' ) {
                $this->has_edd_fes = true;
                break;
            }
        }
    }


    /**
     * Process plugin deactivation
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function run() {
        // Display notice
        add_action( 'admin_notices', array( $this, 'missing_fes_notice' ) );
    }


    /**
     * Display notice if EDD isn't installed
     *
     * @access      public
     * @since       1.0.0
     * @return      string The notice to display
     */
    public function missing_fes_notice() {
        if ( $this->has_edd_fes ) {
            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires %sEDD Frontend Submissions%s. Please activate it to continue.', 'edd-commission-thresholds' ), '<a href="https://easydigitaldownloads.com/downloads/frontend-submissions/" title="EDD Frontend Submissions" target="_blank">', '</a>' ) . '</p></div>';
        } else {
            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires %sEDD Frontend Submissions%s. Please install it to continue.', 'edd-commission-thresholds' ), '<a href="https://easydigitaldownloads.com/downloads/frontend-submissions/" title="EDD Frontend Submissions" target="_blank">', '</a>' ) . '</p></div>';
        }
    }
}