<?php
if (!defined('ABSPATH')) exit;

class Blog_Cards_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'blog_cards';
    }
    
    public function get_title() {
        return 'کارت‌های بلاگ';
    }
    
    public function get_icon() {
        return 'eicon-posts-grid';
    }
    
    public function get_categories() {
        return ['general'];
    }
    
    protected function _register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'تنظیمات',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'posts_per_page',
            [
                'label' => 'تعداد مقالات',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 9,
                'min' => 1,
                'max' => 100,
            ]
        );
        
        $this->add_control(
            'columns',
            [
                'label' => 'تعداد ستون‌ها',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '5',
                'options' => [
                    '1' => '۱ ستون',
                    '2' => '۲ ستون',
                    '3' => '۳ ستون',
                    '4' => '۴ ستون',
                    '5' => '۵ ستون',
                    '6' => '۶ ستون',
                ],
            ]
        );
        
        $categories = get_categories();
        $category_options = ['0' => 'همه دسته‌بندی‌ها'];
        foreach ($categories as $category) {
            $category_options[$category->slug] = $category->name;
        }
        
        $this->add_control(
            'category',
            [
                'label' => 'دسته‌بندی',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '0',
                'options' => $category_options,
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => 'مرتب‌سازی بر اساس',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => 'تاریخ',
                    'title' => 'عنوان',
                    'rand' => 'تصادفی',
                    'comment_count' => 'تعداد نظرات',
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => 'ترتیب',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => 'نزولی',
                    'ASC' => 'صعودی',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => 'استایل',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'card_background',
            [
                'label' => 'رنگ پس‌زمینه کارت',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-card-inner' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => 'رنگ عنوان',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-card-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'excerpt_color',
            [
                'label' => 'رنگ متن',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-card-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_background',
            [
                'label' => 'رنگ دکمه',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-card-button' => 'background: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $category = ($settings['category'] && $settings['category'] !== '0') ? $settings['category'] : '';
        
        echo do_shortcode('[blog_cards posts_per_page="' . esc_attr($settings['posts_per_page']) . '" columns="' . esc_attr($settings['columns']) . '" category="' . esc_attr($category) . '" orderby="' . esc_attr($settings['orderby']) . '" order="' . esc_attr($settings['order']) . '"]');
    }
}

