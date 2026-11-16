<?php
/**
 * Plugin Name: Vibecast
 * Description: Turn AI-authored HTML into reusable, isolated sections with an authoring skill and responsive height presets.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Compiled Rogue
 * Author URI: https://compiledrogue.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vibecast
 */
namespace CompiledRogue\VibeCast;

defined('ABSPATH') || exit;

final class Plugin {
    public const HEIGHT_DEFAULTS = ['base' => 600, 'sm' => 600, 'md' => 600, 'lg' => 600];

    public static function sanitize_heights($input): array {
        $input = is_array($input) ? $input : [];
        $heights = [];
        foreach (self::HEIGHT_DEFAULTS as $key => $default) {
            $value = $input[$key] ?? $default;
            $heights[$key] = is_scalar($value) && is_numeric($value) ? min(10000, max(100, (int) $value)) : $default;
        }
        return $heights;
    }

    /** Only ordinary content elements; WordPress also sanitizes inline CSS and URLs. */
    public static function sanitize($html): string {
        $allowed = [];
        foreach (['p', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'blockquote', 'pre', 'code', 'strong', 'em', 'b', 'i', 'u', 's', 'small', 'sub', 'sup', 'br', 'hr', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'details', 'summary'] as $tag) {
            $allowed[$tag] = ['style' => true, 'class' => true, 'id' => true, 'title' => true, 'lang' => true, 'dir' => true];
        }
        $allowed['a'] = ['href' => true, 'title' => true, 'style' => true];
        $allowed['img'] = ['src' => true, 'alt' => true, 'width' => true, 'height' => true, 'style' => true];
        // Remove script/stylesheet text for readable output. KSES is the security boundary.
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', (string) $html);
        // Authored inline CSS must not re-enable nested scrolling.
        $no_scrolling = static function ($properties) {
            return array_values(array_diff($properties, ['overflow', 'overflow-x', 'overflow-y', 'scrollbar-width', 'scrollbar-color']));
        };
        add_filter('safe_style_css', $no_scrolling, PHP_INT_MAX);
        try {
            return wp_kses($html, $allowed, ['http', 'https', 'mailto', 'tel']);
        } finally {
            remove_filter('safe_style_css', $no_scrolling, PHP_INT_MAX);
        }
    }

    public static function register(): void {
        register_post_type('cr_vibecast', [
            'labels' => [
                'name' => __('Vibecasts', 'vibecast'),
                'singular_name' => __('Vibecast', 'vibecast'),
                'add_new_item' => __('Add Vibecast', 'vibecast'),
                'edit_item' => __('Edit Vibecast', 'vibecast'),
            ],
            'public' => false, 'show_ui' => true, 'show_in_rest' => false,
            'rewrite' => false, 'query_var' => false, 'menu_icon' => 'dashicons-editor-code',
            'supports' => ['title', 'revisions'],
            'map_meta_cap' => true,
            'capability_type' => ['cr_vibecast', 'cr_vibecasts'],
            'capabilities' => array_fill_keys([
                'edit_posts', 'edit_others_posts', 'create_posts', 'publish_posts',
                'edit_published_posts', 'edit_private_posts', 'delete_posts',
                'delete_others_posts', 'delete_published_posts', 'delete_private_posts', 'read_private_posts',
            ], 'manage_options'),
        ]);
        register_post_meta('cr_vibecast', '_vibecast_code', [
            'type' => 'string', 'single' => true, 'show_in_rest' => false,
            'revisions_enabled' => true, 'sanitize_callback' => [self::class, 'sanitize'],
            'auth_callback' => static function ($allowed, $key, $id) { return current_user_can('manage_options') && current_user_can('edit_post', $id); },
        ]);
        register_post_meta('cr_vibecast', '_vibecast_heights', [
            'type' => 'object', 'single' => true, 'show_in_rest' => false,
            'revisions_enabled' => true, 'sanitize_callback' => [self::class, 'sanitize_heights'],
            'auth_callback' => static function ($allowed, $key, $id) { return current_user_can('manage_options') && current_user_can('edit_post', $id); },
        ]);
        wp_register_script('vibecast-editor', plugins_url('assets/editor.js', __FILE__), ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'], substr(md5_file(__DIR__ . '/assets/editor.js'), 0, 12), true);
        wp_set_script_translations('vibecast-editor', 'vibecast');
        register_block_type('cr-vibecast/cast', [
            'api_version' => 3, 'editor_script' => 'vibecast-editor',
            'attributes' => ['id' => ['type' => 'integer', 'default' => 0], 'height' => ['type' => 'integer', 'default' => 0]],
            'render_callback' => static function ($attrs) { return self::render(absint($attrs['id'] ?? 0), absint($attrs['height'] ?? 0)); },
        ]);
        add_shortcode('cr_vibecast', static function ($attrs) {
            $attrs = shortcode_atts(['id' => 0, 'slug' => '', 'height' => 0], $attrs, 'cr_vibecast');
            $id = absint($attrs['id']);
            if (!$id && $attrs['slug']) {
                $post = get_page_by_path(sanitize_title($attrs['slug']), OBJECT, 'cr_vibecast');
                $id = $post ? $post->ID : 0;
            }
            return self::render($id, absint($attrs['height']));
        });
    }

    public static function published_casts(): array {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && !current_user_can('manage_options')) {
            return [];
        }
        $posts = get_posts([
            'post_type' => 'cr_vibecast', 'post_status' => 'publish',
            'has_password' => false, 'numberposts' => -1,
            'orderby' => 'title', 'order' => 'ASC',
        ]);
        return array_map(static function ($post) {
            return ['id' => $post->ID, 'title' => $post->post_title];
        }, $posts);
    }

