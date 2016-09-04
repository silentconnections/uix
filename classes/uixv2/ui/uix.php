<?php
/**
 * UIX Core
 *
 * @package   uixv2
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
namespace uixv2\ui;

/**
 * UIX class
 * @package uixv2\ui
 * @author  David Cramer
 */
abstract class uix{

    /**
     * Config Structure of object
     *
     * @since 2.0.0
     * @access public
     * @var      array
     */
    public $struct = array();


    /**
     * The type of UI object
     *
     * @since 2.0.0
     * @access protected
     * @var      string
     */
    protected $type = 'uix';

    /**
     * object slug
     *
     * @since 2.0.0
     *
     * @var      string
     */
    protected $slug;
    
    /**
     * Base URL of this class
     *
     * @since 2.0.0
     * @access protected
     * @var      string
     */
    protected $url;

    /**
     * List of core object scripts ( common scripts )
     *
     * @since 2.0.0
     * @access protected
     * @var      array
     */
    protected $scripts = array();

    /**
     * List of core object styles ( common styles )
     *
     * @since 2.0.0
     * @access protected
     * @var      array
     */
    protected $styles = array();

    /**
     * prefix for min scripts
     *
     * @since 2.0.0
     * @access protected
     * @var      array
     */
    protected $debug_scripts = null;

    /**
     * prefix for min styles
     *
     * @since 2.0.0
     * @access protected
     * @var      array
     */
    protected $debug_styles = null; 

    /**
     * UIX constructor - override this to control order of initialization
     *
     * @since 2.0.0
     *
     */
    public function __construct() {
        // Set the root URL for this plugin.
        $this->set_url();

        // enable / disable debug scripts
        $this->debug_scripts();

        // enable / disable debug styles
        $this->debug_styles();

        // define then register core styles
        $this->uix_styles();

        // define then register core scripts
        $this->uix_scripts();

        // start internal actions to allow for automating post init
        $this->actions();

    }

    /**
     * setup actions and hooks - ovveride to add specific hooks. use parent::actions() to keep admin head
     *
     * @since 2.0.0
     *
     */
    protected function actions() {
        // init UIX headers
        add_action( 'admin_enqueue_scripts', array( $this, 'init' ) );
        // queue helps
        add_action( 'admin_head', array( $this, 'add_help' ) );        
    }


    /**
     * Enabled debuging of scripts
     *
     * @since 2.0.0
     *
     */
    protected function debug_scripts() {
        // detect debug scripts
        if( !defined( 'DEBUG_SCRIPTS' ) ){
            $this->debug_scripts = '.min';
        }
    }

    /**
     * Enabled debuging of styles
     *
     * @since 2.0.0
     *
     */
    protected function debug_styles() {
        // detect debug styles      
        if( !defined( 'DEBUG_STYLES' ) ){
            $this->debug_styles = '.min';
        }
    }   


    /**
     * Define core UIX styles - override to register core ( common styles for uix type )
     *
     * @since 2.0.0
     *
     */
    public function uix_styles() {
        // Initilize core styles
        $core_styles = array();
        // push to activly register styles
        $this->styles( $core_styles );

    }

    /**
     * Define core UIX scripts - override to register core ( common scripts for uix type )
     *
     * @since 2.0.0
     *
     */
    public function uix_scripts() {
        // Initilize core scripts
        $core_scripts = array();
        // push to activly register scripts
        $this->scripts( $core_scripts );
    }


    /**
     * Register the core UIX styles
     *
     * @since 2.0.0
     * @param array Array of styles to be enqueued for all objects of current instance
     */
    public function styles( array $styles ) {
        
        if( !empty( $this->struct['styles'] ) )
            $styles = array_merge( $this->struct['styles'], $styles );
        
        $this->styles = $styles;

    }


    /**
     * Register the core UIX scripts
     *
     * @since 2.0.0
     * @param array Array of scripts to be enqueued for all objects of current instance
     */
    public function scripts( array $scripts ) {

        if( !empty( $this->struct['scripts'] ) )
            $scripts = array_merge( $this->struct['scripts'], $scripts );

        $this->scripts = $scripts;

    }

    /**
     * Register the UIX objects
     *
     * @since 2.0.0
     *
     * @param array $objects object structure array
     * @return array objects|\uix all objects instances
     */
    public static function register( array $objects ) {

        $uix_objects = array();
        
        foreach( $objects as $slug => $object ){
            // get the current instance
            $caller = get_called_class();
            $uix = new $caller();

            /**
             * Filter objects to be created
             *
             * @param array $object array of UIX object structure to be registered
             * @param string $slug Of UIX object being registered
             */
            $object = apply_filters( 'uix_register_object-' . $uix->type, $object, $slug );

            $uix->slug = $slug;

            $uix->struct = $object;

            // set objects to the instance
            $uix->setup();

            // add to object list
            $uix_objects[ $slug ] = $uix;
        }

        return $uix_objects;
    }

