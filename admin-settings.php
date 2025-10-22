<?php
if (!defined('ABSPATH')) exit;

class Blog_Cards_Settings {
    
    private $options;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
    }
    
    public function add_plugin_page() {
        add_menu_page(
            'تنظیمات کارت‌های بلاگ',
            'کارت‌های بلاگ',
            'manage_options',
            'blog-cards-settings',
            array($this, 'create_admin_page'),
            'dashicons-grid-view',
            65
        );
    }
    
    public function enqueue_color_picker($hook) {
        if ('toplevel_page_blog-cards-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('blog-cards-admin', plugin_dir_url(__FILE__) . 'admin.js', array('wp-color-picker'), false, true);
    }
    
    public function create_admin_page() {
        $this->options = get_option('blog_cards_settings');
        ?>
        <div class="wrap" dir="rtl">
            <h1>⚙️ تنظیمات کارت‌های بلاگ</h1>
            <p>از این بخش می‌توانید ظاهر و جزئیات کارت‌های بلاگ را سفارشی‌سازی کنید.</p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('blog_cards_option_group');
                do_settings_sections('blog-cards-settings');
                submit_button('ذخیره تنظیمات');
                ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #fff; border-right: 4px solid #667eea; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3>📖 راهنمای استفاده سریع</h3>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #667eea; margin-bottom: 10px;">🎯 روش 1: ساخت صفحه بلاگ</h4>
                    <ol style="line-height: 1.8;">
                        <li>به <strong>صفحات > افزودن</strong> بروید</li>
                        <li>یک صفحه جدید با عنوان "بلاگ" ایجاد کنید</li>
                        <li>در محتوا این shortcode را وارد کنید: <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;">[blog_cards]</code></li>
                        <li>صفحه را منتشر کنید ✅</li>
                    </ol>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #667eea; margin-bottom: 10px;">🎨 روش 2: استفاده در Elementor</h4>
                    <ol style="line-height: 1.8;">
                        <li>صفحه را در Elementor باز کنید</li>
                        <li>ویجت <strong>"کارت‌های بلاگ"</strong> را جستجو کنید</li>
                        <li>آن را به صفحه drag کنید</li>
                        <li>تنظیمات را از پنل سمت چپ انجام دهید</li>
                    </ol>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <h4 style="margin-top: 0; color: #667eea;">📚 Shortcode با پارامترها:</h4>
                    <code style="background: #fff; padding: 8px 12px; border-radius: 4px; display: block; direction: ltr; text-align: left;">[blog_cards posts_per_page="12" columns="4" category="اخبار"]</code>
                    <p style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong>نکته:</strong> اگر از shortcode بدون پارامتر استفاده کنید، تنظیمات این صفحه اعمال می‌شود.
                    </p>
                </div>
                
                <div style="margin-top: 15px; padding: 12px; background: #e7f3ff; border-right: 3px solid #2196F3; border-radius: 4px;">
                    <strong>💡 نکته مهم:</strong> بعد از تغییر تنظیمات، صفحه را رفرش کنید تا تغییرات اعمال شوند.
                </div>
            </div>
        </div>
        
        <style>
            .form-table th {
                text-align: right;
                padding-right: 0;
                padding-left: 10px;
            }
            .form-table td {
                text-align: right;
            }
            .form-table input[type="number"],
            .form-table input[type="text"] {
                direction: ltr;
                text-align: left;
            }
            h2.title {
                border-right: 4px solid #667eea;
                padding-right: 15px;
                margin-top: 30px;
            }
        </style>
        <?php
    }
    
    public function page_init() {
        register_setting(
            'blog_cards_option_group',
            'blog_cards_settings',
            array($this, 'sanitize')
        );
        
        // Layout Section
        add_settings_section(
            'layout_section',
            '📐 تنظیمات چیدمان',
            array($this, 'layout_section_info'),
            'blog-cards-settings'
        );
        
        add_settings_field(
            'default_columns',
            'تعداد ستون‌های پیش‌فرض',
            array($this, 'default_columns_callback'),
            'blog-cards-settings',
            'layout_section'
        );
        
        add_settings_field(
            'default_posts_per_page',
            'تعداد مقالات در هر صفحه',
            array($this, 'default_posts_per_page_callback'),
            'blog-cards-settings',
            'layout_section'
        );
        
        add_settings_field(
            'cards_gap',
            'فاصله بین کارت‌ها (px)',
            array($this, 'cards_gap_callback'),
            'blog-cards-settings',
            'layout_section'
        );
        
        add_settings_field(
            'container_max_width',
            'حداکثر عرض کانتینر (px)',
            array($this, 'container_max_width_callback'),
            'blog-cards-settings',
            'layout_section'
        );
        
        // Colors Section
        add_settings_section(
            'colors_section',
            '🎨 تنظیمات رنگ',
            array($this, 'colors_section_info'),
            'blog-cards-settings'
        );
        
        add_settings_field(
            'card_background',
            'رنگ پس‌زمینه کارت',
            array($this, 'card_background_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'title_color',
            'رنگ عنوان',
            array($this, 'title_color_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'excerpt_color',
            'رنگ متن توضیحات',
            array($this, 'excerpt_color_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'meta_color',
            'رنگ تاریخ و اطلاعات',
            array($this, 'meta_color_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'button_background',
            'رنگ دکمه',
            array($this, 'button_background_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'button_text_color',
            'رنگ متن دکمه',
            array($this, 'button_text_color_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        add_settings_field(
            'category_background',
            'رنگ برچسب دسته‌بندی',
            array($this, 'category_background_callback'),
            'blog-cards-settings',
            'colors_section'
        );
        
        // Typography Section
        add_settings_section(
            'typography_section',
            '✍️ تنظیمات فونت',
            array($this, 'typography_section_info'),
            'blog-cards-settings'
        );
        
        add_settings_field(
            'title_font_size',
            'سایز فونت عنوان (px)',
            array($this, 'title_font_size_callback'),
            'blog-cards-settings',
            'typography_section'
        );
        
        add_settings_field(
            'excerpt_font_size',
            'سایز فونت متن (px)',
            array($this, 'excerpt_font_size_callback'),
            'blog-cards-settings',
            'typography_section'
        );
        
        add_settings_field(
            'meta_font_size',
            'سایز فونت تاریخ (px)',
            array($this, 'meta_font_size_callback'),
            'blog-cards-settings',
            'typography_section'
        );
        
        add_settings_field(
            'button_font_size',
            'سایز فونت دکمه (px)',
            array($this, 'button_font_size_callback'),
            'blog-cards-settings',
            'typography_section'
        );
        
        // Card Details Section
        add_settings_section(
            'card_details_section',
            '🃏 جزئیات کارت',
            array($this, 'card_details_section_info'),
            'blog-cards-settings'
        );
        
        add_settings_field(
            'image_height',
            'ارتفاع تصویر (px)',
            array($this, 'image_height_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
        
        add_settings_field(
            'card_padding',
            'فضای داخلی کارت (px)',
            array($this, 'card_padding_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
        
        add_settings_field(
            'card_border_radius',
            'گردی گوشه‌های کارت (px)',
            array($this, 'card_border_radius_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
        
        add_settings_field(
            'excerpt_words',
            'تعداد کلمات متن توضیحات',
            array($this, 'excerpt_words_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
        
        add_settings_field(
            'show_category',
            'نمایش دسته‌بندی',
            array($this, 'show_category_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
        
        add_settings_field(
            'show_date',
            'نمایش تاریخ',
            array($this, 'show_date_callback'),
            'blog-cards-settings',
            'card_details_section'
        );
    }
    
    public function sanitize($input) {
        $new_input = array();
        
        $new_input['default_columns'] = isset($input['default_columns']) ? absint($input['default_columns']) : 5;
        $new_input['default_posts_per_page'] = isset($input['default_posts_per_page']) ? absint($input['default_posts_per_page']) : 10;
        $new_input['cards_gap'] = isset($input['cards_gap']) ? absint($input['cards_gap']) : 30;
        $new_input['container_max_width'] = isset($input['container_max_width']) ? absint($input['container_max_width']) : 1400;
        
        $new_input['card_background'] = isset($input['card_background']) ? sanitize_hex_color($input['card_background']) : '#ffffff';
        $new_input['title_color'] = isset($input['title_color']) ? sanitize_hex_color($input['title_color']) : '#1e293b';
        $new_input['excerpt_color'] = isset($input['excerpt_color']) ? sanitize_hex_color($input['excerpt_color']) : '#475569';
        $new_input['meta_color'] = isset($input['meta_color']) ? sanitize_hex_color($input['meta_color']) : '#64748b';
        $new_input['button_background'] = isset($input['button_background']) ? sanitize_text_field($input['button_background']) : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $new_input['button_text_color'] = isset($input['button_text_color']) ? sanitize_hex_color($input['button_text_color']) : '#ffffff';
        $new_input['category_background'] = isset($input['category_background']) ? sanitize_text_field($input['category_background']) : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        
        $new_input['title_font_size'] = isset($input['title_font_size']) ? absint($input['title_font_size']) : 16;
        $new_input['excerpt_font_size'] = isset($input['excerpt_font_size']) ? absint($input['excerpt_font_size']) : 13;
        $new_input['meta_font_size'] = isset($input['meta_font_size']) ? absint($input['meta_font_size']) : 12;
        $new_input['button_font_size'] = isset($input['button_font_size']) ? absint($input['button_font_size']) : 13;
        
        $new_input['image_height'] = isset($input['image_height']) ? absint($input['image_height']) : 180;
        $new_input['card_padding'] = isset($input['card_padding']) ? absint($input['card_padding']) : 16;
        $new_input['card_border_radius'] = isset($input['card_border_radius']) ? absint($input['card_border_radius']) : 16;
        $new_input['excerpt_words'] = isset($input['excerpt_words']) ? absint($input['excerpt_words']) : 12;
        
        // Checkboxes - اگر تیک نخورده باشند، اصلاً در POST ارسال نمی‌شوند
        $new_input['show_category'] = isset($input['show_category']) ? true : false;
        $new_input['show_date'] = isset($input['show_date']) ? true : false;
        
        return $new_input;
    }
    
    // Section Callbacks
    public function layout_section_info() {
        echo '<p>تنظیمات مربوط به چیدمان و ساختار کارت‌ها</p>';
    }
    
    public function colors_section_info() {
        echo '<p>رنگ‌های مختلف کارت‌ها را تنظیم کنید</p>';
    }
    
    public function typography_section_info() {
        echo '<p>سایز فونت‌های مختلف را تنظیم کنید</p>';
    }
    
    public function card_details_section_info() {
        echo '<p>جزئیات ظاهری کارت‌ها را سفارشی کنید</p>';
    }
    
    // Field Callbacks
    public function default_columns_callback() {
        printf(
            '<select name="blog_cards_settings[default_columns]">
                <option value="1" %s>۱ ستون</option>
                <option value="2" %s>۲ ستون</option>
                <option value="3" %s>۳ ستون</option>
                <option value="4" %s>۴ ستون</option>
                <option value="5" %s>۵ ستون</option>
                <option value="6" %s>۶ ستون</option>
            </select>',
            selected($this->get_option('default_columns', 5), 1, false),
            selected($this->get_option('default_columns', 5), 2, false),
            selected($this->get_option('default_columns', 5), 3, false),
            selected($this->get_option('default_columns', 5), 4, false),
            selected($this->get_option('default_columns', 5), 5, false),
            selected($this->get_option('default_columns', 5), 6, false)
        );
    }
    
    public function default_posts_per_page_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[default_posts_per_page]" value="%s" min="1" max="100" />',
            esc_attr($this->get_option('default_posts_per_page', 10))
        );
    }
    
    public function cards_gap_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[cards_gap]" value="%s" min="0" max="100" />',
            esc_attr($this->get_option('cards_gap', 30))
        );
    }
    
    public function container_max_width_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[container_max_width]" value="%s" min="800" max="2000" />',
            esc_attr($this->get_option('container_max_width', 1400))
        );
    }
    
    public function card_background_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[card_background]" value="%s" class="color-picker" />',
            esc_attr($this->get_option('card_background', '#ffffff'))
        );
    }
    
    public function title_color_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[title_color]" value="%s" class="color-picker" />',
            esc_attr($this->get_option('title_color', '#1e293b'))
        );
    }
    
    public function excerpt_color_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[excerpt_color]" value="%s" class="color-picker" />',
            esc_attr($this->get_option('excerpt_color', '#475569'))
        );
    }
    
    public function meta_color_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[meta_color]" value="%s" class="color-picker" />',
            esc_attr($this->get_option('meta_color', '#64748b'))
        );
    }
    
    public function button_background_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[button_background]" value="%s" placeholder="linear-gradient(135deg, #667eea 0%%, #764ba2 100%%)" style="width: 400px;" /><br><small>می‌توانید از gradient استفاده کنید</small>',
            esc_attr($this->get_option('button_background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'))
        );
    }
    
    public function button_text_color_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[button_text_color]" value="%s" class="color-picker" />',
            esc_attr($this->get_option('button_text_color', '#ffffff'))
        );
    }
    
    public function category_background_callback() {
        printf(
            '<input type="text" name="blog_cards_settings[category_background]" value="%s" placeholder="linear-gradient(135deg, #667eea 0%%, #764ba2 100%%)" style="width: 400px;" /><br><small>می‌توانید از gradient استفاده کنید</small>',
            esc_attr($this->get_option('category_background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'))
        );
    }
    
    public function title_font_size_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[title_font_size]" value="%s" min="10" max="40" />',
            esc_attr($this->get_option('title_font_size', 16))
        );
    }
    
    public function excerpt_font_size_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[excerpt_font_size]" value="%s" min="10" max="30" />',
            esc_attr($this->get_option('excerpt_font_size', 13))
        );
    }
    
    public function meta_font_size_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[meta_font_size]" value="%s" min="8" max="20" />',
            esc_attr($this->get_option('meta_font_size', 12))
        );
    }
    
    public function button_font_size_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[button_font_size]" value="%s" min="10" max="20" />',
            esc_attr($this->get_option('button_font_size', 13))
        );
    }
    
    public function image_height_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[image_height]" value="%s" min="100" max="400" />',
            esc_attr($this->get_option('image_height', 180))
        );
    }
    
    public function card_padding_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[card_padding]" value="%s" min="0" max="50" />',
            esc_attr($this->get_option('card_padding', 16))
        );
    }
    
    public function card_border_radius_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[card_border_radius]" value="%s" min="0" max="50" />',
            esc_attr($this->get_option('card_border_radius', 16))
        );
    }
    
    public function excerpt_words_callback() {
        printf(
            '<input type="number" name="blog_cards_settings[excerpt_words]" value="%s" min="5" max="50" />',
            esc_attr($this->get_option('excerpt_words', 12))
        );
    }
    
    public function show_category_callback() {
        printf(
            '<label><input type="checkbox" name="blog_cards_settings[show_category]" value="1" %s /> نمایش برچسب دسته‌بندی روی تصویر</label>',
            checked($this->get_option('show_category', true), true, false)
        );
    }
    
    public function show_date_callback() {
        printf(
            '<label><input type="checkbox" name="blog_cards_settings[show_date]" value="1" %s /> نمایش تاریخ انتشار</label>',
            checked($this->get_option('show_date', true), true, false)
        );
    }
    
    private function get_option($key, $default = '') {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $default;
    }
}

if (is_admin()) {
    new Blog_Cards_Settings();
}

