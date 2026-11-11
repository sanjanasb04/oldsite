<?php
/**
 * Plugin Name: Antigravity Site Autopilot & Brand Replacer
 * Description: Automatically replaces brand names, removes ?v= geolocation param, and manages WordPress via REST API.
 * Version: 2.1.0
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
    if (get_option('antigravity_brand_replaced_v4') === 'done') {
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
        // 4a. Posts table
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts} SET 
                post_content = REPLACE(post_content, %s, %s),
                post_title   = REPLACE(post_title, %s, %s),
                post_excerpt = REPLACE(post_excerpt, %s, %s)",
            $search, $replace, $search, $replace, $search, $replace
        ));

        // 4b. Postmeta _elementor_data (pure JSON, not PHP serialized)
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

        // 4c. Other postmeta non-serialized
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key != '_elementor_data' AND meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%'",
            $search, $replace
        ));

        // 4d. Options non-serialized
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s) WHERE option_name NOT LIKE '%transient%' AND option_value NOT LIKE 'a:%' AND option_value NOT LIKE 'O:%'",
            $search, $replace
        ));
    }

    update_option('antigravity_brand_replaced_v4', 'done');
});

// 5. Output buffer: replace any header logo SVG & hardcoded template mentions on frontend
add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    ob_start(function($html) {
        if (empty($html)) {
            return $html;
        }

        // 5a. Replace base64 LearnersKart SVG in Header with crisp stylized text logo
        $certPilotLogo = '<a class="elementor-icon" href="https://certificationpilot.com/" style="text-decoration:none;display:inline-flex;align-items:center;padding:5px 0;">'
                       . '<span style="font-size:24px;font-weight:800;letter-spacing:0.5px;font-family:Urbanist,sans-serif;color:#098ce9;white-space:nowrap;">CERTIFICATION <span style="color:#1c1c1b;">PILOT</span></span>'
                       . '</a>';

        $html = preg_replace(
            '/<a class="elementor-icon" href="[^"]*">\s*<svg[^>]*>.*?<\/svg>\s*<\/a>/si',
            $certPilotLogo,
            $html
        );

        // 5b. String replacements for any remaining mentions in widgets, menus, titles
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