    public static function editor_data(): void {
        wp_localize_script('vibecast-editor', 'vibecastLibrary', ['casts' => self::published_casts()]);
    }

    public static function document(string $content, bool $full = false, string $title = ''): string {
        $overflow = $full ? '' : 'html,body{overflow:hidden!important}*,*::before,*::after{overflow:clip!important;scrollbar-width:none!important}*::-webkit-scrollbar{display:none!important}';
        return '<!doctype html><html><head><title>' . esc_html($title ?: __('Vibecast', 'vibecast')) . '</title><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="default-src &#39;none&#39;; script-src &#39;none&#39;; style-src &#39;unsafe-inline&#39;; img-src https: http:; base-uri &#39;none&#39;; form-action &#39;none&#39;"><meta name="viewport" content="width=device-width, initial-scale=1"><style>html,body{margin:0}body{overflow-wrap:anywhere}*,*::before,*::after{box-sizing:border-box}' . $overflow . 'img{max-width:100%;height:auto}</style></head><body>' . $content . '</body></html>';
    }

    public static function render(int $id, int $height = 0, bool $preview = false): string {
        $post = $id > 0 ? get_post($id) : null;
        if (!$post || 'cr_vibecast' !== $post->post_type || post_password_required($post)) {
            return '';
        }
        if ('publish' !== $post->post_status && (!$preview || !current_user_can('manage_options') || !current_user_can('edit_post', $id))) {
            return '';
        }
        // Sanitize on output too: old revisions and pre-directory installations may contain scripts.
        $content = self::sanitize(get_post_meta($id, '_vibecast_code', true));
        if ('' === trim($content)) {
            return '';
        }
        $document = self::document($content, false, $post->post_title);
        $heights = self::sanitize_heights(get_post_meta($id, '_vibecast_heights', true));
        // Preserve explicit fixed-height placements from earlier versions.
        if ($height > 0) {
            $heights = array_fill_keys(array_keys(self::HEIGHT_DEFAULTS), min(10000, max(100, $height)));
        }
        $variables = '';
        foreach ($heights as $key => $value) {
            $variables .= '--cr-vibecast-' . $key . ':' . $value . 'px;';
        }
        // Static, plugin-owned CSS travels with each placement, including partial-page navigation.
        $css = '.cr-vibecast-wrap{container:cr-vibecast / inline-size;display:block;width:100%;min-width:0}'
            . '.cr-vibecast-wrap>iframe{display:block;width:100%;max-width:100%;height:var(--cr-vibecast-base,600px);border:0}'
            . '@container cr-vibecast (min-width:640px){.cr-vibecast-wrap>iframe{height:var(--cr-vibecast-sm)}}'
            . '@container cr-vibecast (min-width:768px){.cr-vibecast-wrap>iframe{height:var(--cr-vibecast-md)}}'
            . '@container cr-vibecast (min-width:1024px){.cr-vibecast-wrap>iframe{height:var(--cr-vibecast-lg)}}';
        return '<style>' . $css . '</style><div class="cr-vibecast-wrap" style="' . esc_attr($variables) . '">'
            . sprintf('<iframe title="%s" sandbox="" scrolling="no" referrerpolicy="no-referrer" loading="lazy" srcdoc="%s"></iframe>',
                esc_attr($post->post_title ?: __('Vibecast', 'vibecast')), htmlspecialchars($document, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', true)) . '</div>'
            . ('publish' === $post->post_status && '' === $post->post_password ? '<p><a href="' . esc_url(add_query_arg(['action' => 'vibecast_full', 'id' => $id], admin_url('admin-post.php'))) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Open full content', 'vibecast') . '</a></p>' : '');
    }

    public static function full_view(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only view: only published, unprotected content is returned; no state is changed.
        $id = isset($_GET['id']) && is_string($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
        $post = $id ? get_post($id) : null;
        if (!$post || 'cr_vibecast' !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password) {
            wp_die(esc_html__('This Vibecast is unavailable.', 'vibecast'), '', ['response' => 404]);
        }
        nocache_headers();
        header('Content-Type: text/html; charset=UTF-8');
        header("Content-Security-Policy: sandbox; default-src 'none'; script-src 'none'; style-src 'unsafe-inline'; img-src https: http:; base-uri 'none'; form-action 'none'");
        header('X-Robots-Tag: noindex');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The complete document contains only plugin markup and allowlisted sanitized content.
        echo self::document(self::sanitize(get_post_meta($id, '_vibecast_code', true)), true, $post->post_title);
        exit;
    }

    public static function revision_diff(array $diffs, $from, $to): array {
        $parent = 'revision' === $to->post_type ? $to->post_parent : $to->ID;
        if ('cr_vibecast' !== get_post_type($parent)) {
            return $diffs;
        }
        foreach (['_vibecast_code' => __('HTML content', 'vibecast'), '_vibecast_heights' => __('Responsive heights', 'vibecast')] as $key => $label) {
            $before = $from ? get_post_meta($from->ID, $key, true) : '';
            $after = get_post_meta($to->ID, $key, true);
            if ('_vibecast_heights' === $key) {
                $before = $from ? wp_json_encode(self::sanitize_heights($before), JSON_PRETTY_PRINT) : '';
                $after = wp_json_encode(self::sanitize_heights($after), JSON_PRETTY_PRINT);
            }
            // WordPress's diff renderer escapes the compared text, including HTML source.
            $diff = wp_text_diff((string) $before, (string) $after, ['show_split_view' => true]);
            if ($diff) {
                $diffs[] = ['id' => $key, 'name' => $label, 'diff' => $diff];
            }
        }
        return $diffs;
    }

    public static function skill_menu(): void {
        $hook = add_submenu_page('edit.php?post_type=cr_vibecast', __('Agent skill', 'vibecast'), __('Agent skill', 'vibecast'), 'manage_options', 'vibecast-skill', [self::class, 'skill_page']);
        add_action('admin_enqueue_scripts', static function ($screen) use ($hook) {
            if ($screen === $hook) {
                wp_enqueue_script('vibecast-skill', plugins_url('assets/skill.js', __FILE__), ['wp-i18n'], '1.0.0', true);
                wp_set_script_translations('vibecast-skill', 'vibecast');
            }
        });
    }

    public static function sample_id(): int {
        $id = absint(get_option('vibecast_first_cast_id', 0));
        $post = $id ? get_post($id) : null;
        return $post && 'cr_vibecast' === $post->post_type && 'trash' !== $post->post_status ? $id : 0;
    }

    public static function create_sample() {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('vibecast_forbidden', __('You cannot create a Vibecast.', 'vibecast'));
        }
        $existing = self::sample_id();
        if ($existing) {
            return $existing;
        }
        $html = file_get_contents(__DIR__ . '/examples/my-first-vibecast.html');
        $heights = json_decode(file_get_contents(__DIR__ . '/examples/my-first-vibecast-heights.json'), true);
        if (false === $html || !is_array($heights)) {
            return new \WP_Error('vibecast_sample_missing', __('The bundled example could not be read.', 'vibecast'));
        }
        $id = wp_insert_post(wp_slash([
            'post_type' => 'cr_vibecast', 'post_status' => 'draft',
            'post_title' => __('My First Vibecast', 'vibecast'),
            'post_author' => get_current_user_id(),
            'meta_input' => ['_vibecast_code' => self::sanitize($html), '_vibecast_heights' => self::sanitize_heights($heights)],
        ]), true);
        if (!is_wp_error($id)) {
            update_option('vibecast_first_cast_id', $id, false);
        }
        return $id;
    }

    public static function install_sample(): void {
        if (!current_user_can('manage_options') || 'POST' !== sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'] ?? ''))) {
            wp_die(esc_html__('You cannot create a Vibecast.', 'vibecast'), '', ['response' => 403]);
        }
        check_admin_referer('vibecast_create_sample');
        $id = self::create_sample();
        if (is_wp_error($id)) {
            wp_die(esc_html($id->get_error_message()));
        }
        wp_safe_redirect(admin_url('post.php?post=' . absint($id) . '&action=edit'));
        exit;
    }

    public static function skill_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $instructions = file_get_contents(__DIR__ . '/skills/vibecast/SKILL.md');
        echo '<div class="wrap"><h1>' . esc_html__('Vibecast agent skill', 'vibecast') . '</h1>';
        echo '<p>' . esc_html__('Give these instructions to your AI tool, describe the content you want, then paste the generated HTML into a new Vibecast. Save a draft to preview it before publishing.', 'vibecast') . '</p>';
        echo '<p>' . esc_html__('Copy the instructions into a conversation, or download SKILL.md for an AI tool that supports skills. No AI account is connected to WordPress.', 'vibecast') . '</p>';
        $sample = self::sample_id();
        echo '<h2>' . esc_html__('Try a living example', 'vibecast') . '</h2>';
        echo '<p>' . esc_html__('My First Vibecast explains the idea while demonstrating it. It includes HTML and measured responsive heights. Create it as a draft, explore the preview, then make it your own.', 'vibecast') . '</p>';
        if ($sample) {
            echo '<p><a class="button" href="' . esc_url(admin_url('post.php?post=' . $sample . '&action=edit')) . '">' . esc_html__('Open My First Vibecast', 'vibecast') . '</a></p>';
        } else {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="vibecast_create_sample">';
            wp_nonce_field('vibecast_create_sample');
            submit_button(__('Create My First Vibecast', 'vibecast'), 'secondary', 'submit', false);
            echo '</form>';
        }
        echo '<h2>' . esc_html__('Create your own with an AI tool', 'vibecast') . '</h2>';
        echo '<p><button type="button" class="button button-primary" id="vibecast-copy-skill">' . esc_html__('Copy instructions', 'vibecast') . '</button> ';
        echo '<a class="button" href="' . esc_url(plugins_url('skills/vibecast/SKILL.md', __FILE__)) . '" download="SKILL.md">' . esc_html__('Download SKILL.md', 'vibecast') . '</a></p>';
        echo '<p id="vibecast-copy-status" role="status" aria-live="polite"></p>';
        echo '<label for="vibecast-skill">' . esc_html__('Authoring instructions', 'vibecast') . '</label>';
        echo '<textarea id="vibecast-skill" class="large-text code" rows="24" readonly>' . esc_textarea($instructions) . '</textarea></div>';
    }

    public static function boxes(): void {
        add_meta_box('vibecast-html', __('Reusable HTML', 'vibecast'), [self::class, 'editor'], 'cr_vibecast', 'normal', 'high');
    }

    public static function editor(\WP_Post $post): void {
        wp_nonce_field('vibecast_save', 'vibecast_nonce');
        echo '<p><a href="' . esc_url(admin_url('edit.php?post_type=cr_vibecast&page=vibecast-skill')) . '">' . esc_html__('Get the agent skill to create compatible HTML with your AI tool.', 'vibecast') . '</a></p>';
        echo '<p>' . esc_html__('Paste HTML with inline styles. Scripts, event handlers, stylesheet blocks, forms, and embeds are removed. Save to refresh the preview.', 'vibecast') . '</p>';
        echo '<label class="screen-reader-text" for="vibecast-html">' . esc_html__('HTML content', 'vibecast') . '</label>';
        echo '<textarea id="vibecast-html" name="vibecast_code" class="large-text code" rows="20">' . esc_textarea(get_post_meta($post->ID, '_vibecast_code', true)) . '</textarea>';
        $heights = self::sanitize_heights(get_post_meta($post->ID, '_vibecast_heights', true));
        echo '<h3>' . esc_html__('Responsive heights', 'vibecast') . '</h3>';
        echo '<p>' . esc_html__('Enter the four pixel heights supplied with your HTML. Breakpoints use the available content width, not the browser width. Internal scrollbars are disabled: a height that is too short clips content. Check the saved preview and actual placement, including expanded disclosures.', 'vibecast') . '</p>';
        foreach (['base' => __('Base: below 640px', 'vibecast'), 'sm' => __('Small: 640–767px', 'vibecast'), 'md' => __('Medium: 768–1023px', 'vibecast'), 'lg' => __('Large: 1024px and above', 'vibecast')] as $key => $label) {
            echo '<p><label for="vibecast-height-' . esc_attr($key) . '">' . esc_html($label) . '</label> <input type="number" min="100" max="10000" required id="vibecast-height-' . esc_attr($key) . '" name="vibecast_heights[' . esc_attr($key) . ']" value="' . esc_attr($heights[$key]) . '"> ' . esc_html__('px', 'vibecast') . '</p>';
        }
        echo '<p>' . esc_html__('After publishing, use the Vibecast block with this ID, or copy this shortcode. Updates affect every placement.', 'vibecast') . '</p>';
        echo '<code>[cr_vibecast id="' . absint($post->ID) . '"]</code>';
        echo '<h3>' . esc_html__('Saved preview', 'vibecast') . '</h3>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() sanitizes content and escapes all iframe attributes.
        echo self::render($post->ID, 0, true);
    }

    public static function save(int $id): void {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('manage_options') || !current_user_can('edit_post', $id)) {
            return;
        }
        if (!isset($_POST['vibecast_nonce'], $_POST['vibecast_code']) || !is_string($_POST['vibecast_nonce']) || !is_string($_POST['vibecast_code']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['vibecast_nonce'])), 'vibecast_save')) {
            return;
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitize() applies an explicit wp_kses allowlist and safe inline CSS filtering.
        update_post_meta($id, '_vibecast_code', wp_slash(self::sanitize(wp_unslash($_POST['vibecast_code']))));
        if (isset($_POST['vibecast_heights']) && is_array($_POST['vibecast_heights'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitize_heights() accepts only four bounded numeric values.
            update_post_meta($id, '_vibecast_heights', self::sanitize_heights(wp_unslash($_POST['vibecast_heights'])));
        }
    }
}

add_action('init', [Plugin::class, 'register']);
add_filter('wp_get_revision_ui_diff', [Plugin::class, 'revision_diff'], 10, 3);
add_action('admin_post_vibecast_full', [Plugin::class, 'full_view']);
add_action('admin_post_nopriv_vibecast_full', [Plugin::class, 'full_view']);
add_action('enqueue_block_editor_assets', [Plugin::class, 'editor_data']);
add_action('admin_menu', [Plugin::class, 'skill_menu']);
add_action('admin_post_vibecast_create_sample', [Plugin::class, 'install_sample']);
add_action('add_meta_boxes_cr_vibecast', [Plugin::class, 'boxes']);
add_action('save_post_cr_vibecast', [Plugin::class, 'save']);
add_filter('use_block_editor_for_post_type', static function ($use, $type) { return 'cr_vibecast' === $type ? false : $use; }, 10, 2);