    /**
     * Set additional the UIX objects to the current instance
     *
     * @since 2.0.0
     *
     * @param array $objects object structure array
     */
    public function setup() {}

    /**
     * initialize object and enqueue assets
     *
     * @since 2.0.0
     *
     */
    public function init() {

        // attempt to get a config
        if( !$this->is_active() ){ return; }

        /**
         * do object initilisation
         *
         * @param object current uix instance
         */
        do_action( 'uix_admin_enqueue_scripts' . $this->type, $this );

        // enqueue core scripts and styles
        $assets = array(
            'scripts' => $this->scripts,
            'styles' => $this->styles,
        );
        // enqueue core scripts and styles
        $this->enqueue( $assets, $this->type );

        // done enqueuing 
        $this->enqueue_active_assets();
    }

    /**
     * runs after assets have been enqueued
     *
     * @since 2.0.0
     *
     */
    protected function enqueue_active_assets(){}

    /**
     * Detects the root of the plugin folder and sets the URL
     *
     * @since 2.0.0
     *
     */
    public function set_url(){

        $plugins_url = plugins_url();
        $this_url = trim( substr( trailingslashit( plugin_dir_url(  __FILE__ ) ), strlen( $plugins_url ) ), '/' );
        
        if( false !== strpos( $this_url, '/') ){
            $url_path = explode('/', $this_url );
            // generic 3 path depth: classes/namespace/ui|data
            array_splice( $url_path, count( $url_path ) - 3 );
            $this_url = implode( '/', $url_path );
        }
        // setup the base URL
        $this->url = trailingslashit( $plugins_url . '/' . $this_url );
    }

    /**
     * enqueue a set of styles and scripts
     *
     * @since 2.0.0
     * @param array $set {
     *      array   $scripts array of script sources to be enqueued
     *      string  $prefix prefix for enqueue handle ( usually the object slug )
     * }object array structure
     */
    protected function enqueue( $set, $prefix ){
        // go over the set to see if it has styles or scripts

        // setup default args for array type includes
        $arguments_array = array(
            "src"       => false,
            "deps"      => array(),
            "ver"       => false,
            "in_footer" => false,
            "media"     => false
        );

        // enqueue set specific runtime styles
        if( !empty( $set['styles'] ) ){
            foreach( $set['styles'] as $style_key => $style ){
                if( is_int( $style_key ) ){
                    wp_enqueue_style( $style );
                }else{
                    if( is_array( $style ) ){
                        $args = array_merge( $arguments_array, $style );
                        wp_enqueue_style( $prefix . '-' . $script_key, $args['src'], $args['deps'], $args['ver'], $args['in_footer'] );
                    }else{
                        wp_enqueue_style( $prefix . '-' . $style_key, $style );
                    }
                }
            }
        }
        // enqueue set specific runtime scripts
        if( !empty( $set['scripts'] ) ){
            foreach( $set['scripts'] as $script_key => $script ){
                if( is_int( $script_key ) ){
                    wp_enqueue_script( $script );
                }else{
                    if( is_array( $script ) ){
                        $args = array_merge( $arguments_array, $script );
                        wp_enqueue_script( $prefix . '-' . $script_key, $args['src'], $args['deps'], $args['ver'], $args['in_footer'] );
                    }else{
                        wp_enqueue_script( $prefix . '-' . $script_key, $script );
                    }
                }
            }
        }

    }   

    /**
     * Determin if a UIX object should be active for this screen
     * Intended to be ovveridden
     * @since 2.0.0
     *
     */
    protected function is_active(){
        return false;
    }

    /**
     * Add defined contextual help to current screen
     *
     * @since 2.0.0
     */
    public function add_help(){
        
        if( ! $this->is_active() ){ return; }

        $screen = get_current_screen();
        
        if( !empty( $this->struct['help'] ) ){
            foreach( (array) $this->struct['help'] as $help_slug => $help ){

                if( is_file( $help['content'] ) && file_exists( $help['content'] ) ){
                    ob_start();
                    include $help['content'];
                    $content = ob_get_clean();
                }else{
                    $content = $help['content'];
                }

                $screen->add_help_tab( array(
                    'id'       =>   $help_slug,
                    'title'    =>   $help['title'],
                    'content'  =>   $content
                ));
            }
        }            
        // Help sidebars are optional
        if(!empty( $this->struct['help_sidebar'] ) ){
            $screen->set_help_sidebar( $this->struct['help_sidebar'] );
        }
    }

    /**
     * get the children of an object
     *
     * @since 2.0.0
     * @param string $slug registered object slug to fetch
     *
     * @return array|null array of child objects
     */
    public function children( $slug ){
        $uix = $this->get( $slug );
        var_dump( $uix );
        die;
        foreach( uixv2()->ui as $uix ){
            if( is_array( $uix ) ){
                // controls or sub types
            }else{
                foreach( $uix->objects as $object_slug => $object ){

                }
            }
            
        }
        die;
    }

    /**
     * Render the UIX object
     *
     * @since 2.0.0
     */
    abstract public function render();

}