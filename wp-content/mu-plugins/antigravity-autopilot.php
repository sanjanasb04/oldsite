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

// 3. Basic settings
add_action('init', function() {
    if (get_option('blogname') !== 'Certification Pilot') {
        update_option('blogname', 'Certification Pilot');
    }
    if (get_option('blogdescription') !== 'Training for Professionals') {
        update_option('blogdescription', 'Training for Professionals');
    }
});

// 4. (Database migration already completed)

// 5. Inject 2026 Modern Design System CSS and Favicon into wp_head
add_action('wp_head', function() {
    echo '<link rel="icon" href="https://certificationpilot.com/wp-content/uploads/2026/11/certification-pilot-favicon.png" type="image/png" sizes="512x512" />' . "\n";
    echo '<link rel="apple-touch-icon" href="https://certificationpilot.com/wp-content/uploads/2026/11/certification-pilot-favicon.png" />' . "\n";
    $css_file = __DIR__ . '/modern-ui.css';
    if (file_exists($css_file)) {
        echo '<style id="certification-pilot-modern-ui">' . file_get_contents($css_file) . '</style>' . "\n";
    }
}, 9999);

// 6. Output buffer: replace logo, placeholders & hardcoded template mentions on frontend
add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    ob_start(function($html) {
        if (empty($html)) {
            return $html;
        }

        // 6a. Official Airplane/CP Vector Logo for Header & Navbar
        $officialHeaderLogo = '<a class="cp-official-logo" href="https://certificationpilot.com/" style="text-decoration:none;display:inline-flex;align-items:center;padding:2px 0;">'
                            . '<img src="https://certificationpilot.com/wp-content/uploads/2026/11/certification-pilot-logo-horizontal.png" alt="Certification Pilot" style="height:62px;width:auto;max-width:280px;object-fit:contain;display:block;" />'
                            . '</a>';

        $html = preg_replace(
            '/<div class="elementor-element elementor-element-08bde2f[^"]*"[^>]*>.*?<\/div>\s*<\/div>\s*<\/div>/s',
            '<div class="elementor-element elementor-element-08bde2f elementor-widget elementor-widget-icon" data-element_type="widget"><div class="elementor-widget-container"><div class="elementor-icon-wrapper">' . $officialHeaderLogo . '</div></div></div>',
            $html,
            1
        );

        // 6b. Replace footer logo & old logo images
        $oldLogoFiles = [
            'cropped-hd-logo-1.png',
            'cropped-hd-logo-1-300x70.png',
            'cropped-hd-logo-1-1024x239.png',
            'cropped-hd-logo-1-768x179.png',
            'cropped-hd-logo-1-1536x359.png',
            'logos/certification-pilot-logo-horizontal.png'
        ];
        foreach ($oldLogoFiles as $olf) {
            $html = str_replace($olf, '2026/11/certification-pilot-logo-horizontal.png', $html);
        }

        // 6c. Replace old course image placeholders with official course covers
        $courseImageReplacements = [
            '2149595847-1-1024x683.jpg' => 'pmp-v6.jpg',
            '2149595847-666x450.jpg'      => 'pmp-v6.jpg',
            'WhatsApp-Image-2025-05-04-at-6.33.51-PM-666x450.jpeg' => 'pmp-v6.jpg',
            '2149595850-666x450.jpg'      => 'lssyb-v3.jpg',
            '167088-666x450.jpg'          => 'lssbb-v3.jpg',
            '2147826617-666x450.jpg'      => 'lssgb-v3.jpg',
        ];
        foreach ($courseImageReplacements as $oldImg => $newImg) {
            $html = str_replace($oldImg, $newImg, $html);
        }

        // 6d. String replacements for any remaining mentions in widgets, menus, titles
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

// 7. Interactive Modern Course Catalog Shortcode [cp_course_catalog]
add_shortcode('cp_course_catalog', function($atts) {
    $courses = [
        [
            'title'    => 'PMP® Certification Training',
            'category' => 'Project Management',
            'badge'    => 'Best Seller',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/pmp-v6.jpg',
            'url'      => 'https://certificationpilot.com/pmp-certification-training/',
            'desc'     => 'Global gold standard for project leaders. 35 PDUs, mock exams, and pass guarantee.'
        ],
        [
            'title'    => 'CAPM® Certification Training',
            'category' => 'Project Management',
            'badge'    => 'Entry Level',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/capm-v9.jpg',
            'url'      => 'https://certificationpilot.com/capm-certification-training/',
            'desc'     => 'Kickstart your project management career with PMI-certified foundation skills.'
        ],
        [
            'title'    => 'PRINCE2® Foundation & Practitioner',
            'category' => 'Project Management',
            'badge'    => 'Combo Deal',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/prince2-combo-v3.jpg',
            'url'      => 'https://certificationpilot.com/prince2-foundation-and-practitioner-training/',
            'desc'     => 'Master the UK and globally recognized process-based project methodology.'
        ],
        [
            'title'    => 'PRINCE2® Foundation',
            'category' => 'Project Management',
            'badge'    => 'Popular',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/prince2-foundation-v4.jpg',
            'url'      => 'https://certificationpilot.com/prince2-foundation-certification-training/',
            'desc'     => 'Learn core PRINCE2 principles, themes, and processes for project governance.'
        ],
        [
            'title'    => 'PRINCE2® Practitioner',
            'category' => 'Project Management',
            'badge'    => 'Advanced',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/prince2-practitioner-v4.jpg',
            'url'      => 'https://certificationpilot.com/prince2-practitioner-training/',
            'desc'     => 'Apply and tailor PRINCE2 to real-world corporate project scenarios.'
        ],
        [
            'title'    => 'Lean Six Sigma Green Belt',
            'category' => 'Quality Management',
            'badge'    => 'High Demand',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/lssgb-v3.jpg',
            'url'      => 'https://certificationpilot.com/lean-six-sigma-green-belt-certification-training/',
            'desc'     => 'Drive DMAIC process improvements, reduce variation, and elevate operational quality.'
        ],
        [
            'title'    => 'Lean Six Sigma Black Belt',
            'category' => 'Quality Management',
            'badge'    => 'Leadership',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/lssbb-v3.jpg',
            'url'      => 'https://certificationpilot.com/lean-six-sigma-black-belt-certification-training/',
            'desc'     => 'Lead enterprise-level change initiatives and complex statistical quality analytics.'
        ],
        [
            'title'    => 'Lean Six Sigma Green & Black Belt Combo',
            'category' => 'Quality Management',
            'badge'    => 'Best Value',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/lssgb_lssbb-v3.jpg',
            'url'      => 'https://certificationpilot.com/lssgb-lssbb-combo-certification-training/',
            'desc'     => 'Accelerate from foundational DMAIC to advanced Black Belt expertise in one journey.'
        ],
        [
            'title'    => 'Lean Six Sigma Yellow Belt',
            'category' => 'Quality Management',
            'badge'    => 'Fundamentals',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/lssyb-v3.jpg',
            'url'      => 'https://certificationpilot.com/lean-six-sigma-yellow-belt-certification-training/',
            'desc'     => 'Understand Six Sigma principles and support process improvement project teams.'
        ],
        [
            'title'    => 'PMI-ACP® (Agile Certified Practitioner)',
            'category' => 'Agile & Scrum',
            'badge'    => 'Hot',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/pmi_acp-v3.jpg',
            'url'      => 'https://certificationpilot.com/agile-certified-practitioner-certification-training/',
            'desc'     => 'Validate your proficiency across Scrum, Kanban, Lean, and XP agile approaches.'
        ],
        [
            'title'    => 'PgMP® (Program Management Professional)',
            'category' => 'Executive',
            'badge'    => 'Executive',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/pgmp-v3.jpg',
            'url'      => 'https://certificationpilot.com/program-management-professional-certification-training/',
            'desc'     => 'Senior credential for leaders managing multiple complex, strategic projects.'
        ],
        [
            'title'    => 'PMI-RMP® (Risk Management Professional)',
            'category' => 'Project Management',
            'badge'    => 'Specialized',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/rmp-v3.jpg',
            'url'      => 'https://certificationpilot.com/risk-management-professional-certification-training/',
            'desc'     => 'Mitigate enterprise threats and capitalize on project opportunities systematically.'
        ],
        [
            'title'    => 'CBAP® (Business Analysis Professional)',
            'category' => 'Business Analysis',
            'badge'    => 'IIBA Accredited',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/cbap-v3.jpg',
            'url'      => 'https://certificationpilot.com/cbap-training/',
            'desc'     => 'Premier IIBA certification for elite business analysts leading strategic transformations.'
        ],
        [
            'title'    => 'CCBA® (Capability in Business Analysis)',
            'category' => 'Business Analysis',
            'badge'    => 'Intermediate',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/ccba-v3.jpg',
            'url'      => 'https://certificationpilot.com/ccba-certification-training/',
            'desc'     => 'For practitioners with 2-3 years experience ready to formalize IIBA capabilities.'
        ],
        [
            'title'    => 'ECBA® (Entry Certificate in Business Analysis)',
            'category' => 'Business Analysis',
            'badge'    => 'Beginner',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/ecba-v3.jpg',
            'url'      => 'https://certificationpilot.com/ecba-certification-training/',
            'desc'     => 'Ideal for fresh graduates and newcomers stepping into corporate business analysis.'
        ],
        [
            'title'    => 'Digital Marketing Certification',
            'category' => 'Marketing',
            'badge'    => 'Career Boost',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/digital-marketing-v2.jpg',
            'url'      => 'https://certificationpilot.com/digital-marketing-training/',
            'desc'     => 'Master SEO, SEM, social media performance marketing, analytics, and growth.'
        ],
        [
            'title'    => 'CPM-AI™ (Certified Project Manager in AI)',
            'category' => 'Next-Gen AI',
            'badge'    => 'Trending 2026',
            'cover'    => 'https://certificationpilot.com/wp-content/uploads/2026/11/cpmai-banner.png',
            'url'      => 'https://certificationpilot.com/courses/',
            'desc'     => 'Integrate generative AI, autonomous agent workflows, and predictive AI into PM.'
        ],
    ];

    ob_start();
    ?>
    <div class="cp-catalog-wrapper" style="max-width:1280px;margin:30px auto;padding:0 20px;">
        <div class="cp-catalog-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(290px, 1fr));gap:24px;">
            <?php foreach ($courses as $c): ?>
                <div class="cp-card" style="background:#ffffff;border:1px solid rgba(226, 232, 240, 0.85);border-radius:16px;overflow:hidden;box-shadow:0 4px 14px rgba(15, 23, 42, 0.05);display:flex;flex-direction:column;transition:transform 0.25s ease, box-shadow 0.25s ease;">
                    <div style="position:relative;width:100%;padding-top:56.25%;overflow:hidden;background:#f1f5f9;">
                        <img src="<?php echo esc_url($c['cover']); ?>" alt="<?php echo esc_attr($c['title']); ?>" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;transition:transform 0.3s ease;" loading="lazy" />
                        <span style="position:absolute;top:12px;left:12px;background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%);color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?php echo esc_html($c['badge']); ?></span>
                    </div>
                    <div style="padding:20px;display:flex;flex-direction:column;flex-grow:1;">
                        <span style="font-size:12px;font-weight:600;color:#0284c7;text-transform:uppercase;margin-bottom:6px;"><?php echo esc_html($c['category']); ?></span>
                        <h3 style="font-size:17px;font-weight:700;color:#0f172a;line-height:1.35;margin:0 0 8px;min-height:46px;"><?php echo esc_html($c['title']); ?></h3>
                        <p style="font-size:13px;color:#64748b;line-height:1.5;margin:0 0 16px;flex-grow:1;"><?php echo esc_html($c['desc']); ?></p>
                        <a href="<?php echo esc_url($c['url']); ?>" style="display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%);color:#ffffff;font-size:13.5px;font-weight:600;padding:10px 16px;border-radius:10px;text-decoration:none;transition:transform 0.2s ease, box-shadow 0.2s ease;">View Course & Schedule &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
