<?php

require get_template_directory() . '/inc/github-updater.php';

// Shim get_field() — hanya aktif jika ACF tidak terinstall sama sekali.
// Cek file di disk (bukan function_exists) agar tidak konflik saat aktivasi ACF:
// saat aktivasi, ACF belum di active_plugins tapi filenya sudah ada.
if ( ! function_exists( 'get_field' ) ) {
    $acf_exists = file_exists( WP_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php' )
               || file_exists( WP_PLUGIN_DIR . '/advanced-custom-fields/acf.php' );

    if ( ! $acf_exists ) {
        function get_field( $field, $post_id = false ) {
            if ( 'option' === $post_id || 'options' === $post_id ) {
                return get_option( 'options_' . $field );
            }
            $id = $post_id ?: get_the_ID();
            return maybe_unserialize( get_post_meta( $id, $field, true ) );
        }
    }
}

// Convert URL dalam teks plain menjadi <a> tag
function hasta_linkify( $text ) {
    $escaped = esc_html( $text );
    $linked  = preg_replace(
        '#(https?://[^\s<>"\']+)#i',
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary underline hover:opacity-70 transition-opacity">$1</a>',
        $escaped
    );
    return nl2br( $linked );
}

// Izinkan upload ZIP di media library
add_filter( 'upload_mimes', function ( $mimes ) {
    $mimes['zip'] = 'application/zip';
    return $mimes;
} );

function hasta_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [
        'height'      => 120,
        'width'       => 300,
        'flex-width'  => true,
        'flex-height' => true,
        'header-text' => [ 'site-title', 'site-description' ],
    ] );
}
add_action( 'after_setup_theme', 'hasta_setup' );

// ─── Helpers: baca setting (native WP options, fallback WP custom logo) ──
function hasta_get_logo_url() {
    $url = get_option( 'hasta_logo' );
    if ( $url ) return $url;
    $id  = get_theme_mod( 'custom_logo' );
    if ( $id ) { $img = wp_get_attachment_image_src( $id, 'full' ); return $img ? $img[0] : null; }
    return null;
}

function hasta_get_logo_right_url() {
    return get_option( 'hasta_logo_right' ) ?: null;
}

function hasta_get_ornament_url() {
    return get_option( 'hasta_ornament' )
        ?: get_template_directory_uri() . '/assets/images/ornament.svg';
}

function hasta_get_tagline_right() {
    return get_option( 'hasta_tagline_right' ) ?: 'IKPM&nbsp;&nbsp;GONTOR';
}

// ─── Native Admin Settings Page ──────────────────────────
function hasta_admin_menu() {
    add_menu_page(
        'Hasta Aksara Settings',
        'Hasta Aksara',
        'manage_options',
        'hasta-aksara-settings',
        'hasta_settings_page',
        'dashicons-editor-textcolor',
        3
    );
}
add_action( 'admin_menu', 'hasta_admin_menu' );

function hasta_register_settings() {
    foreach ( [ 'hasta_logo_right', 'hasta_ornament', 'hasta_tagline_right', 'hasta_instagram', 'hasta_footer_copyright' ] as $opt ) {
        register_setting( 'hasta_options', $opt, [ 'sanitize_callback' => 'sanitize_text_field' ] );
    }
}
add_action( 'admin_init', 'hasta_register_settings' );

