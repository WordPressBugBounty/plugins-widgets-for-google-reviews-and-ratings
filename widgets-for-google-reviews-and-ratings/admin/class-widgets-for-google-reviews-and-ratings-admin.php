<?php

class Widgets_For_Google_Reviews_And_Ratings_Admin {

    private $plugin_name;
    private $version;
    public $place_details;
    public $admin_html;
    public $is_min;
    public $star_color;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = esc_html($plugin_name);
        $this->version = esc_html($version);
        $this->place_details = get_option('wgrr_g_place_details', '');
        $this->is_min = !(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);
        $this->star_color = get_option('repocean_star_color', '#F6BB06');
        add_action('wp_ajax_repocean_handle_review_action', array($this, 'handle_review_action'));
        add_action('wp_ajax_nopriv_repocean_handle_review_action', array($this, 'handle_review_action'));
    }

    public function enqueue_styles() {
        if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'google-reviews-settings') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/widgets-for-google-reviews-and-ratings-admin.css', [], $this->version, 'all');
        }
    }

    public function enqueue_scripts() {
        if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'google-reviews-settings') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/widgets-for-google-reviews-and-ratings-admin.js', ['jquery'], $this->version, false);
            wp_localize_script($this->plugin_name, 'wgrr_ajax', ['nonce' => wp_create_nonce('wgrr_nonce')]);
        }
    }

    public function wgrr_reviews_menu() {
        add_menu_page(
                esc_html__('Google Reviews Settings', 'widgets-for-google-reviews-and-ratings'),
                esc_html__('Google Reviews', 'widgets-for-google-reviews-and-ratings'),
                'manage_options',
                'google-reviews-settings',
                [$this, 'wgrr_reviews_settings_page'],
                esc_url(WGRR_ASSET_URL . 'admin/css/images/star.png'),
                15
        );
    }

    public function wgrr_reviews_settings_page() {
        $allowed_html = [
            'span' => ['style' => [], 'class' => []],
            'svg' => ['class' => [], 'viewBox' => [], 'xmlns' => []],
            'polygon' => ['points' => [], 'fill' => []],
            'defs' => [],
            'linearGradient' => ['id' => []],
            'stop' => ['offset' => [], 'stop-color' => []],
        ];
        $setting_tabs = apply_filters('wgrr_setting_tab', [
            'widget_customizer' => [
                'label' => esc_html__('Widget Customizer', 'widgets-for-google-reviews-and-ratings'),
                'sub_items' => [
                    'connect_google' => wp_kses(sprintf('<span>1</span> %s', __('Connect Google', 'widgets-for-google-reviews-and-ratings')), $allowed_html),
                    'select_layout' => wp_kses(sprintf('<span>2</span> %s', __('Select Layout', 'widgets-for-google-reviews-and-ratings')), $allowed_html),
                    'get_settings' => wp_kses(sprintf('<span class="dashicons dashicons-admin-generic"></span> %s', __('Settings', 'widgets-for-google-reviews-and-ratings')), $allowed_html),
                ],
            ],
            'get_reviews' => ['label' => esc_html__('Get Reviews', 'widgets-for-google-reviews-and-ratings')],
        ]);

        $sub_tab = empty($this->place_details) ? 'connect_google' : 'select_layout';
        $current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'widget_customizer'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $current_subtab = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : $sub_tab; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <div id="wgrr-plugin-settings-page">
            <h2 class="nav-tab-wrapper" style="display: none;">
                <?php
                foreach ($setting_tabs as $name => $tab) {
                    echo '<a href="' . esc_url(admin_url('admin.php?page=google-reviews-settings&tab=' . $name)) . '" class="nav-tab ' . ($current_tab == $name ? 'nav-tab-active' : '') . '">' . esc_html($tab['label']) . '</a>';
                }
                ?>
            </h2>

            <?php
            foreach ($setting_tabs as $setting_tab_key => $setting_tab_value) {
                if ($current_tab === $setting_tab_key) {
                    if (isset($setting_tab_value['sub_items'])) {
                        do_action('wgrr_' . esc_attr($setting_tab_key) . '_setting_save_field');
                        do_action('wgrr_' . esc_attr($setting_tab_key) . '_setting', $setting_tabs);
                        foreach ($setting_tab_value['sub_items'] as $key => $value) {
                            if ($current_subtab === $key) {
                                do_action('wgrr_' . esc_attr($setting_tab_key) . '_' . esc_attr($key) . '_setting_save_field');
                                do_action('wgrr_' . esc_attr($setting_tab_key) . '_' . esc_attr($key) . '_setting');
                            }
                        }
                    } else {
                        do_action('wgrr_' . esc_attr($setting_tab_key) . '_setting_save_field');
                        do_action('wgrr_' . esc_attr($setting_tab_key) . '_setting', $setting_tabs);
                    }
                }
            }
            ?>
        </div>
        <?php
    }

    public function wgrr_widget_customizer_setting($setting_tabs) {
        try {
            $sub_tab = empty($this->place_details) ? 'connect_google' : 'select_layout';
            $current_subtab = isset($_GET['subtab']) ? sanitize_text_field(wp_unslash($_GET['subtab'])) : $sub_tab; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ?>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach ($setting_tabs['widget_customizer']['sub_items'] as $name => $tab) {
                    $class = 'done';
                    if ($name === 'connect_google' && empty($this->place_details)) {
                        $class = '';
                    } elseif (($name === 'select_layout' || $name === 'get_settings') && empty($this->place_details)) {
                        $class = 'disabled';
                    }
                    echo '<a href="' . esc_url(admin_url('admin.php?page=google-reviews-settings&tab=widget_customizer&subtab=' . $name)) . '" class="nav-tab ' . ($current_subtab == $name ? 'nav-tab-active ' . esc_attr($class) : esc_attr($class)) . '">' . wp_kses_post($tab) . '</a>';
                }
                ?>
                <div id="link-support" style="margin-left: auto;">
                    <a href="https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/">Support</a>
                </div>

            </h2>
            <?php
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_widget_customizer_connect_google_setting() {
        try {
            if (empty($this->place_details)) {
                echo '<br/><br/><iframe id="wrrr_google_connect" src="' . esc_url(REPOCEAN_URL . 'place.html') . '" width="100%" height="900px" scrolling="yes" frameborder="0" allowfullscreen></iframe>';
            } else {
                $this->wgrr_display_place_details($this->place_details);
            }
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_widget_customizer_get_settings_setting_save_field() {
        try {
            // Code for saving shortcode settings
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_save_place_details() {
        try {
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgrr_nonce')) {
                wp_send_json_error(esc_html__('Nonce verification failed', 'widgets-for-google-reviews-and-ratings'));
            }
            if (isset($_POST['place']['placeId'])) {
                $place_data = json_decode(sanitize_text_field(wp_unslash($_POST['place']['placeId'])), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    update_option('wgrr_g_place_details', $place_data);
                    wp_send_json(['success' => true, 'return_url' => admin_url('admin.php?page=google-reviews-settings&tab=widget_customizer&subtab=select_layout')]);
                } else {
                    wp_send_json_error(esc_html__('Error decoding JSON.', 'widgets-for-google-reviews-and-ratings'));
                }
            } else {
                wp_send_json_error(esc_html__('Error: Place data not received.', 'widgets-for-google-reviews-and-ratings'));
            }
        } catch (Exception $ex) {
            wp_send_json_error(esc_html__('An error occurred: ', 'widgets-for-google-reviews-and-ratings') . esc_html($ex->getMessage()));
        }
    }

    public function generate_star_rating($rating) {
        try {
            $fullStars = floor($rating);
            $partialStar = $rating - $fullStars;
            $emptyStars = 5 - ceil($rating);
            $starHtml = '';
            for ($i = 0; $i < $fullStars; $i++) {
                $starHtml .= '<svg class="star" viewBox="0 0 24 24"><polygon points="12,2 15,8 22,9 17,14 18,21 12,17 6,21 7,14 2,9 9,8" fill="#fb8e28"/></svg>';
            }
            if ($partialStar > 0) {
                $partialStarOffset = esc_attr($partialStar * 100);
                $starHtml .= '
                <svg class="star" viewBox="0 0 24 24">
                    <defs>
                        <linearGradient id="partial-grad">
                            <stop offset="' . $partialStarOffset . '%" stop-color="#fb8e28" />
                            <stop offset="' . $partialStarOffset . '%" stop-color="#ccc" />
                        </linearGradient>
                    </defs>
                    <polygon points="12,2 15,8 22,9 17,14 18,21 12,17 6,21 7,14 2,9 9,8" fill="url(#partial-grad)"/>
                </svg>';
            }
            for ($i = 0; $i < $emptyStars; $i++) {
                $starHtml .= '<svg class="star" viewBox="0 0 24 24"><polygon points="12,2 15,8 22,9 17,14 18,21 12,17 6,21 7,14 2,9 9,8" fill="#ccc"/></svg>';
            }
            return $starHtml;
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_display_place_details($place) {
        try {
            /* phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  */
            $rating = $place['rating'] ?? 0;
            echo '<div id="place-details" class="">
              <div class="place-info">
                <img id="place-img" src="' . esc_url(WGRR_ASSET_URL . 'admin/image/bussiness-logo.png') . '" 
                     alt="' . esc_attr__('Place Image', 'widgets-for-google-reviews-and-ratings') . '" width="50" height="50">
                <div class="place-details-wrapper">
                    <div id="place-name">' . esc_html($place['name']) . '</div>
                    <div class="place-meta">
                        <div class="review-box" style="--rating: ' . esc_attr($rating) . '; --star-color: ' . esc_attr($this->star_color) . ';"></div>
                        <span id="place-reviews">' . esc_html($place['user_ratings_total'] ?? 0) . ' ' . esc_html__('reviews', 'widgets-for-google-reviews-and-ratings') . '</span>
                    </div>
                    <div id="place-address">' . esc_html($place['formatted_address']) . '</div>
                </div>
            </div>
            <div id="additional-details"></div>
            <button id="wgrr-disconnect-btn-red" class="btn-primary">' . esc_html__('Disconnect', 'widgets-for-google-reviews-and-ratings') . '</button>
          </div>';
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_delete_place_details() {
        try {
            check_ajax_referer('wgrr_nonce', 'nonce');
            if (!current_user_can('manage_options')) {
                wp_send_json_error();
            }
            delete_option('wgrr_g_place_details');
            delete_transient('repocean_google_review');
            set_transient('wgrr_place_disconnected_message', esc_html__('Place disconnected successfully. Connect a new place.', 'widgets-for-google-reviews-and-ratings'), 60);
            wp_send_json_success();
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_display_message() {
        try {
            if ($message = get_transient('wgrr_place_disconnected_message')) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                delete_transient('wgrr_place_disconnected_message');
            }
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_widget_customizer_select_layout_setting() {
        try {
            ?>
            <div class="repocean-notice-box">
                Hi there,
                If you have any questions or need assistance, feel free to 
                <a href="https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/#new-topic-0" target="_blank" rel="noopener">reach out</a> anytime. 
                If you have a moment, I would love it if you could 
                <a href="https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/reviews/#new-post" target="_blank" rel="noopener">leave a review</a>.
            </div>


            <div id="wgrr_accordion">
                <h2 class="select-layout-title"></h2>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Slider', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="slider"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_slider_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Slider I.', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="slider_v1"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_slider_v1_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Slider II.', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="slider_v2"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_slider_v2_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Slider III.', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="slider_v3"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_slider_v3_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Slider IIII.', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="slider_v4"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_slider_v4_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Grid', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="grid"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_grid_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('List', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="list"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_list_widget()); ?>
                    </div>
                </div>
                <div class="layout-section">
                    <div class="layout-header-with-shodtcode">
                        <div class="layout-row">
                            <h3><?php esc_html_e('Sidebar', 'widgets-for-google-reviews-and-ratings'); ?></h3>
                            <div class="shortcode-container">
                                <span><?php esc_html_e('Shortcode:', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                <code class="repocean-shortcode" id="repocean-shortcode-id">[repocean_reviews layout="sidebar"]</code>
                                <button class="repocean-btn repocean-tooltip">
                                    <?php esc_html_e('Copy to Clipboard', 'widgets-for-google-reviews-and-ratings'); ?>
                                    <span class="repocean-tooltip-message" style="opacity: 0;"><?php esc_html_e('Copied!', 'widgets-for-google-reviews-and-ratings'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-content">
                        <?php echo esc_html($this->wgrr_display_sidebar_widget()); ?>
                    </div>
                </div>
            </div>
            <?php
        } catch (Exception $ex) {
            // Handle exception
        }
    }

    public function wgrr_display_slider_widget() {
        echo do_shortcode('[repocean_reviews layout="slider"]');
    }

    public function wgrr_display_slider_v1_widget() {
        echo do_shortcode('[repocean_reviews layout="slider_v1"]');
    }

    public function wgrr_display_slider_v2_widget() {
        echo do_shortcode('[repocean_reviews layout="slider_v2"]');
    }

    public function wgrr_display_slider_v3_widget() {
        echo do_shortcode('[repocean_reviews layout="slider_v3"]');
    }

    public function wgrr_display_slider_v4_widget() {
        echo do_shortcode('[repocean_reviews layout="slider_v4"]');
    }

    public function wgrr_display_sidebar_widget() {
        echo do_shortcode('[repocean_reviews layout="sidebar"]');
    }

    public function wgrr_display_list_widget() {
        echo do_shortcode('[repocean_reviews layout="list"]');
    }

    public function wgrr_display_grid_widget() {
        echo do_shortcode('[repocean_reviews layout="grid"]');
    }

    public function register_repocean_reviews_widget() {
        register_widget('Widgets_For_Google_Reviews_And_Ratings_Widget');
    }

    public function repocean_get_user_status() {
        $activation_time = get_option('repocean_activation_time', false);
        $release_date = strtotime('2025-03-19 00:00:00');
        if (!$activation_time || $activation_time >= $release_date) {
            return 'new';
        }
        return 'existing';
    }

    public function repocean_get_user_status_4_july() {
        $activation_time = get_option('repocean_activation_time', false);
        $release_date = strtotime('2025-07-04 00:00:00');
        if (!$activation_time || $activation_time >= $release_date) {
            return 'new';
        }
        return 'existing';
    }

    public function wgrr_widget_customizer_get_settings_setting() {
        try {
            $repocean_show_verified_symbol_default_option = ( $this->repocean_get_user_status_4_july() === 'existing') ? 'no' : 'yes';
            $repocean_show_verified_by_default_option = ( $this->repocean_get_user_status() === 'existing') ? 'no' : 'yes';
            if (isset($_POST['repocean_submit'])) {
                if (!isset($_POST['google_reviews_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['google_reviews_nonce'])), 'google_reviews_settings_nonce')) {
                    wp_die(esc_html__('Security check failed. Nonce verification failed.', 'widgets-for-google-reviews-and-ratings'));
                }
                $options = [
                    'repocean_hide_date' => 'no',
                    'repocean_hide_profile_picture' => 'no',
                    'repocean_hide_google_logo' => 'no',
                    'repocean_hide_rating_text' => 'no',
                    'repocean_hide_prev_next_buttons' => 'no',
                    'repocean_show_verified_symbol' => $repocean_show_verified_symbol_default_option,
                    'repocean_star_color' => '251,183,1',
                    'repocean_shorten_reviewer_names' => 'no',
                    'repocean_enable_dark_mode' => 'no',
                    'repocean_show_verified_by' => $repocean_show_verified_by_default_option
                ];
                foreach ($options as $key => $default) {
                    $value = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : 'no';
                    update_option($key, $value);
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'widgets-for-google-reviews-and-ratings') . '</p></div>';
            }
            $hide_date = get_option('repocean_hide_date', 'no');
            $hide_profile_picture = get_option('repocean_hide_profile_picture', 'no');
            $hide_google_logo = get_option('repocean_hide_google_logo', 'no');
            $hide_rating_text = get_option('repocean_hide_rating_text', 'no'); // New Option
            $hide_prev_next_buttons = get_option('repocean_hide_prev_next_buttons', 'no'); // New Option
            $show_verified_symbol = get_option('repocean_show_verified_symbol', $repocean_show_verified_symbol_default_option); // New Option
            $repocean_show_verified_by = get_option('repocean_show_verified_by', $repocean_show_verified_by_default_option);
            $star_color = get_option('repocean_star_color', '#FBB701'); // New Option
            $shorten_reviewer_names = get_option('repocean_shorten_reviewer_names', 'no');
            $enable_dark_mode = get_option('repocean_enable_dark_mode', 'no');
            ?>
            <div class="repocean-settings-wrapper">
                <div class="repocean-settings-header">
                    <h2><?php esc_html_e('Google Review Widget Settings', 'widgets-for-google-reviews-and-ratings'); ?></h2>
                </div>
                <form method="post" action="">
                    <div class="repocean-settings-container">
                        <table class="repocean-form-table">
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_hide_date" name="repocean_hide_date" value="yes" <?php checked($hide_date, 'yes'); ?>>
                                    <label for="repocean_hide_date"><?php esc_html_e('Hide Review Date', 'widgets-for-google-reviews-and-ratings'); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_hide_profile_picture" name="repocean_hide_profile_picture" value="yes" <?php checked($hide_profile_picture, 'yes'); ?>>
                                    <label for="repocean_hide_profile_picture"><?php esc_html_e('Hide Reviewer Profile Picture', 'widgets-for-google-reviews-and-ratings'); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_hide_google_logo" name="repocean_hide_google_logo" value="yes" <?php checked($hide_google_logo, 'yes'); ?>>
                                    <label for="repocean_hide_google_logo"><?php esc_html_e('Hide Google Logo', 'widgets-for-google-reviews-and-ratings'); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_hide_rating_text" name="repocean_hide_rating_text" value="yes" <?php checked($hide_rating_text, 'yes'); ?>>
                                    <label for="repocean_hide_rating_text"><?php esc_html_e('Hide Widget Rating Text (e.g. EXCELLENT XXX reviews)', 'widgets-for-google-reviews-and-ratings'); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_hide_prev_next_buttons" name="repocean_hide_prev_next_buttons" value="yes" <?php checked($hide_prev_next_buttons, 'yes'); ?>>
                                    <label for="repocean_hide_prev_next_buttons">
                                        <?php esc_html_e('Hide "Previous" and "Next" Buttons', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_show_verified_by" name="repocean_show_verified_by" value="yes" <?php checked($repocean_show_verified_by, 'yes'); ?>>
                                    <label for="repocean_show_verified_by">
                                        <?php esc_html_e('Show Verified By RepOcean', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_show_verified_symbol" name="repocean_show_verified_symbol" value="yes" <?php checked($show_verified_symbol, 'yes'); ?>>
                                    <label for="repocean_show_verified_symbol">
                                        <?php esc_html_e('Show Verified Symbol for Reviews', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                    <span class="verified-icon-box"><span class="repocean-verified-tooltip" style="width: 101px;"><?php esc_html_e('RepOcean verifies that the original source of the review is Google.', 'widgets-for-google-reviews-and-ratings'); ?></span></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_shorten_reviewer_names" name="repocean_shorten_reviewer_names" value="yes" <?php checked($shorten_reviewer_names, 'yes'); ?>>
                                    <label for="repocean_shorten_reviewer_names">
                                        <?php esc_html_e('Shorten Reviewer Names (e.g., John Smith → John S.)', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="repocean_enable_dark_mode" name="repocean_enable_dark_mode" value="yes" <?php checked($enable_dark_mode, 'yes'); ?>>
                                    <label for="repocean_enable_dark_mode">
                                        <?php esc_html_e('Enable Dark Mode (Dark background with light text)', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <td style="display: flex; align-items: center; gap: 10px;">
                                    <label for="repocean_star_color">
                                        <?php esc_html_e('Change Star Color', 'widgets-for-google-reviews-and-ratings'); ?>
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <!-- Star Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" id="star-icon" style="fill: <?php echo esc_attr(strtoupper($star_color) ?: '#FBB701'); ?>;">
                                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                                        </svg>
                                        <!-- Color Picker -->
                                        <input 
                                            type="color" 
                                            id="repocean_star_color" 
                                            name="repocean_star_color" 
                                            value="<?php echo esc_attr(strtoupper($star_color) ?: '#FBB701'); ?>" 
                                            style="width: 36px; height: 36px; border: none; cursor: pointer;" 
                                            oninput="
                                            const color = this.value.toUpperCase();
                                            document.getElementById('star-icon').style.fill = color;
                                            document.getElementById('repocean_star_color_text').value = color;
                                            "
                                            onchange="
                                                                const color = this.value.toUpperCase();
                                                                document.getElementById('star-icon').style.fill = color;
                                                                document.getElementById('repocean_star_color_text').value = color;
                                            "
                                            />

                                        <!-- Text Input -->
                                        <input 
                                            type="text" 
                                            id="repocean_star_color_text" 
                                            name="repocean_star_color_text" 
                                            value="<?php echo esc_attr(strtoupper($star_color) ?: '#FBB701'); ?>" 
                                            style="padding: 5px; width: 100px; border: 1px solid #ccc; text-align: center;" 
                                            placeholder="#FBB701" 
                                            oninput="
                                            let color = this.value.toUpperCase();
                                            if (!color.startsWith('#')) color = '#' + color;
                                            if (color.length === 7) {
                                            document.getElementById('star-icon').style.fill = color;
                                            document.getElementById('repocean_star_color').value = color;
                                            }
                                            "
                                            onchange="
                                                                if (!this.value.trim()) {
                                                                    this.value = '#FBB701';
                                                                    document.getElementById('repocean_star_color').value = '#FBB701';
                                                                    document.getElementById('star-icon').style.fill = '#FBB701';
                                                                }
                                            "
                                            />

                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <div class="repocean-submit-container">
                        <?php wp_nonce_field('google_reviews_settings_nonce', 'google_reviews_nonce'); ?>
                        <button type="submit" name="repocean_submit" class="repocean-btn"><?php esc_html_e('Save Changes', 'widgets-for-google-reviews-and-ratings'); ?></button>
                    </div>
                </form>
            </div>
            <?php
        } catch (Exception $ex) {
            echo '<div class="notice notice-error"><p>' . esc_html__('An error occurred while saving the settings. Please try again.', 'widgets-for-google-reviews-and-ratings') . '</p></div>';
        }
    }

    public function plugin_action_links($actions) {
        $base_url = admin_url('admin.php?page=google-reviews-settings');
        $configure_url = $base_url;
        $configure = sprintf('<a href="%s">%s</a>', $configure_url, __('Settings', 'widgets-for-google-reviews-and-ratings'));
        $custom_actions = array(
            'settings' => $configure,
        );
        return array_merge($custom_actions, $actions);
    }

    public function add_plugin_meta_links($meta, $file) {
        if (basename($file) === basename(WGRR_PLUGIN_FILE)) {
            $meta[] = '<a href="https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/">' . __('Community support', 'widgets-for-google-reviews-and-ratings') . '</a>';
            $meta[] = '<a href="https://wordpress.org/support/plugin/widgets-for-google-reviews-and-ratings/reviews/#new-post" target="_blank" rel="noopener noreferrer">' . __('Rate our plugin', 'widgets-for-google-reviews-and-ratings') . '</a>';
        }
        return $meta;
    }

    public function wrrr_plugin_redirect_after_activation() {
        if (get_transient('wrrr_plugin_activation_redirect')) {
            delete_transient('wrrr_plugin_activation_redirect');
            if (is_network_admin() || isset($_GET['activate-multi'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return;
            }
            wp_redirect(admin_url('admin.php?page=google-reviews-settings'));
            exit;
        }
    }

    public function leaverev() {
        $activation_time = get_option('repocean_activation_time');
        if ($activation_time == '') {
            $activation_time = time();
            update_option('repocean_activation_time', $activation_time);
        }
        $rev_notice_hide = get_option('repocean_review_notice_hide_v1');
        $next_show_time = get_option('repocean_next_show_time', time());
        $days_since_activation = (time() - $activation_time) / 86400;
        if ($rev_notice_hide != 'never' && $days_since_activation >= 10 && time() >= $next_show_time) {
            $class = 'repocean-review-notice-parent notice notice-info';
            $notice = '<div class="repocean-review-notice">' .
                    '<p style="font-weight:normal;font-size:15px;">' .
                    '<strong>Hi there,</strong><br>' .
                    'We’re glad to see you’ve been using <b>Google Reviews Plugin</b>!<br>' .
                    'Could you share your experience by leaving a review on WordPress?<br> Your feedback means a lot to us.<br>' .
                    '<br>Thank you!<br>Team RepOcean' .
                    '</p>' .
                    '<p style="margin-bottom:10px;">' .
                    '<a>' .
                    '<button class="button button-primary repocean-action-button" data-action="reviewed" style="margin-right:5px;" type="button">OK, you deserve it</button>' .
                    '</a>' .
                    '<button class="button button-secondary repocean-action-button" data-action="later" style="margin-right:5px;">Not now, maybe later</button>' .
                    '<button class="button button-secondary repocean-action-button" data-action="never" style="float:right;">Don’t remind me again</button>' .
                    '</p>' .
                    '</div>';

            printf('<div class="%1$s" style="position:fixed;bottom:50px;right:20px;padding-right:30px;z-index:2;margin-left:20px">%2$s</div>', esc_attr($class), wp_kses_post($notice));
        }
    }

    public function handle_review_action() {
        if (!isset($_POST['nonce'])) {
            wp_send_json_error('Nonce is missing');
        }
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'repocean_review_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        $action = isset($_POST['review_action']) ? sanitize_text_field(wp_unslash($_POST['review_action'])) : '';
        if ($action === 'later') {
            update_option('repocean_next_show_time', time() + (86400 * 7));
            update_option('repocean_review_notice_hide_v1', 'later');
        } elseif ($action === 'never') {
            update_option('repocean_review_notice_hide_v1', 'never');
        } elseif ($action === 'reviewed') {
            update_option('repocean_review_notice_hide_v1', 'never');
        } else {
            wp_send_json_error('Invalid action');
        }
        wp_send_json_success();
    }

    public function wgrr_enqueue_scripts() {
        wp_enqueue_script(
                'repocean-review-ajax',
                WGRR_ASSET_URL . '/admin/js/review-ajax.js',
                array('jquery'),
                WGRR_VERSION,
                true
        );
        $nonce = wp_create_nonce('repocean_review_nonce');
        wp_localize_script('repocean-review-ajax', 'rep_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
        ));
    }
}
