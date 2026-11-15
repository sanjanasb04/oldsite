<?php
/**
 * Plugin Name: Antigravity Site Autopilot, Brand Replacer & Official Logo
 * Description: Automatically applies official Certification Pilot logo, brand names, and modern UI system.
 * Version: 3.1.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

// 0. Remove index.html if present in WordPress root
if (file_exists(ABSPATH . 'index.html')) {
    @unlink(ABSPATH . 'index.html');
}

// 1. SSL proxy detection for Bigrock / cPanel
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 2. Completely eliminate WooCommerce ?v=... parameter
add_filter('woocommerce_get_geolocation_hash', '__return_empty_string', 999);
add_action('init', function() {
    if (get_option('woocommerce_default_customer_address') !== 'base') {
        update_option('woocommerce_default_customer_address', 'base');
    }
});

// 3. Upgrade user sunnylee to administrator & set site title
add_action('init', function() {
    $user = get_user_by('login', 'sunnylee');
    if ($user && !in_array('administrator', (array)$user->roles)) {
        $user->set_role('administrator');
    }
    
    if (get_option('blogname') !== 'Certification Pilot') {
        update_option('blogname', 'Certification Pilot');
    }
    if (get_option('blogdescription') !== 'Training for Professionals') {
        update_option('blogdescription', 'Training for Professionals');
    }
});

// 4. One-time database batch replacement for LearnersKart -> Certification Pilot
add_action('init', function() {
    if (get_option('antigravity_brand_replaced_v6') === 'done') {
        return;
    }

    global $wpdb;

    $replacements = [
        'https://www.learnerskart.com' => 'https://certificationpilot.com',
        'http://www.learnerskart.com'  => 'https://certificationpilot.com',
        'https://learnerskart.com'     => 'https://certificationpilot.com',
        'http://learnerskart.com'      => 'https://certificationpilot.com',
        'www.learnerskart.com'         => 'certificationpilot.com',
        'learnerskart.com'             => 'certificationpilot.com',
        'learnerkart.com'              => 'certificationpilot.com',
        'LEARNERSKART'                 => 'CERTIFICATION PILOT',
        'LearnersKart'                 => 'Certification Pilot',
        'Learnerskart'                 => 'Certification Pilot',
        'learnerskart'                 => 'certificationpilot'
    ];

    foreach ($replacements as $search => $replace) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts} SET 
                post_content = REPLACE(post_content, %s, %s),
                post_title   = REPLACE(post_title, %s, %s),
                post_excerpt = REPLACE(post_excerpt, %s, %s)",
            $search, $replace, $search, $replace, $search, $replace
        ));

        $elementor_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($search) . '%'
        ));
        if (!empty($elementor_rows)) {
            foreach ($elementor_rows as $row) {
                $updated = str_replace($search, $replace, $row->meta_value);
                $wpdb->update($wpdb->postmeta, ['meta_value' => $updated], ['meta_id' => $row->meta_id]);
            }
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key != '_elementor_data' AND meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%'",
            $search, $replace
        ));

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_name NOT LIKE '%transient%' AND option_value NOT LIKE 'a:%' AND option_value NOT LIKE 'O:%'",
            $search, $replace
        ));
    }

    update_option('antigravity_brand_replaced_v6', 'done');
});

// 5. Inject 2026 Modern Design System CSS and Favicon into wp_head
add_action('wp_head', function() {
    echo '<link rel="icon" href="https://certificationpilot.com/wp-content/uploads/logos/certification-pilot-favicon.png" type="image/png" sizes="512x512" />' . "\n";
    echo '<link rel="apple-touch-icon" href="https://certificationpilot.com/wp-content/uploads/logos/certification-pilot-favicon.png" />' . "\n";
    $css_file = __DIR__ . '/modern-ui.css';
    if (file_exists($css_file)) {
        echo '<style id="certification-pilot-modern-ui">' . file_get_contents($css_file) . '</style>' . "\n";
    }
}, 9999);

// 6. Output buffer: replace logo & hardcoded template mentions on frontend
add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    ob_start(function($html) {
        if (empty($html)) {
            return $html;
        }

        // 6a. Official Airplane/CP Vector Logo for Header & Navbar
        $officialHeaderLogo = '<a class="elementor-icon" href="https://certificationpilot.com/" style="text-decoration:none;display:inline-flex;align-items:center;padding:2px 0;">'
                            . '<img src="https://certificationpilot.com/wp-content/uploads/logos/certification-pilot-logo-horizontal.png" alt="Certification Pilot" style="height:62px;width:auto;max-width:280px;object-fit:contain;display:block;" />'
                            . '</a>';

        $html = preg_replace(
            '/<a class="elementor-icon" href="[^"]*">.*?<\/a>/si',
            $officialHeaderLogo,
            $html,
            1
        );

        // 6b. Replace footer logo / cropped-hd-logo-1
        $html = preg_replace(
            '/<img[^>]+src="[^"]*cropped-hd-logo-1[^"]*"[^>]*>/si',
            '<img src="https://certificationpilot.com/wp-content/uploads/logos/certification-pilot-logo-horizontal.png" title="Certification Pilot Logo" alt="Certification Pilot Logo" style="height:48px;width:auto;object-fit:contain;" />',
            $html
        );

        // 6c. String replacements for any remaining mentions in widgets, menus, titles
        $search = [
            'LEARNERSKART',
            'LearnersKart',
            'Learnerskart',
            'learnerskart.com',
            'learnerkart.com',
            'learnerskart'
        ];

        $replace = [
            'CERTIFICATION PILOT',
            'Certification Pilot',
            'Certification Pilot',
            'certificationpilot.com',
            'certificationpilot.com',
            'certificationpilot'
        ];

        return str_replace($search, $replace, $html);
    });
});
