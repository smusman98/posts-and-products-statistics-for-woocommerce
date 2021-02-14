<?php
/**
 * Plugin Name: Posts and Products Statics For WooCommerce
 * Plugin URI: https://www.scintelligencia.com/
 * Author: SCI Intelligencia
 * Description:
 * Version: 1.1
 * Author: Syed Muhammad Usman
 * Author URI: https://www.linkedin.com/in/syed-muhammad-usman/
 * License: GPL v2 or later
 * Stable tag: 1.1
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: WC, posts, products, views, counter, track
 * @author Syed Muhammad Usman
 * @url https://www.fiverr.com/mr_ussi
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('PostsAndProductsStats') ) {
    class PostsAndProductsStats
    {
        /**
         * PostsAndProductsStats constructor.
         * @since 1.0
         * @version 1.0
         */
        public function __construct()
        {
            $this->run();
        }

        /**
         * Runs Plugins
         * @since 1.0
         * @version 1.0
         */
        public function run()
        {
            $this->constants();
            $this->includes();
            $this->add_actions();
            $this->register_hooks();
        }

        /**
         * @param $name Name of constant
         * @param $value Value of constant
         * @since 1.0
         * @version 1.0
         */
        public function define($name, $value)
        {
            if (!defined($name))
                define($name, $value);
        }

        /**
         * Defines Constants
         * @since 1.0
         * @version 1.0
         */
        public function constants()
        {
            $this->define('PAPSFWC_VERSION', '1.1');
            $this->define('PAPSFWC_PREFIX', 'papsfwc_');
            $this->define('PAPSFWC_TEXT_DOMAIN', 'papsfwc');
            $this->define('PAPSFWC_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
            $this->define('PAPSFWC_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
        }

        /**
         * Require File
         * @since 1.0
         * @version 1.0
         */
        public function file( $required_file ) {
            if ( file_exists( $required_file ) )
                require_once $required_file;
            else
                echo 'File Not Found';
        }

        /**
         * Include files
         * @since 1.0
         * @version 1.0
         */
        public function includes()
        {

        }

        /**
         * Enqueue Styles and Scripts
         * @since 1.0
         * @version 1.0
         */
        public function enqueue_scripts()
        {
            add_action("wp_ajax_get_stats", [$this, 'get_stats']);
            add_action("wp_ajax_nopriv_get_stats", [$this, 'get_stats']);
            wp_enqueue_style(PAPSFWC_TEXT_DOMAIN . '-css', PAPSFWC_PLUGIN_DIR_URL . 'assets/css/style.css', '', PAPSFWC_VERSION);
            wp_enqueue_script(PAPSFWC_TEXT_DOMAIN . '-canvas-js', PAPSFWC_PLUGIN_DIR_URL . 'includes/libraries/canvas/canvas.js', '', PAPSFWC_VERSION);
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script(PAPSFWC_TEXT_DOMAIN . '-custom-js', PAPSFWC_PLUGIN_DIR_URL . 'assets/js/custom.js', 'jquery', PAPSFWC_VERSION);
        }

        /**
         * Adds Admin Page in Dashboard
         * @since 1.0
         * @version 1.0
         */
        public function add_menu()
        {

        }

        /**
         * Home page of Plugin
         * @since 1.0
         * @version 1.0
         */
        public function home()
        {

        }

        /**
         * Add Actions
         * @since 1.0
         * @version 1.0
         */
        public function add_actions()
        {
            add_action('init', [$this, 'enqueue_scripts']);
            add_action('admin_menu', [$this, 'add_menu']);
            add_filter( 'manage_posts_columns', array( $this, 'posts_column_views') );
            add_action( 'manage_posts_custom_column', array( $this, 'posts_custom_column_views') );
            add_filter('the_content', array( $this, 'counter' ), 10, 1);
            add_action('woocommerce_before_add_to_cart_form', array( $this, 'counter' ) );
            add_action('admin_menu', array( $this, 'add_menu_page' ) );
        }

        /**
         * Counts Views
         * @since 1.0
         * @version 1.0
         */
        public function counter( $content )
        {
            if ( is_admin() ) return $content;
            $counts = array();

            $meta_key = 'papsfwc_counter';

            $post_id = get_the_ID();

            $count = get_post_meta( $post_id, $meta_key, true );

            $unserialized_count = unserialize( $count );

            $current_time = round(microtime(true) * 1000);

            $current_date = date( 'd-m-Y', round( $current_time/1000 ) );

            $saved_date = '';

            $total_counts = $unserialized_count['total_counts'];

            unset( $unserialized_count['total_counts']);

            if (is_array( $unserialized_count ) && !empty( $unserialized_count ))
            {
                foreach ( $unserialized_count as $time => $key )
                {
                    $saved_date = date( 'd-m-Y', round( $time/1000 ) );

                    if ( $saved_date == $current_date )
                    {
                        $key = $key + 1;

                        $unserialized_count[$time] = $key;
                    }
                    else
                    {
                        $unserialized_count[$current_time] = 1;
                    }
                }
            }
            else
            {
                $unserialized_count[$current_time] = 1;
            }

            $unserialized_count['total_counts'] = $total_counts + 1;

            $serialized_count = serialize( $unserialized_count );

            update_post_meta( $post_id, $meta_key, $serialized_count );

            return $content;
        }

        /**
         * Get the Views
         * @return string
         */
        public function get_post_view()
        {
            $count = get_post_meta( get_the_ID(), 'papsfwc_counter', true );

            $unserialized = unserialize( $count );

            $total_counts = $unserialized['total_counts'];
            if ( $total_counts > 0 )
                return "$total_counts Views";
            else
                return 'No Views Yet';
        }

        /**
         * Add Views Column in Table
         * @param $columns
         * @return mixed
         * @since 1.0
         * @version 1.0
         */
        public function posts_column_views( $columns )
        {
            $columns['post_views'] = 'Views';
            return $columns;
        }

        /**
         * Add Counter Column in Table
         * @param $column
         * @since 1.0
         * @version 1.0
         */
        public function  posts_custom_column_views( $column )
        {
            if ( $column === 'post_views') {
                echo $this->get_post_view();
            }
        }

        /**
         * Returns all the ids of post
         * @param $type
         * @param int $number_of_posts
         * @return array
         * @version 1.0
         * @since 1.0
         */
        public function get_posts_ids( $type, $number_of_posts = -1 )
        {
            $ids = array();

            $args = array(
                'numberposts' => $number_of_posts,
                'category' => 0,
                'orderby' => 'date',
                'order' => 'DESC',
                'include' => array(),
                'exclude' => array(),
                'meta_key' => '',
                'meta_value' =>'',
                'post_type' => $type,
                'suppress_filters' => true
            );

            $posts = get_posts( $args );

            foreach ( $posts as $post )
            {
                $ids[] = $post->ID;
            }

            wp_reset_query();

            return $ids;
        }

        /**
         * Get highest visits counts
         * @param $post_type
         * @param $qty
         * @since 1.0
         * @version 1.0
         */
        public function get_highest_visit( $post_type, $qty = -1 )
        {
            $ids = $this->get_posts_ids( $post_type, $qty );

            foreach ( $ids as $id )
            {
                echo $id . '<br/>';
            }
        }

        /**
         * Adds Menu Page
         * @since 1.0
         * @version 1.0
         */
        public function add_menu_page()
        {
            add_menu_page(
                __( 'Posts And Products Statistics For WooCommerce', 'papsfw' ),
                'Statistics',
                'manage_options',
                'papsfw-main',
                array( $this, 'papsfw_main' ),
                plugins_url( 'myplugin/images/icon.png' )
            );
        }

        /**
         * Get statistics
         */
        public function get_stats()
        {
            $counter = 1;

            $inner_array = array();

            $main_array = array();//

            $post_type = sanitize_text_field( $_POST['type'] );

            if ( empty( $post_type ) )
            {
                echo '<h1>' . __( 'Select Post Type From Drop Down', 'papsfwc' ) . '</h1>'; die;
            }

            $post_ids = $this->get_posts_ids( $post_type );

            foreach ( $post_ids as $id )
            {
                $meta = get_post_meta( $id, 'papsfwc_counter', true );

                if (!empty( $meta ))
                {
                    $views = unserialize( $meta );

                    $total_counts = $views['total_counts'];

                    unset( $views['total_counts'] );

                    foreach ( $views as $key => $value)
                    {
                        $inner_array['x'] = $key;

                        $inner_array['y'] = $value;

                        $main_array[] = $inner_array;
                    }
                    ?>
                    <script>
                        function graph_plot() {

                            var chart = new CanvasJS.Chart("chartContainer-<?php echo $id;?>", {
                                animationEnabled: true,
                                theme: "light2",
                                title:{
                                    text: "<?php echo get_the_title( $id ) ?>"
                                },
                                axisX: {
                                    valueFormatString: "DD MMM YYYY"
                                },
                                axisY: {
                                    title: "Total Number of Visits",
                                    includeZero: true,
                                    maximum: <?php echo $total_counts; ?>
                                },
                                data: [{
                                    type: "splineArea",
                                    color: "#6599FF",
                                    xValueType: "dateTime",
                                    xValueFormatString: "DD MMM YYYY",
                                    yValueFormatString: "#,##0 Visits",
                                    dataPoints: <?php echo json_encode( $main_array ); ?>
                                }]
                            });

                            chart.render();

                        }
                        graph_plot();
                    </script>
                    <div id="chartContainer-<?php echo $id;?>" style="height: 200px; width: 100%; padding: 10px 0;margin: 50px 0;"></div>
                    <?php
                    unset( $main_array );
                    $counter++;
                }
            }
            echo '<div style="clear: both"></div>';

            die;
        }

        /**
         * Renders papsfw-main page
         * @since 1.0
         * @version 1.0
         */
        public function papsfw_main()
        {
            ?>
            <div class="wrap">
                <h1><?php _e( 'Posts And Products Statistics For WooCommerce', 'papsfwc' ); ?></h1>
                <form action="" id="get-stats" method="post">
                    <label for="type">Type</label>
                    <select name="type" id="type" required>
                        <option value=""><?php _e( 'Select Post Type', 'papsfwc' ); ?></option>
                        <option value="post"><?php _e( 'Posts', 'papsfwc' ); ?></option>
                        <option value="product"><?php _e( 'WooCommerce Products', 'papsfwc' ); ?></option>
                    </select>
                    <input type="submit" class="button button-primary" name="submit" value="Submit">
                    <div style="text-align: center">
                        <img src="<?php echo PAPSFWC_PLUGIN_DIR_URL . '/assets/images/loader.gif'?>" style="display:none" id="loader" alt="">
                    </div>
                </form>
                <div class="graph">
                    <?php
                    //$this->get_highest_visit( 'post' );
                    //$this->get_highest_visit( 'product' );
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Register Activation, Deactivation and Uninstall Hooks
         * @since 1.0
         * @version 1.0
         */
        public function register_hooks()
        {
            register_activation_hook( __FILE__, [$this, 'activate'] );
            register_deactivation_hook( __FILE__, [$this, 'deactivate'] );
            register_uninstall_hook(__FILE__, 'papsfwc_function_to_run');
        }

        /**
         * Runs on Plugin's activation
         * @since 1.0
         * @version 1.0
         */
        public function activate()
        {

        }

        /**
         * Runs on Plugin's Deactivation
         * @since 1.0
         * @version 1.0
         */
        public function deactivate()
        {

        }
    }
}

new PostsAndProductsStats();
