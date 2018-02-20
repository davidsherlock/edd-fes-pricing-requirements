<?php
/**
 * Plugin Name:     Easy Digital Downloads - FES Pricing Restrictions
 * Plugin URI:      https://wordpress.org/plugins/edd-fes-pricing-restrictions/
 * Description:     Force minimum and maximum pricing requirements on FES product submissions.
 * Version:         1.0.0
 * Author:          Sell Comet
 * Author URI:      https://sellcomet.com
 * Text Domain:     edd-fes-pricing-restrictions
 *
 * @package         EDD\FES_Pricing_Restrictions
 * @author          Sell Comet
 * @copyright       Copyright (c) Sell Comet
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'EDD_FES_Pricing_Restrictions' ) ) {

    /**
     * Main EDD_FES_Pricing_Restrictions class
     *
     * @since       1.0.0
     */
    class EDD_FES_Pricing_Restrictions {

        /**
         * @var         EDD_FES_Pricing_Restrictions $instance The one true EDD_FES_Pricing_Restrictions
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_FES_Pricing_Restrictions
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_FES_Pricing_Restrictions();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_FES_PRICING_RESTRICTIONS_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_FES_PRICING_RESTRICTIONS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_FES_PRICING_RESTRICTIONS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Register the "Pricing Restrictions" menu item within the FES tab
            add_filter( 'edd_settings_sections', array( $this, 'add_pricing_restrictions_section' ), 1, 1 );

            // Register the "Pricing Restrictions" FES menu settings
            add_filter( 'edd_registered_settings', array( $this, 'register_pricing_restrictions_settings' ), 1, 1 );

            // Validate the pricing thresholds
            add_filter( 'fes_validate_multiple_pricing_field', array( $this, 'validate_pricing' ), 1, 5 );
        }


        /**
    	 * Register the "Pricing Restrictions" menu item within the FES tab
    	 *
         * @access      public
         * @since       1.0.0
         * @param       array $sections The existing EDD FES section array
         * @return      array $sections The modified EDD FES section array
    	 */
        public function add_pricing_restrictions_section( $sections ) {
            $sections['fes']['pricing_restrictions'] = __( 'Pricing Restrictions', 'edd_fes' );
            return $sections;
        }


        /**
    	 * Register the "Pricing Restrictions" FES menu settings
    	 *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD FES settings array
         * @return      array $settings The modified EDD FES settings array
    	 */
    	public function register_pricing_restrictions_settings( $settings ) {
    		$settings['fes']['pricing_restrictions'] = array(
                'fes-pricing-restrictions-min-product-price' => array(
                    'id'      => 'edd_fes_pricing_restrictions_min',
                    'name'    => __( 'Minimum Price', 'edd-fes-pricing-restrictions' ),
                    'desc'    => __( 'Enter the minimum allowed price for product submissions.', 'edd-fes-pricing-restrictions' ),
                    'type'    => 'text',
                    'size'    => 'small',
                ),
                'fes-pricing-restrictions-max-product-price' => array(
                    'id'      => 'edd_fes_pricing_restrictions_max',
                    'name'    => __( 'Maximum Price', 'edd-fes-pricing-restrictions' ),
                    'desc'    => __( 'Enter the maximum allowed price for product submissions.', 'edd-fes-pricing-restrictions' ),
                    'type'    => 'text',
                    'size'    => 'small',
                ),
                'fes-pricing-restrictions-disallow-free-submissions' => array(
                    'id'      => 'edd_fes_pricing_restrictions_disallow_free',
                    'name'    => __('Disallow Free Submissions', 'edd-fes-pricing-restrictions' ),
                    'desc'    => __('If checked free product submissions will not be permitted.', 'edd-fes-pricing-restrictions'),
                    'type'    => 'checkbox',
                ),
    		);

            return $settings;
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_FES_PRICING_RESTRICTIONS_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_fes_pricing_restrictions_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-fes-pricing-restrictions' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-fes-pricing-restrictions', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-fes-pricing-restrictions/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-plugin-name/ folder
                load_textdomain( 'edd-fes-pricing-restrictions', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
                load_textdomain( 'edd-fes-pricing-restrictions', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-fes-pricing-restrictions', false, $lang_dir );
            }
        }

        /**
         * Validate the product submission pricing
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function validate_pricing( $return_value, $values, $name, $save_id, $user_id ) {
        	if ( ! empty( $values[ 'option' ] ) ) {
        		if ( is_array( $values[ 'option' ] ) ) {

                    $disallow_free  = (bool) edd_get_option( 'edd_fes_pricing_restrictions_disallow_free', false );
                    $min_price      = edd_get_option( 'edd_fes_pricing_restrictions_min', false );
                    $max_price      = edd_get_option( 'edd_fes_pricing_restrictions_max', false );

        			foreach( $values[ 'option' ] as $key => $option  ) {
        				if ( isset( $option[ 'price' ] ) ) {

                            // Check free product (0.00) submissions if "Disallow Free Submissions" is checked
                            if ( $disallow_free ) {
                                if ( $values[ 'option' ][ $key ]['price'] == 0 ) {
                                  $return_value = sprintf( __( 'Free (%s) product submissions are not permitted.', 'edd-fes-pricing-restrictions' ), edd_currency_filter( edd_format_amount( 0.00 ) ) );
                                  break;
                                }
                            }

                            // Check minimum product price
                            if ( $min_price ) {
                                if ( $values[ 'option' ][ $key ]['price'] < (float) $min_price ) {
                                  $return_value = sprintf( __( 'Price must be greater than %s.', 'edd-fes-pricing-restrictions' ), edd_currency_filter( edd_format_amount( $min_price ) ) );
                                  break;
                                }
                            }

                            // Check maximum product price
                            if ( $max_price ) {
                                if ( $values[ 'option' ][ $key ]['price'] > (float) $max_price ) {
                                  $return_value = sprintf( __( 'Price must be less than %s.', 'edd-fes-pricing-restrictions' ), edd_currency_filter( edd_format_amount( $max_price ) ) );
                                  break;
                                }
                            }

                        }
        			}
        		}
        	}

        	return $return_value;
        }

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_FES_Pricing_Restrictions
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_FES_Pricing_Restrictions The one true EDD_FES_Pricing_Restrictions
 */
function EDD_FES_Pricing_Restrictions_load() {
    if ( ! class_exists( 'Easy_Digital_Downloads' ) || ! class_exists( 'EDD_Front_End_Submissions' ) ) {
        if ( ! class_exists( 'EDD_Extension_Activation' ) || ! class_exists( 'EDD_FES_Activation' ) ) {
          require_once 'includes/classes/class-activation.php';
        }

        // Easy Digital Downloads activation
        if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
            $edd_activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
            $edd_activation = $edd_activation->run();
        }

        // Commissions activation
        if ( ! class_exists( 'EDD_Front_End_Submissions' ) ) {
            $edd_fes_activation = new EDD_FES_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
            $edd_fes_activation = $edd_fes_activation->run();
        }

    } else {
        return EDD_FES_Pricing_Restrictions::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_FES_Pricing_Restrictions_load' );
