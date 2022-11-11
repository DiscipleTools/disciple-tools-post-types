<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.



add_action( 'after_setup_theme', function (){
    $custom_post_types = get_option( 'dt_custom_post_types', [] );

    foreach ( $custom_post_types as $post_type_key => $post_type ){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( $post_type_key, $post_type['single_name'] ?? $post_type_key, $post_type['plural_name'] ?? $post_type_key );
        }
    }

}, 100 );


/**
 * Class Disciple_Tools_Post_Types_Base
 * Load the core post type hooks into the Disciple.Tools system
 */
class Disciple_Tools_Post_Types_Base {

    public $post_type = null;
    public $single_name = null;
    public $plural_name = null;
    public function __construct( $post_type, $single_name, $plural_name ) {
        $this->post_type = $post_type;
        $this->single_name = $single_name;
        $this->plural_name = $plural_name;
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( "dt_capabilities", [ $this, "dt_capabilities" ], 100, 1 );
    }

    public function after_setup_theme(){
        $this->single_name = __( 'Starter', 'disciple-tools-post-types' );
        $this->plural_name = __( 'Starters', 'disciple-tools-post-types' );
    }
    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $settings['label_singular'] = __( 'Starter', 'disciple-tools-plugin-starter-template' );
            $settings['label_plural'] = __( 'Starters', 'disciple-tools-plugin-starter-template' );
        }
        return $settings;
    }

}