function hasta_admin_enqueue( $hook ) {
    if ( 'toplevel_page_hasta-aksara-settings' !== $hook ) return;
    wp_enqueue_media();
    wp_add_inline_script( 'jquery-core', '
        (function($){
            $(document).on("click", ".hasta-media-btn", function(e){
                e.preventDefault();
                var target = $(this).data("target");
                var preview = $(this).data("preview");
                var frame = wp.media({ title: "Pilih Gambar", button: { text: "Gunakan Gambar" }, multiple: false });
                frame.on("select", function(){
                    var att = frame.state().get("selection").first().toJSON();
                    $("#" + target).val(att.url);
                    $("#" + preview).attr("src", att.url).show();
                });
                frame.open();
            });
            $(document).on("click", ".hasta-media-remove", function(e){
                e.preventDefault();
                var target = $(this).data("target");
                var preview = $(this).data("preview");
                $("#" + target).val("");
                $("#" + preview).hide().attr("src","");
            });
        })(jQuery);
    ' );
}
add_action( 'admin_enqueue_scripts', 'hasta_admin_enqueue' );

function hasta_settings_page() {
    $logo_right          = get_option( 'hasta_logo_right', '' );
    $ornament            = get_option( 'hasta_ornament', '' );
    $tagline             = get_option( 'hasta_tagline_right', '' );
    $instagram           = get_option( 'hasta_instagram', '' );
    $footer_copyright    = get_option( 'hasta_footer_copyright', '' );
    ?>
    <div class="wrap">
      <h1>Hasta Aksara — Settings</h1>
      <p style="color:#666;margin-bottom:20px;">
        Logo utama (kiri) diatur di <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>">Appearance → Customize → Site Identity</a>.
      </p>
      <form method="post" action="options.php">
        <?php settings_fields( 'hasta_options' ); ?>
        <table class="form-table" role="presentation">

          <tr>
            <th scope="row"><label>Logo IKPM Gontor <br><small style="font-weight:normal;color:#888;">(kanan header)</small></label></th>
            <td>
              <?php if ( $logo_right ) : ?>
                <img id="prev_hasta_logo_right" src="<?php echo esc_url( $logo_right ); ?>"
                     style="max-height:80px;display:block;margin-bottom:8px;">
              <?php else : ?>
                <img id="prev_hasta_logo_right" src="" style="max-height:80px;display:none;margin-bottom:8px;">
              <?php endif; ?>
              <input type="hidden" id="hasta_logo_right" name="hasta_logo_right"
                     value="<?php echo esc_attr( $logo_right ); ?>">
              <button class="button hasta-media-btn" data-target="hasta_logo_right" data-preview="prev_hasta_logo_right">
                <?php echo $logo_right ? 'Ganti Gambar' : 'Upload / Pilih Gambar'; ?>
              </button>
              <?php if ( $logo_right ) : ?>
                <button class="button hasta-media-remove" data-target="hasta_logo_right" data-preview="prev_hasta_logo_right"
                        style="margin-left:4px;">Hapus</button>
              <?php endif; ?>
              <p class="description">Jika diisi, menggantikan tulisan "IKPM GONTOR" di kanan header. Gunakan PNG/SVG dengan background transparan.</p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label>Teks Kanan Header</label></th>
            <td>
              <input type="text" name="hasta_tagline_right" value="<?php echo esc_attr( $tagline ); ?>"
                     class="regular-text" placeholder="IKPM  GONTOR">
              <p class="description">Teks fallback jika Logo Kanan tidak diisi. Default: IKPM GONTOR</p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label>Background Ornament</label></th>
            <td>
              <?php if ( $ornament ) : ?>
                <img id="prev_hasta_ornament" src="<?php echo esc_url( $ornament ); ?>"
                     style="max-height:80px;display:block;margin-bottom:8px;">
              <?php else : ?>
                <img id="prev_hasta_ornament" src="" style="max-height:80px;display:none;margin-bottom:8px;">
              <?php endif; ?>
              <input type="hidden" id="hasta_ornament" name="hasta_ornament"
                     value="<?php echo esc_attr( $ornament ); ?>">
              <button class="button hasta-media-btn" data-target="hasta_ornament" data-preview="prev_hasta_ornament">
                <?php echo $ornament ? 'Ganti Gambar' : 'Upload / Pilih Gambar'; ?>
              </button>
              <?php if ( $ornament ) : ?>
                <button class="button hasta-media-remove" data-target="hasta_ornament" data-preview="prev_hasta_ornament"
                        style="margin-left:4px;">Hapus</button>
              <?php endif; ?>
              <p class="description">Ornamen background halaman. Kosongkan untuk pakai ornament default.</p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="hasta_instagram">URL Instagram</label></th>
            <td>
              <input type="url" id="hasta_instagram" name="hasta_instagram"
                     value="<?php echo esc_attr( $instagram ); ?>"
                     class="regular-text" placeholder="https://instagram.com/hastaaksara">
              <p class="description">Tampil sebagai icon Instagram di footer. Contoh: https://instagram.com/hastaaksara</p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="hasta_footer_copyright">Teks Copyright</label></th>
            <td>
              <input type="text" id="hasta_footer_copyright" name="hasta_footer_copyright"
                     value="<?php echo esc_attr( $footer_copyright ); ?>"
                     class="regular-text" placeholder="© <?php echo esc_attr( gmdate('Y') ); ?> Hasta Aksara">
              <p class="description">Kosongkan untuk menggunakan default: <code>© <?php echo esc_html( gmdate('Y') ); ?> <?php bloginfo('name'); ?></code></p>
            </td>
          </tr>

        </table>
        <?php submit_button( 'Simpan Settings' ); ?>
      </form>

      <!-- ── Update Theme ── -->
      <hr style="margin:30px 0;">
      <h2>Update Theme</h2>
      <?php hasta_updater_section(); ?>

    </div>
    <?php
}

function hasta_enqueue_assets() {
    $ver = wp_get_theme()->get( 'Version' );
    wp_enqueue_style(
        'hasta-style',
        get_template_directory_uri() . '/assets/css/style.css',
        [],
        $ver
    );
    wp_enqueue_script(
        'hasta-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        $ver,
        true
    );
    if ( is_singular( 'font' ) ) {
        wp_enqueue_script(
            'hasta-font-preview',
            get_template_directory_uri() . '/assets/js/font-preview.js',
            [],
            $ver,
            true
        );
    }
    if ( is_post_type_archive( 'font' ) ) {
        wp_enqueue_script(
            'hasta-font-browse',
            get_template_directory_uri() . '/assets/js/font-browse.js',
            [],
            $ver,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'hasta_enqueue_assets' );

// ─── Bersihkan <head> dari output WP yang tidak perlu ────────────
remove_action( 'wp_head', 'feed_links',                       2 ); // RSS feed utama
remove_action( 'wp_head', 'feed_links_extra',                 3 ); // RSS feed kategori dll
remove_action( 'wp_head', 'rsd_link'                            ); // Really Simple Discovery
remove_action( 'wp_head', 'wlwmanifest_link'                    ); // Windows Live Writer
remove_action( 'wp_head', 'wp_shortlink_wp_head',            10 ); // Shortlink
remove_action( 'wp_head', 'wp_generator'                        ); // Versi WordPress
remove_action( 'wp_head', 'rest_output_link_wp_head',        10 ); // REST API link
remove_action( 'wp_head', 'wp_oembed_add_discovery_links',   10 ); // oEmbed discovery
remove_action( 'wp_head', 'print_emoji_detection_script',     7 ); // Emoji JS
remove_action( 'wp_print_styles', 'print_emoji_styles'          ); // Emoji CSS
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles'       );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 ); // Prev/next post link

// ─── Native Meta Box (fallback jika ACF Pro tidak aktif) ──
function hasta_register_font_metabox() {
    if ( function_exists( 'acf' ) ) return; // ACF aktif → skip, pakai ACF saja

    add_meta_box(
        'hasta_font_meta',
        'Font Metadata',
        'hasta_font_metabox_html',
        'font',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'hasta_register_font_metabox' );

function hasta_font_metabox_html( $post ) {
    wp_nonce_field( 'hasta_font_meta_save', 'hasta_font_meta_nonce' );
    $f = function( $key ) use ( $post ) {
        return get_post_meta( $post->ID, $key, true );
    };
    $weights_saved = (array) ( maybe_unserialize( get_post_meta( $post->ID, 'font_weights', true ) ) ?: [] );
    $license_opts  = [
        'ofl'        => 'SIL Open Font License 1.1 (OFL)',
        'apache'     => 'Apache License 2.0',
        'cc0'        => 'Public Domain / CC0',
        'freeware'   => 'Freeware (Personal Use)',
        'commercial' => 'Commercial License',
        'custom'     => 'Custom License',
    ];
    $status_opts = [ 'active' => 'Active', 'coming_soon' => 'Coming Soon', 'draft' => 'Draft' ];
    $weight_opts = [ '100' => 'Thin', '200' => 'ExtraLight', '300' => 'Light', '400' => 'Regular',
                     '500' => 'Medium', '600' => 'SemiBold', '700' => 'Bold', '800' => 'ExtraBold' ];
    ?>
    <style>
      .hasta-meta table { width:100%; border-collapse:collapse; }
      .hasta-meta th { width:200px; text-align:left; padding:10px 12px; font-weight:600; vertical-align:top; color:#444; font-size:13px; }
      .hasta-meta td { padding:8px 12px; vertical-align:top; }
      .hasta-meta tr { border-bottom:1px solid #f0f0f0; }
      .hasta-meta input[type=text],.hasta-meta input[type=number],.hasta-meta select,.hasta-meta textarea { width:100%;max-width:500px; }
      .hasta-meta .weight-grid { display:flex; flex-wrap:wrap; gap:8px; }
      .hasta-meta .weight-grid label { display:flex; align-items:center; gap:4px; font-size:13px; }
      .hasta-meta .section-head { background:#f9f9f9; font-size:11px; letter-spacing:.1em; text-transform:uppercase; color:#888; padding:8px 12px; }
    </style>
    <div class="hasta-meta">
    <table>
      <tr><td colspan="2" class="section-head">Identitas Font</td></tr>
      <tr>
        <th>CSS Font Family <span style="color:red">*</span></th>
        <td>
          <input type="text" name="font_family"
                 value="<?php echo esc_attr( $f('font_family') ?: $post->post_title ); ?>"
                 placeholder="<?php echo esc_attr( $post->post_title ); ?>">
          <p class="description">Nama untuk CSS <code>font-family</code>. Default: judul post. Ubah hanya jika berbeda dari judul.</p>
        </td>
      </tr>
      <tr>
        <th>Folder Fonts <span style="color:red">*</span></th>
        <td>
          <input type="text" name="font_folder" value="<?php echo esc_attr( $f('font_folder') ); ?>" placeholder="gontor" style="max-width:200px;">
          <p class="description">Nama subfolder di <code>fonts/</code>. Contoh: "gontor" → <code>fonts/gontor/otf/</code></p>
        </td>
      </tr>
      <tr>
        <th>Klasifikasi</th>
        <td>
          <input type="text" name="font_classification" value="<?php echo esc_attr( $f('font_classification') ); ?>" placeholder="Transitional Serif">
          <p class="description">Klasifikasi tipografi spesifik. Contoh: Transitional Serif, Humanist Sans, Geometric, Old Style</p>
        </td>
      </tr>
      <tr>
        <th>Foundry</th>
        <td><input type="text" name="font_foundry" value="<?php echo esc_attr( $f('font_foundry') ); ?>" placeholder="Forcreator IKPM Gontor"></td>
      </tr>
      <tr>
        <th>Desainer</th>
        <td><input type="text" name="font_designer" value="<?php echo esc_attr( $f('font_designer') ); ?>"></td>
      </tr>
      <tr>
        <th>Versi</th>
        <td><input type="text" name="font_version" value="<?php echo esc_attr( $f('font_version') ?: '1.0' ); ?>" style="max-width:100px;"></td>
      </tr>

      <tr><td colspan="2" class="section-head">Lisensi & Harga</td></tr>
      <tr>
        <th>Lisensi</th>
        <td>
          <select name="font_license">
            <?php foreach ( $license_opts as $val => $label ) : ?>
              <option value="<?php echo esc_attr($val); ?>" <?php selected( $f('font_license'), $val ); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      <tr>
        <th>Teks Lisensi (EN)</th>
        <td><textarea name="font_license_text" rows="4" placeholder="This Font Software is licensed under..."><?php echo esc_textarea( $f('font_license_text') ); ?></textarea></td>
      </tr>
      <tr>
        <th>File ZIP (Download)</th>
        <td>
          <input type="text" name="font_zip" id="font_zip_url"
                 value="<?php echo esc_attr( $f('font_zip') ); ?>"
                 placeholder="https://..." style="max-width:460px;">
          <button type="button" class="button" id="font_zip_picker">Pilih dari Media Library</button>
          <p class="description">Upload ZIP berisi semua file OTF. Gunakan Media Library (ZIP diizinkan).</p>
        </td>
      </tr>
      <tr>
        <th>Harga (Rp)</th>
        <td><input type="number" name="price" value="<?php echo esc_attr( $f('price') ?: '0' ); ?>" min="0" style="max-width:150px;">
        <p class="description">0 = gratis</p></td>
      </tr>

      <tr><td colspan="2" class="section-head">Tampilan & Preview</td></tr>
      <tr>
        <th>Teks Sample Preview</th>
        <td><textarea name="font_sample_text" rows="2" placeholder="Hampir sebelum kami menyadarinya..."><?php echo esc_textarea( $f('font_sample_text') ); ?></textarea></td>
      </tr>
      <tr>
      </tr>

      <tr><td colspan="2" class="section-head">Teknis</td></tr>
      <tr>
        <th>Units per Em (UPM)</th>
        <td><input type="text" name="font_upm" value="<?php echo esc_attr( $f('font_upm') ); ?>" placeholder="1926" style="max-width:120px;"></td>
      </tr>
      <tr>
        <th>Base Metrics</th>
        <td><textarea name="font_metrics" rows="4" placeholder="UPM: 1926&#10;Ascender: 1650&#10;Descender: -276&#10;Cap Height: 1280"><?php echo esc_textarea( $f('font_metrics') ); ?></textarea>
        <p class="description">Satu metrik per baris, format: Nama: Nilai</p></td>
      </tr>
      <tr>
        <th>Weights Tersedia</th>
        <td>
          <div class="weight-grid">
            <?php foreach ( $weight_opts as $val => $label ) : ?>
              <label>
                <input type="checkbox" name="font_weights[]" value="<?php echo esc_attr($val); ?>"
                       <?php checked( in_array( $val, $weights_saved, true ) ); ?>>
                <?php echo esc_html("$val — $label"); ?>
              </label>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
      <tr>
        <th>Status</th>
        <td>
          <select name="font_status">
            <?php foreach ( $status_opts as $val => $label ) : ?>
              <option value="<?php echo esc_attr($val); ?>" <?php selected( $f('font_status') ?: 'active', $val ); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
    </table>
    </div>
    <script>
    jQuery(function($){
        $('#font_zip_picker').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({
                title: 'Pilih File ZIP',
                button: { text: 'Gunakan file ini' },
                library: { type: 'application/zip' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#font_zip_url').val(attachment.url);
            });
            frame.open();
        });
    });
    </script>
    <?php
}

function hasta_font_metabox_save( $post_id ) {
    if ( function_exists( 'acf' ) ) return;
    if ( ! isset( $_POST['hasta_font_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hasta_font_meta_nonce'] ) ), 'hasta_font_meta_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $text_fields = [ 'font_family', 'font_folder', 'font_zip', 'font_classification', 'font_foundry',
                     'font_designer', 'font_version', 'font_license', 'font_status', 'font_upm' ];
    foreach ( $text_fields as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    $textarea_fields = [ 'font_sample_text', 'font_license_text', 'font_metrics' ];
    foreach ( $textarea_fields as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    if ( isset( $_POST['price'] ) ) {
        update_post_meta( $post_id, 'price', absint( $_POST['price'] ) );
    }

    $weights = isset( $_POST['font_weights'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['font_weights'] ) ) : [];
    update_post_meta( $post_id, 'font_weights', $weights );

}
add_action( 'save_post_font', 'hasta_font_metabox_save' );

function hasta_font_archive_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'font' ) ) {
        return;
    }
    $query->set( 'posts_per_page', 12 );
}
add_action( 'pre_get_posts', 'hasta_font_archive_query' );

function hasta_register_font_cpt() {
    $labels = [
        'name'               => 'Fonts',
        'singular_name'      => 'Font',
        'add_new'            => 'Tambah Font',
        'add_new_item'       => 'Tambah Font Baru',
        'edit_item'          => 'Edit Font',
        'new_item'           => 'Font Baru',
        'view_item'          => 'Lihat Font',
        'search_items'       => 'Cari Font',
        'not_found'          => 'Font tidak ditemukan',
        'not_found_in_trash' => 'Font tidak ditemukan di Trash',
        'menu_name'          => 'Fonts',
    ];

    register_post_type( 'font', [
        'labels'          => $labels,
        'public'          => true,
        'publicly_queryable' => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'query_var'       => true,
        'rewrite'         => [ 'slug' => 'font' ],
        'capability_type' => 'post',
        'has_archive'     => true,
        'hierarchical'    => false,
        'menu_position'   => 5,
        'menu_icon'       => 'dashicons-editor-textcolor',
        'supports'        => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'show_in_rest'    => true,
    ] );
}
add_action( 'init', 'hasta_register_font_cpt' );

// ─── Taxonomy: Kategori Font ──────────────────────────────
function hasta_register_font_taxonomy() {
    register_taxonomy( 'font_category', 'font', [
        'labels' => [
            'name'              => 'Kategori Font',
            'singular_name'     => 'Kategori Font',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Kategori Induk',
            'parent_item_colon' => 'Kategori Induk:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ],
        'hierarchical'      => true,   // seperti kategori, bukan tag
        'public'            => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'kategori-font', 'with_front' => false ],
        'query_var'         => true,
    ] );
}
add_action( 'init', 'hasta_register_font_taxonomy' );


// ─── ACF Pro — Font Meta Fields ──────────────────────────
function hasta_register_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'    => 'group_hasta_font_meta',
        'title'  => 'Font Metadata',
        'fields' => [
            [
                'key'          => 'field_font_family',
                'label'        => 'CSS Font Family',
                'name'         => 'font_family',
                'type'         => 'text',
                'instructions' => 'Nama font-family untuk CSS. Contoh: Gontor',
                'required'     => 1,
                'placeholder'  => 'Gontor',
            ],
            [
                'key'           => 'field_font_license',
                'label'         => 'Lisensi',
                'name'          => 'font_license',
                'type'          => 'select',
                'choices'       => [
                    'ofl'        => 'SIL Open Font License 1.1 (OFL)',
                    'apache'     => 'Apache License 2.0',
                    'cc0'        => 'Public Domain / CC0',
                    'freeware'   => 'Freeware (Personal Use)',
                    'commercial' => 'Commercial License',
                    'custom'     => 'Custom License',
                ],
                'default_value' => 'ofl',
                'allow_null'    => 0,
            ],
            [
                'key'           => 'field_font_weights',
                'label'         => 'Weights Tersedia',
                'name'          => 'font_weights',
                'type'          => 'checkbox',
                'choices'       => [
                    '100' => '100 — Thin',
                    '200' => '200 — ExtraLight',
                    '300' => '300 — Light',
                    '400' => '400 — Regular',
                    '500' => '500 — Medium',
                    '600' => '600 — SemiBold',
                    '700' => '700 — Bold',
                    '800' => '800 — ExtraBold',
                ],
                'default_value' => [ '400' ],
                'layout'        => 'horizontal',
                'toggle'        => 1,
            ],
            [
                'key'           => 'field_font_sample_text',
                'label'         => 'Teks Preview Card',
                'name'          => 'font_sample_text',
                'type'          => 'textarea',
                'rows'          => 2,
                'instructions'  => 'Kalimat yang tampil di kartu browse. Kosongkan untuk pakai default.',
                'default_value' => '',
                'placeholder'   => 'Hampir sebelum kami menyadarinya, kami telah meninggalkan tanah.',
            ],
            [
                'key'          => 'field_font_foundry',
                'label'        => 'Foundry',
                'name'         => 'font_foundry',
                'type'         => 'text',
                'placeholder'  => 'Forcreator, IKPM Gontor',
            ],
            [
                'key'         => 'field_font_designer',
                'label'       => 'Desainer',
                'name'        => 'font_designer',
                'type'        => 'text',
            ],
            [
                'key'         => 'field_font_upm',
                'label'       => 'Units per Em (UPM)',
                'name'        => 'font_upm',
                'type'        => 'text',
                'placeholder' => '1926',
            ],
            [
                'key'           => 'field_font_version',
                'label'         => 'Versi',
                'name'          => 'font_version',
                'type'          => 'text',
                'default_value' => '1.0',
                'placeholder'   => '1.0',
            ],
            [
                'key'           => 'field_font_status',
                'label'         => 'Status',
                'name'          => 'font_status',
                'type'          => 'select',
                'choices'       => [
                    'active'      => 'Active',
                    'coming_soon' => 'Coming Soon',
                    'draft'       => 'Draft',
                ],
                'default_value' => 'active',
                'allow_null'    => 0,
            ],
            [
                'key'           => 'field_font_zip',
                'label'         => 'File ZIP (Download)',
                'name'          => 'font_zip',
                'type'          => 'file',
                'return_format' => 'url',
                'mime_types'    => 'zip',
                'instructions'  => 'Upload ZIP berisi semua file OTF untuk didownload user.',
            ],
            [
                'key'           => 'field_font_specimen',
                'label'         => 'PDF Specimen Book',
                'name'          => 'font_specimen',
                'type'          => 'file',
                'return_format' => 'url',
                'mime_types'    => 'pdf',
                'instructions'  => 'Upload PDF specimen book (opsional)',
            ],
            [
                'key'           => 'field_price',
                'label'         => 'Harga',
                'name'          => 'price',
                'type'          => 'number',
                'instructions'  => '0 = gratis',
                'default_value' => 0,
                'min'           => 0,
                'prepend'       => 'Rp',
            ],
            // ── Field-field baru ────────────────────────────
            [
                'key'          => 'field_font_classification',
                'label'        => 'Klasifikasi',
                'name'         => 'font_classification',
                'type'         => 'text',
                'instructions' => 'Klasifikasi tipografi spesifik. Contoh: Transitional Serif, Humanist Sans, Geometric, Old Style',
                'placeholder'  => 'Transitional Serif',
            ],
            [
                'key'          => 'field_font_license_text',
                'label'        => 'Teks Lisensi (EN)',
                'name'         => 'font_license_text',
                'type'         => 'textarea',
                'instructions' => 'Teks lisensi resmi dalam bahasa Inggris (verbatim). Misal: teks SIL Open Font License.',
                'rows'         => 5,
                'placeholder'  => 'This Font Software is licensed under the SIL Open Font License, Version 1.1...',
            ],
            [
                'key'          => 'field_font_metrics',
                'label'        => 'Base Metrics',
                'name'         => 'font_metrics',
                'type'         => 'textarea',
                'instructions' => 'Metrik teknis font, satu per baris. Contoh: UPM: 1926 / Ascender: 1650 / Descender: -276 / Cap Height: 1280',
                'rows'         => 4,
                'placeholder'  => "UPM: 1926\nAscender: 1650\nDescender: -276\nCap Height: 1280\nx-Height: 895",
            ],
            [
                'key'           => 'field_font_license_image',
                'label'         => 'Badge Lisensi',
                'name'          => 'font_license_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Upload badge/logo lisensi (misal: badge OFL). Tampil di halaman detail font.',
            ],
        ],
        'location' => [
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'font' ] ],
        ],
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ] );
}
add_action( 'acf/init', 'hasta_register_acf_fields' );

