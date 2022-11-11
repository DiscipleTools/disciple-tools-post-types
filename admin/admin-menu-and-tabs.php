<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Post_Types_Menu
 */
class Disciple_Tools_Post_Types_Menu {

    public $token = 'disciple_tools_post_types';
    public $page_title = 'Post Types';

    private static $_instance = null;

    /**
     * Disciple_Tools_Post_Types_Menu Instance
     *
     * Ensures only one instance of Disciple_Tools_Post_Types_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Post_Types_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'register_menu' ) );

        $this->page_title = __( 'Post Types', 'disciple-tools-post-types' );
    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        $this->page_title = __( 'Post Types', 'disciple-tools-post-types' );

        add_submenu_page( 'dt_extensions', $this->page_title, $this->page_title, 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple.Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple.Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->page_title ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'general' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">General</a>
                <a href="<?php echo esc_attr( $link ) . 'second' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'second' ) ? 'nav-tab-active' : '' ); ?>">Second</a>
            </h2>

            <?php
            switch ( $tab ) {
                case 'general':
                    $object = new Disciple_Tools_Post_Types_Tab_General();
                    $object->process_form();
                    $object->content();
                    break;
                case 'second':
                    $object = new Disciple_Tools_Post_Types_Tab_Second();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }
}
Disciple_Tools_Post_Types_Menu::instance();

/**
 * Class Disciple_Tools_Post_Types_Tab_General
 */
class Disciple_Tools_Post_Types_Tab_General {

    public function process_form(){

        if ( isset( $_POST['post_type_create_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['post_type_create_nonce'] ) ), 'post_type_create' ) ){
            $post_submission = dt_recursive_sanitize_array( $_POST );
            if ( isset( $_POST['add_new_post_type'] ) ){
                $custom_post_types = get_option( 'dt_custom_post_types', [] );
                if ( !empty( $post_submission['new_post_type_key'] ) && !empty( $post_submission['new_post_type_singular'] ) && !empty( $post_submission['new_post_type_plural'] ) ){
                    $post_type_key = dt_create_field_key( $post_submission['new_post_type_key'] );
                    if ( !isset( $custom_post_types[$post_type_key] ) ){
                        $custom_post_types[$post_type_key] = [
                            'single_name' => $post_submission['new_post_type_singular'],
                            'plural_name' => $post_submission['new_post_type_plural'],
                        ];
                        update_option( 'dt_custom_post_types', $custom_post_types );
                        return true;
                    }
                }
            }
            if ( isset( $_POST['delete_custom_post_type'] ) ){
                $custom_post_types = get_option( 'dt_custom_post_types', [] );
                if ( isset( $custom_post_types[$post_submission['delete_custom_post_type']] ) ){
                    unset( $custom_post_types[$post_submission['delete_custom_post_type']] );
                    update_option( 'dt_custom_post_types', $custom_post_types );
                    return true;
                }
            }
        }
    }

    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        $custom_post_types = get_option( 'dt_custom_post_types', [] );
        $post_types = DT_Posts::get_post_types();
        $post_types_settings = [];
        foreach ( $post_types as $post_type ){
            $post_types_settings[$post_type] = DT_Posts::get_post_settings( $post_type, false );
        }
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Custom Post Types</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="post_type_create_nonce" id="post_type_create_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_create' ) ) ?>" />
                            <table>
                                <thead><tr>
                                    <th>Post Type</th>
                                    <th>Singular</th>
                                    <th>Plural</th>
                                    <th>Custom</th>
                                    <th>Delete</th>
                                </tr></thead>
                                <?php foreach ( $post_types_settings as $post_type_key => $post_type ) :
                                    $is_custom_post_type = isset( $custom_post_types[$post_type_key] );
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html( $post_type_key ); ?></td>
                                        <td><?php echo esc_html( $post_type['label_singular'] ?? $post_type_key ); ?></td>
                                        <td><?php echo esc_html( $post_type['label_plural'] ?? $post_type_key ); ?></td>
                                        <td><?php echo esc_html( $is_custom_post_type ? 'Yes' : '' ); ?></td>
                                        <td>
                                            <?php if ( $is_custom_post_type ) :?>
                                                <button type="submit" class="button button-primary" name="delete_custom_post_type" value="<?php echo esc_html( $post_type_key ); ?>">Delete</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="post_type_create_nonce" id="post_type_create_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_create' ) ) ?>" />
                            <h3>Add new</h3>
                            <label style="display: block">
                                New post type key (plural)<br>
                                <input type="text" name="new_post_type_key" required>
                            </label>
                            <label style="display: block">
                                Singular Label<br>
                                <input type="text" name="new_post_type_singular" required>
                            </label>
                            <label style="display: block">
                                Plural Label<br>
                                <input type="text" name="new_post_type_plural" required>
                            </label>
                            <br>
                            <button type="submit" name="add_new_post_type" class="button">Submit</button>
                            <p>After submit please refresh the page again.</p>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}


/**
 * Class Disciple_Tools_Post_Types_Tab_Second
 */
class Disciple_Tools_Post_Types_Tab_Second {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Header</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Content
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

