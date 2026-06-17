<?php
/**
 * GitHub Theme Updater
 *
 * Cek update otomatis dari GitHub Releases.
 * Cara kerja:
 *   1. Fetch latest release dari GitHub API → cache 12 jam
 *   2. Bandingkan tag_name dengan Version di style.css
 *   3. Kalau lebih baru → inject ke WP update transient → muncul di Appearance > Themes
 *   4. Tombol "Periksa Update" di Themes screen → hapus cache → cek ulang
 *
 * Workflow rilis:
 *   git tag v1.1.0 && git push origin v1.1.0
 *   → buat GitHub Release → upload hastaaksara.zip sebagai release asset
 */

defined( 'ABSPATH' ) || exit;

const HASTA_GITHUB_USER = 'webaneid';
const HASTA_GITHUB_REPO = 'hastaaksara';
const HASTA_THEME_SLUG  = 'hastaaksara';
const HASTA_UPDATE_CACHE_KEY = 'hasta_github_release';

// ── 1. Fetch release dari GitHub API (dengan cache) ──────────────

function hasta_github_get_release() {
    $cached = get_transient( HASTA_UPDATE_CACHE_KEY );
    if ( false !== $cached ) return $cached;

    $response = wp_remote_get(
        'https://api.github.com/repos/' . HASTA_GITHUB_USER . '/' . HASTA_GITHUB_REPO . '/releases/latest',
        [
            'timeout' => 10,
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ],
        ]
    );

    if ( is_wp_error( $response ) ) return false;
    if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) return false;

    $release = json_decode( wp_remote_retrieve_body( $response ) );
    if ( empty( $release->tag_name ) ) return false;

    set_transient( HASTA_UPDATE_CACHE_KEY, $release, 12 * HOUR_IN_SECONDS );
    return $release;
}

// ── 2. Inject ke WP update system ────────────────────────────────

add_filter( 'pre_set_site_transient_update_themes', 'hasta_github_update_check' );

function hasta_github_update_check( $transient ) {
    if ( empty( $transient->checked ) ) return $transient;

    $release = hasta_github_get_release();
    if ( ! $release ) return $transient;

    $latest  = ltrim( $release->tag_name, 'v' );
    $current = wp_get_theme( HASTA_THEME_SLUG )->get( 'Version' );

    if ( ! version_compare( $latest, $current, '>' ) ) return $transient;

    // Cari ZIP asset di release (prioritas: hastaaksara.zip)
    $package = '';
    if ( ! empty( $release->assets ) ) {
        foreach ( $release->assets as $asset ) {
            if ( str_ends_with( strtolower( $asset->name ), '.zip' ) ) {
                $package = $asset->browser_download_url;
                break;
            }
        }
    }
    // Fallback: zipball dari GitHub (folder mungkin perlu rename manual)
    if ( ! $package ) $package = $release->zipball_url;

    $transient->response[ HASTA_THEME_SLUG ] = [
        'theme'       => HASTA_THEME_SLUG,
        'new_version' => $latest,
        'url'         => $release->html_url,
        'package'     => $package,
    ];

    return $transient;
}

// Bersihkan cache setelah update berhasil
add_action( 'upgrader_process_complete', 'hasta_github_clear_after_update', 10, 2 );

function hasta_github_clear_after_update( $upgrader, $hook_extra ) {
    if ( isset( $hook_extra['themes'] ) && in_array( HASTA_THEME_SLUG, (array) $hook_extra['themes'], true ) ) {
        delete_transient( HASTA_UPDATE_CACHE_KEY );
    }
}

// ── 3. Update section (dipanggil dari hasta_settings_page) ───────

function hasta_updater_section() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $release     = hasta_github_get_release();
    $current     = wp_get_theme( HASTA_THEME_SLUG )->get( 'Version' );
    $latest      = $release ? ltrim( $release->tag_name, 'v' ) : null;
    $has_update  = $latest && version_compare( $latest, $current, '>' );
    $release_url = $release->html_url ?? ( 'https://github.com/' . HASTA_GITHUB_USER . '/' . HASTA_GITHUB_REPO . '/releases' );
    $check_url   = wp_nonce_url(
        add_query_arg( 'hasta_check_update', '1', admin_url( 'admin.php?page=hasta-aksara-settings' ) ),
        'hasta_check_update'
    );
    $cache_ttl   = get_option( '_transient_timeout_' . HASTA_UPDATE_CACHE_KEY );
    $next_check  = $cache_ttl ? human_time_diff( time(), (int) $cache_ttl ) : null;
    ?>
    <div style="background:#fff;border:1px solid #c3c4c7;padding:20px 24px;max-width:600px;">
      <table class="form-table" style="margin-top:0;">
          <tr>
            <th style="width:160px;">Versi saat ini</th>
            <td><strong>v<?php echo esc_html( $current ); ?></strong></td>
          </tr>
          <tr>
            <th>Versi terbaru</th>
            <td>
              <?php if ( $latest ) : ?>
                <strong>v<?php echo esc_html( $latest ); ?></strong>
                <?php if ( $has_update ) : ?>
                  &nbsp;<span style="color:#d63638;">● Update tersedia</span>
                  &nbsp;<a href="<?php echo esc_url( $release_url ); ?>" target="_blank" rel="noopener noreferrer">Lihat changelog</a>
                <?php else : ?>
                  &nbsp;<span style="color:#46b450;">✓ Sudah terbaru</span>
                <?php endif; ?>
              <?php else : ?>
                <span style="color:#888;">Tidak dapat terhubung ke GitHub</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ( $next_check ) : ?>
          <tr>
            <th>Cache update</th>
            <td style="color:#888;font-size:13px;">
              Diperbarui otomatis dalam <?php echo esc_html( $next_check ); ?>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <th>Repository</th>
            <td>
              <a href="https://github.com/<?php echo esc_attr( HASTA_GITHUB_USER . '/' . HASTA_GITHUB_REPO ); ?>" target="_blank" rel="noopener noreferrer">
                github.com/<?php echo esc_html( HASTA_GITHUB_USER . '/' . HASTA_GITHUB_REPO ); ?>
              </a>
            </td>
          </tr>
        </table>

        <p style="margin-bottom:0;">
          <a href="<?php echo esc_url( $check_url ); ?>" class="button button-secondary">
            ↻ Periksa Update Sekarang
          </a>
          <?php if ( $has_update ) : ?>
            &nbsp;
            <a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>" class="button button-primary">
              Pergi ke Halaman Update
            </a>
          <?php endif; ?>
        </p>
    </div>
    <?php
}

// ── 4. Handle tombol "Periksa Update Sekarang" ───────────────────

add_action( 'admin_init', 'hasta_github_handle_check' );

function hasta_github_handle_check() {
    if ( empty( $_GET['hasta_check_update'] ) ) return;
    if ( ! current_user_can( 'update_themes' ) ) return;
    if ( ! check_admin_referer( 'hasta_check_update' ) ) return;

    delete_transient( HASTA_UPDATE_CACHE_KEY );
    delete_site_transient( 'update_themes' );

    wp_safe_redirect( admin_url( 'admin.php?page=hasta-aksara-settings' ) );
    exit;
}
