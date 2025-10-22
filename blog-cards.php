<?php
/**
 * Plugin Name: Blog Cards
 * Description: نمایش مقالات به صورت کارتی و زیبا با پشتیبانی از Elementor
 * Version: 1.0
 * Author: Arad Branding
 */

if (!defined('ABSPATH')) exit;

// Load admin settings
require_once plugin_dir_path(__FILE__) . 'admin-settings.php';

class Blog_Cards_Plugin {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('blog_cards_settings', array());
        
        add_shortcode('blog_cards', array($this, 'render_blog_cards'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_head', array($this, 'custom_styles'));
        
        // Add Elementor widget support
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget'));
    }
    
    private function convert_to_persian_numbers($string) {
        $persian_numbers = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $english_numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        return str_replace($english_numbers, $persian_numbers, $string);
    }
    
    public function enqueue_styles() {
        wp_enqueue_style('blog-cards', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0');
    }
    
    public function custom_styles() {
        $css = '<style id="blog-cards-custom-styles">';
        
        // Layout
        if (!empty($this->settings['cards_gap'])) {
            $css .= '.blog-cards-container { gap: ' . intval($this->settings['cards_gap']) . 'px; }';
        }
        if (!empty($this->settings['container_max_width'])) {
            $css .= '.blog-cards-container { max-width: ' . intval($this->settings['container_max_width']) . 'px; }';
        }
        
        // Colors
        if (!empty($this->settings['card_background'])) {
            $css .= '.blog-card-inner { background-color: ' . esc_attr($this->settings['card_background']) . '; }';
        }
        if (!empty($this->settings['title_color'])) {
            $css .= '.blog-card-title a { color: ' . esc_attr($this->settings['title_color']) . '; }';
        }
        if (!empty($this->settings['excerpt_color'])) {
            $css .= '.blog-card-excerpt { color: ' . esc_attr($this->settings['excerpt_color']) . '; }';
        }
        if (!empty($this->settings['meta_color'])) {
            $css .= '.blog-card-meta { color: ' . esc_attr($this->settings['meta_color']) . '; }';
        }
        if (!empty($this->settings['button_background'])) {
            $css .= '.blog-card-button { background: ' . esc_attr($this->settings['button_background']) . '; }';
        }
        if (!empty($this->settings['button_text_color'])) {
            $css .= '.blog-card-button { color: ' . esc_attr($this->settings['button_text_color']) . '; }';
        }
        if (!empty($this->settings['category_background'])) {
            $css .= '.blog-card-category a { background: ' . esc_attr($this->settings['category_background']) . '; }';
        }
        
        // Typography
        if (!empty($this->settings['title_font_size'])) {
            $css .= '.blog-card-title { font-size: ' . intval($this->settings['title_font_size']) . 'px; }';
        }
        if (!empty($this->settings['excerpt_font_size'])) {
            $css .= '.blog-card-excerpt { font-size: ' . intval($this->settings['excerpt_font_size']) . 'px; }';
        }
        if (!empty($this->settings['meta_font_size'])) {
            $css .= '.blog-card-meta { font-size: ' . intval($this->settings['meta_font_size']) . 'px; }';
        }
        if (!empty($this->settings['button_font_size'])) {
            $css .= '.blog-card-button { font-size: ' . intval($this->settings['button_font_size']) . 'px; }';
        }
        
        // Card Details
        if (!empty($this->settings['image_height'])) {
            $css .= '.blog-card-image { height: ' . intval($this->settings['image_height']) . 'px; }';
        }
        if (!empty($this->settings['card_padding'])) {
            $css .= '.blog-card-content { padding: ' . intval($this->settings['card_padding']) . 'px; }';
        }
        if (!empty($this->settings['card_border_radius'])) {
            $css .= '.blog-card-inner { border-radius: ' . intval($this->settings['card_border_radius']) . 'px; }';
        }
        
        // Hide/Show Elements
        if (isset($this->settings['show_category']) && $this->settings['show_category'] === false) {
            $css .= '.blog-card-category { display: none !important; }';
        }
        if (isset($this->settings['show_date']) && $this->settings['show_date'] === false) {
            $css .= '.blog-card-meta { display: none !important; }';
        }
        
        $css .= '</style>';
        echo $css;
    }
    
    public function render_blog_cards($atts) {
        $default_columns = !empty($this->settings['default_columns']) ? $this->settings['default_columns'] : 5;
        $default_posts = !empty($this->settings['default_posts_per_page']) ? $this->settings['default_posts_per_page'] : 10;
        
        $atts = shortcode_atts(array(
            'posts_per_page' => $default_posts,
            'columns' => $default_columns,
            'category' => '',
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'post_status' => 'publish'
        );
        
        if (!empty($atts['category'])) {
            $args['category_name'] = $atts['category'];
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="blog-cards-container columns-' . esc_attr($atts['columns']) . '">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_card();
            }
            
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<div class="blog-cards-empty"><p>هیچ مقاله‌ای یافت نشد.</p></div>';
        }
        
        return ob_get_clean();
    }
    
    private function render_card() {
        $post_id = get_the_ID();
        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        $categories = get_the_category();
        $excerpt = get_the_excerpt();
        $date = $this->convert_to_persian_numbers(get_the_date('j F Y'));
        
        $show_category = isset($this->settings['show_category']) ? $this->settings['show_category'] : true;
        $show_date = isset($this->settings['show_date']) ? $this->settings['show_date'] : true;
        $excerpt_words = !empty($this->settings['excerpt_words']) ? $this->settings['excerpt_words'] : 12;
        
        ?>
        <article class="blog-card">
            <div class="blog-card-inner">
                <?php if ($thumbnail): ?>
                <div class="blog-card-image">
                    <a href="<?php the_permalink(); ?>">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>">
                    </a>
                    <?php if ($show_category && !empty($categories)): ?>
                    <div class="blog-card-category">
                        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>">
                            <?php echo esc_html($categories[0]->name); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="blog-card-content">
                    <?php if ($show_date): ?>
                    <div class="blog-card-meta">
                        <span class="blog-card-date">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <?php echo esc_html($date); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="blog-card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    
                    <?php if ($excerpt): ?>
                    <div class="blog-card-excerpt">
                        <?php echo wp_trim_words($excerpt, $excerpt_words, '...'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?php the_permalink(); ?>" class="blog-card-button">
                        ادامه مطلب
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </article>
        <?php
    }
    
    public function register_elementor_widget() {
        if (class_exists('Elementor\Widget_Base')) {
            require_once plugin_dir_path(__FILE__) . 'elementor-widget.php';
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Blog_Cards_Elementor_Widget());
        }
    }
}

new Blog_Cards_Plugin();