// ─── ACF Options Page ─────────────────────────────────────
function hasta_register_options_page() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;

    acf_add_options_page( [
        'page_title'  => 'Hasta Aksara — Settings',
        'menu_title'  => 'Hasta Aksara',
        'menu_slug'   => 'hasta-aksara-settings',
        'capability'  => 'manage_options',
        'icon_url'    => 'dashicons-editor-textcolor',
        'position'    => 3,
        'redirect'    => false,
    ] );
}
add_action( 'acf/init', 'hasta_register_options_page' );

function hasta_register_options_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'    => 'group_hasta_theme_options',
        'title'  => 'Hasta Aksara — Theme Options',
        'fields' => [
            // ── Logo ──
            [
                'key'           => 'field_hasta_logo',
                'label'         => 'Logo Hasta Aksara',
                'name'          => 'hasta_logo',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Upload logo (PNG/SVG transparan). Prioritas di atas WordPress Custom Logo. Kosongkan untuk pakai WP Custom Logo atau fallback teks.',
            ],
            // ── Ornament ──
            [
                'key'           => 'field_hasta_ornament',
                'label'         => 'Background Ornament',
                'name'          => 'hasta_ornament',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Upload gambar ornamen background (SVG atau PNG transparan). Kosongkan untuk pakai ornament default dari theme.',
            ],
            // ── Logo kanan header (partner/organisasi) ──
            [
                'key'           => 'field_hasta_logo_right',
                'label'         => 'Logo Kanan Header',
                'name'          => 'hasta_logo_right',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Upload logo partner/organisasi (misal: logo IKPM Gontor). Jika diisi, menggantikan teks di kanan header. Format PNG/SVG transparan.',
            ],
            // ── Teks kanan header (fallback jika logo_right kosong) ──
            [
                'key'           => 'field_hasta_tagline_right',
                'label'         => 'Teks Kanan Header',
                'name'          => 'hasta_tagline_right',
                'type'          => 'text',
                'default_value' => 'IKPM  GONTOR',
                'instructions'  => 'Teks fallback jika Logo Kanan Header kosong. Default: IKPM  GONTOR',
            ],
        ],
        'location' => [
            [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'hasta-aksara-settings' ] ],
        ],
        'menu_order'      => 0,
        'style'           => 'default',
        'label_placement' => 'top',
    ] );
}
add_action( 'acf/init', 'hasta_register_options_fields' );
