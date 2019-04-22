<?php
/**
 * @package  K7Church
 */
namespace Inc\Controller;

use Inc\Api\Church_SettingsApi;
use Inc\Controller\Church_BaseController;
use Inc\Api\Callbacks\Church_SermonCallbacks;

class Church_SermonController extends Church_BaseController

{
	public $settings;

	public $callbacks;

	private static $sermon_trading_hour_days = array();

	public function ch_register()
	{

		$this->settings = new Church_SettingsApi();

		// $this->callbacks = new Church_SermonCallbacks();


		add_action( '', array( $this, 'ch_chortcode_sermon'));
 
        add_action('init', array($this, 'ch_Sermon_cpt')); //register sermon content type
        add_action('add_meta_boxes', array($this, 'ch_add_sermon_meta_boxes')); //add meta boxes
        add_action('save_post_sermon', array($this, 'ch_save_sermon')); //save sermon
        add_filter('the_content', array($this, 'ch_prepend_sermon_meta_to_content')); //gets our meta data and dispayed it before the content
                $this->ch_setShortcodePage();

        add_shortcode('sermon', array($this, 'ch_sermon_shortcode_output'));
    }


    //shortcode display
    public function ch_sermon_shortcode_output($atts, $content = '', $tag)
    {

        //build default arguments
        $arguments = extract(shortcode_atts(array(
                'sermon_id' => '',
                'number_of_sermon' => -1)
            , $atts, $tag) );



        //uses the main output function of the sermon class
        return $this->ch_get_sermon_output($arguments);

    }

    public function ch_setShortcodePage()
    {
        $subpage = array(
            array(
                'parent_slug' => 'edit.php?post_type=sermon',
                'page_title' => 'Shortcodes',
                'menu_title' => 'Shortcodes',
                'capability' => 'manage_options',
                'menu_slug' => 'church_sermon_shortcode',
                'callback' => array( $this->callbacks, 'ch_shortcodePage' )
            )
        );

        $this->settings->ch_addSubPages( $subpage )->ch_register();
    }


    public static function ch_Sermon_cpt()
    {

        //Labels for post type
        $labels = array(
            'name' => __('Sermon', 'k7'),
            'singular_name' => __('Sermon', 'k7'),
            'menu_name' => __('Sermons', 'k7'),
            'name_admin_bar' => __('Sermon', 'k7'),
            'add_new' => __('Add New', 'k7'),
            'add_new_item' => __('Add New Sermon', 'k7'),
            'new_item' => __('New Sermon', 'k7'),
            'edit_item' => __('Edit Sermon', 'k7'),
            'view_item' => __('View Sermon', 'k7'),
            'all_items' => __('All Sermons', 'k7'),
            'search_items' => __('Search Sermons', 'k7'),
            'parent_item_colon' => __('Parent Sermon:', 'k7'),
            'not_found' => 'No Sermons found.',
            'not_found_in_trash' => __('No Sermons found in Trash.', 'k7'),
        );
        //arguments for post type
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav' => true,
            'query_var' => true,
            'hierarchical' => true,
            'supports' => array('title', 'thumbnail', 'editor'),
            'has_archive' => true,
            'menu_position' => 20,
            'show_in_admin_bar' => true,
            'menu_icon' => 'dashicons-welcome-write-blog',
            'rewrite' => array('slug' => 'sermon', 'with_front' => 'true')
        );
        //register post type
        register_post_type('sermon', $args);
    }

    //adding meta boxes for the sermon content type*/
    public function ch_add_sermon_meta_boxes()
    {

        add_meta_box(
            'sermon_meta_box', //id
            esc_html__( 'Sermon Information', 'k7'), //name
            array($this, 'ch_sermon_meta_box_display'), //display function
            'sermon', //post type
            'normal', //sermon
            'default' //priority
        );
    }

    //display function used for our custom sermon meta box*/
    public function ch_sermon_meta_box_display($post)
    {

        //set nonce field
        wp_nonce_field('sermon_nonce', 'sermon_nonce_field');

        //collect variables
        $sermon_vers = get_post_meta($post->ID, 'sermon_vers', true);
        $sermon_author = get_post_meta($post->ID, 'sermon_author', true);
        $sermon_description = get_post_meta($post->ID, 'sermon_description', true);

        ?>
        <p><?php esc_html_e( 'Enter additional information about your sermon', 'k7');?></p>
        <div class="field-container">
            <?php
            //before main form elementst hook
            do_action('sermon_admin_form_start');
            ?>
            <div class="field">
                <label for="sermon_vers"><?php esc_html_e( 'Passages for the sermon', 'k7');?></label><br/>
                <small><?php esc_html_e( 'Biblical Passages', 'k7');?></small>
                <input type="text" name="sermon_vers" spellcheck="true" id="sermon_vers"
                       value="<?php echo $sermon_vers; ?>" autocomplete="off"/>
            </div>
            <hr>
            <div class="field">
                <label for="sermon_author"><?php esc_html_e( 'Author', 'k7');?></label><br/>
                <input type="text" name="sermon_author" id="sermon_author"
                       value="<?php echo $sermon_author; ?>" autocomplete="off"/>
            </div>
            <hr>
            <div class="field">
                <label for="sermon_description"><?php esc_html_e( 'Description', 'k7');?></label><br/>
                <textarea name="sermon_description"
                          id="sermon_description"><?php echo $sermon_description; ?></textarea>
            </div>
   
            <?php
            //after main form elementst hook
            do_action('sermon_admin_form_end');
            ?>
        </div>
        <?php

    }

    public function ch_prepend_sermon_meta_to_content($content)
    {

        global $post, $post_type;

        //display meta only on our sermon (and if its a single sermon)
        if ($post_type == 'sermon' && is_singular('sermon')) {

            //collect variables
            $sermon_id = $post->ID;
            $sermon_vers = get_post_meta($post->ID, 'sermon_vers', true);
            $sermon_author = get_post_meta($post->ID, 'sermon_author', true);
            $sermon_description = get_post_meta($post->ID, 'sermon_description', true);

            //display
            $html = '';

            $html .= '<section class="ch-col-12 meta-data">';

            //hook for outputting additional meta data (at the start of the form)
            do_action('sermon_meta_data_output_start', $sermon_id);

            $html .= '<p classs="ch-row ch-col-12"><br>';
            //phone
            if (!empty($sermon_vers)) {
                $html .= '<b>' . esc_html__( 'Passages of the sermon:', 'k7') . '</b> ' . esc_html($sermon_vers) . '</br>';
            }
            //email
            if (!empty($sermon_author)) {
                $html .= '<b>' . esc_html__( 'Author of the sermon:', 'k7') . '</b> ' . esc_html($sermon_author) . '</br>';
            }
            //description
            if (!empty($sermon_description)) {
                $html .= '<b class="teste">' . esc_html__( 'Description of the Sermon:', 'k7') . '</b> <i>' . esc_html($sermon_description) . '</i></br>';
            }
            $html .= '</p>';

         
            //hook for outputting additional meta data (at the end of the form)
            do_action('sermon_meta_data_output_end', $sermon_id);

            $html .= '</section>';
            $html .= $content;

            return $html;


        } else {
            return $content;
        }

    }

    //main function for displaying sermon (used for our shortcodes and widgets)
    public function ch_get_sermon_output($arguments = "")
    {


        //default args
        $default_args = array(
            'sermon_id' => '',
            'number_of_sermon' => -1
        );

        //update default args if we passed in new args
        if (!empty($arguments) && is_array($arguments)) {
            //go through each supplied argument
            foreach ($arguments as $arg_key => $arg_val) {
                //if this argument exists in our default argument, update its value
                if (array_key_exists($arg_key, $default_args)) {
                    $default_args[$arg_key] = $arg_val;
                }
            }
        }

        //find sermon
        $sermon_args = array(
            'post_type' => 'sermon',
            'posts_per_page' => $default_args['number_of_sermon'],
            'post_status' => 'publish'
        );
        //if we passed in a single sermon to display
        if (!empty($default_args['sermon_id'])) {
            $sermon_args['include'] = $default_args['sermon_id'];
        }

        //output
        $html = '';
        $sermon = get_posts($sermon_args);
        //if we have sermon
        if ($sermon) {
            $html .= '<article class="ch-col-12 sermon_list cf">';
            //foreach sermon
            foreach ($sermon as $sermon) {
                $html .= '<section class="ch-col-12 sermon">';
                //collect sermon data
                $sermon_id = $sermon->ID;
                $sermon_title = get_the_title($sermon_id);
                $sermon_thumbnail = get_the_post_thumbnail($sermon_id, 'thumbnail');
                $sermon_content = apply_filters('the_content', $sermon->post_content);

                if (!empty($sermon_content)) {
                    $sermon_content = strip_shortcodes(wp_trim_words($sermon_content, 40, '...'));
                }
                $sermon_permalink = get_permalink($sermon_id);
                $sermon_vers = get_post_meta($sermon_id, 'sermon_vers', true);
                $sermon_author = get_post_meta($sermon_id, 'sermon_author', true);

                //apply the filter before our main content starts
                //(lets third parties hook into the HTML output to output data)
                $html = apply_filters('sermon_before_main_content', $html);

                //title
                $html .= '<h2 class="ch-title">';
                $html .= '<a href="' . esc_url($sermon_permalink) . '" title="' . esc_attr__( 'view sermon', 'k7') . '">';
                $html .= $sermon_title;
                $html .= '</a>';
                $html .= '</h2>';


                //image & content
                if (!empty($sermon_thumbnail) || !empty($sermon_content)) {

                    if (!empty($sermon_thumbnail)) {
                        $html .= '<p class="image_content">';
                        $html .= $sermon_thumbnail;
                        $html .= '</p>';
                    }
                    if (!empty($sermon_content)) {
                        $html .= '<p>';
                        $html .= $sermon_content;
                        $html .= '</p>';
                    }

                }

                //phone & email output
                if (!empty($sermon_vers) || !empty($sermon_author)) {
                    $html .= '<p class="phone_email">';
                    if (!empty($sermon_vers)) {
                        $html .= '<b>' . esc_html__('Passages', 'k7') .': </b>' . $sermon_vers . '</br>';
                    }
                    if (!empty($sermon_author)) {
                        $html .= '<b>' . esc_html__('Author', 'k7') .': </b>' . $sermon_author;
                    }
                    $html .= '</p>';
                }

                //apply the filter after the main content, before it ends
                //(lets third parties hook into the HTML output to output data)
                $html = apply_filters('sermon_after_main_content', $html);

                //readmore
                $html .= '<a class="link" href="' . esc_url($sermon_permalink) . '" title="' . esc_attr__( 'view sermon', 'k7') . '">' . esc_html__('View Sermon', 'k7') .'</a>';
                $html .= '</section>';
            }
            $html .= '</article>';
            $html .= '<div class="cf"></div>';
        }

        return $html;
    }

    //triggered when adding or editing a sermon
    public function ch_save_sermon($post_id)
    {

        //check for nonce
        if (!isset($_POST['sermon_nonce_field'])) {
            return $post_id;
        }
        //verify nonce
        if (!wp_verify_nonce($_POST['sermon_nonce_field'], 'sermon_nonce')) {
            return $post_id;
        }
        //check for autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        //get our phone, email and description fields
        $sermon_vers = isset($_POST['sermon_vers']) ? sanitize_text_field($_POST['sermon_vers']) : '';
        $sermon_author = isset($_POST['sermon_author']) ? sanitize_text_field($_POST['sermon_author']) : '';
        $sermon_description = isset($_POST['sermon_description']) ? sanitize_textarea_field($_POST['sermon_description']) : '';

        //update phone, memil and description fields
        update_post_meta($post_id, 'sermon_vers', $sermon_vers);
        update_post_meta($post_id, 'sermon_author', $sermon_author);
        update_post_meta($post_id, 'sermon_description', $sermon_description);


        //sermon save hook
        //used so you can hook here and save additional post fields added via 'sermon_meta_data_output_end' or 'sermon_meta_data_output_end'
        do_action('sermon_admin_save', $post_id, $_POST);


    }

}