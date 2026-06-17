<?php get_header();

if ( ! have_posts() ) { get_footer(); return; }
the_post();

// ── Semua data dari ACF Pro ──────────────────────────────
$font_zip           = get_field( 'font_zip' )              ?: '';
$font_family        = get_field( 'font_family' )          ?: get_the_title();
// Kategori dari taxonomy
$cat_terms          = get_the_terms( get_the_ID(), 'font_category' );
$category           = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->name : '';
$classification     = get_field( 'font_classification' )  ?: '';
$license            = get_field( 'font_license' ) ?: 'ofl';
$license_labels     = [
    'ofl'        => 'SIL Open Font License 1.1',
    'apache'     => 'Apache License 2.0',
    'cc0'        => 'Public Domain / CC0',
    'freeware'   => 'Freeware',
    'commercial' => 'Commercial License',
    'custom'     => 'Custom License',
];
$license_short      = [
    'ofl'        => 'OFL',
    'apache'     => 'Apache',
    'cc0'        => 'CC0',
    'freeware'   => 'Free',
    'commercial' => 'COM',
    'custom'     => 'Custom',
];
$license_display    = $license_labels[ $license ] ?? strtoupper( $license );
$license_abbr       = $license_short[ $license ]  ?? strtoupper( $license );
$license_text       = get_field( 'font_license_text' )    ?: '';
$license_image_raw  = get_field( 'font_license_image' );
$license_image_url  = is_array( $license_image_raw ) ? $license_image_raw['url'] : $license_image_raw;
$metrics_raw        = get_field( 'font_metrics' )         ?: '';
$version            = get_field( 'font_version' );
$designer           = get_field( 'font_designer' );
$foundry            = get_field( 'font_foundry' );
$upm                = get_field( 'font_upm' );
$weights            = (array) ( get_field( 'font_weights' ) ?: [] );
$sample_text        = get_field( 'font_sample_text' )     ?: 'Gontor Font';
$specimen_url       = get_field( 'font_specimen' );
$font_status        = get_field( 'font_status' )          ?: 'active';

$weight_labels = [
    100 => 'Thin',      200 => 'ExtraLight', 300 => 'Light',
    400 => 'Regular',   500 => 'Medium',     600 => 'SemiBold',
    700 => 'Bold',      800 => 'ExtraBold',
];

// Jika belum ada weights dari ACF, tampilkan semua
if ( empty( $weights ) ) {
    $weights = array_map( 'strval', array_keys( $weight_labels ) );
}

$styles_count = count( $weights ) * 2; // upright + italic
?>

<main class="bg-white min-h-screen text-[16px]">

  <!-- ── Hero: nama font besar pakai font itu sendiri ── -->
  <section class="px-6 lg:px-16 pt-36 pb-16 lg:pt-48 lg:pb-20 border-b border-dark/10">
    <p class="font-monotracking-[0.24em] uppercase text-dark/35 mb-3">
      <?php
        // Tampilkan klasifikasi jika ada, fallback ke kategori
        echo $classification
          ? esc_html( $classification )
          : esc_html( ucfirst( str_replace( '-', ' ', $category ) ) );
      ?>
    </p>
    <h1 class="text-dark leading-none tracking-[-0.025em]"
        style="font-family:'<?php echo esc_attr( $font_family ); ?>';font-size:clamp(48px,8vw,100px);">
      <?php the_title(); ?>
    </h1>
    <div class="flex flex-wrap items-center gap-3 mt-6">
      <span class="font-monotracking-[0.18em] uppercase border border-dark/20 px-3 py-1.5 text-dark/45">
        <?php echo esc_html( $license_abbr ); ?>
      </span>
      <?php if ( $styles_count ) : ?>
        <span class="font-monotext-dark/30">
          <?php echo esc_html( $styles_count ); ?> styles
        </span>
      <?php endif; ?>
      <?php if ( $version ) : ?>
        <span class="font-monotext-dark/25">v<?php echo esc_html( $version ); ?></span>
      <?php endif; ?>
      <?php if ( $font_status === 'coming_soon' ) : ?>
        <span class="font-monotracking-[0.14em] uppercase text-primary">Segera Hadir</span>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── Tab nav (sticky) ── -->
  <div class="sticky top-14 z-30 bg-white border-b border-dark/10 px-6 lg:px-16">
    <div class="flex gap-6 lg:gap-8 -mb-px">
      <?php foreach ( [
        'overview' => 'Overview',
        'specimen' => 'Specimen',
        'preview'  => 'Preview',
        'download' => 'Download',
      ] as $tid => $tlabel ) : ?>
        <button type="button"
                class="font-mono tracking-[0.12em] uppercase py-4 border-b-2 border-transparent text-dark/40 hover:text-dark transition-colors"
                data-tab="<?php echo esc_attr( $tid ); ?>"
                data-tab-trigger>
          <?php echo esc_html( $tlabel ); ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Tab: Overview ── -->
  <div id="tab-overview" class="px-6 lg:px-16 py-14" data-tab-panel="overview">
    <div>

      <!-- Konten post (editor WP) -->
      <?php if ( get_the_content() ) : ?>
        <div class="font-mono leading-[1.9] text-dark/60 mb-10
                    [&>p]:mb-6 [&>p:last-child]:mb-0
                    [&>h2]:font-gontor [&>h2]:text-xl [&>h2]:text-dark [&>h2]:mt-10 [&>h2]:mb-4
                    [&>h3]:font-gontor [&>h3]:text-lg [&>h3]:text-dark [&>h3]:mt-8 [&>h3]:mb-3
                    [&>ul]:mb-6 [&>ul]:pl-5 [&>ul>li]:list-disc [&>ul>li]:mb-2
                    [&>ol]:mb-6 [&>ol]:pl-5 [&>ol>li]:list-decimal [&>ol>li]:mb-2">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>


      <!-- Metadata grid -->
      <dl class="grid grid-cols-2 sm:grid-cols-3 gap-6 border-t border-dark/10 pt-8 mb-10">
        <?php if ( $classification ) : ?>
          <div>
            <dt class="font-monotracking-[0.18em] uppercase text-dark/30 mb-1">Klasifikasi</dt>
            <dd class="font-gontor text-dark"><?php echo esc_html( $classification ); ?></dd>
          </div>
        <?php endif; ?>
        <?php if ( $foundry ) : ?>
          <div>
            <dt class="font-monotracking-[0.18em] uppercase text-dark/30 mb-1">Foundry</dt>
            <dd class="font-gontor text-dark"><?php echo esc_html( $foundry ); ?></dd>
          </div>
        <?php endif; ?>
        <?php if ( $designer ) : ?>
          <div>
            <dt class="font-monotracking-[0.18em] uppercase text-dark/30 mb-1">Desainer</dt>
            <dd class="font-gontor text-dark"><?php echo esc_html( $designer ); ?></dd>
          </div>
        <?php endif; ?>
        <div>
          <dt class="font-monotracking-[0.18em] uppercase text-dark/30 mb-1">Lisensi</dt>
          <dd class="font-gontor text-dark"><?php echo esc_html( $license_abbr ); ?></dd>
        </div>
      </dl>

      <!-- Base Metrics -->
      <?php
        $metrics_lines = array_filter( array_map( 'trim', explode( "\n", $metrics_raw ) ) );
        // Fallback: jika font_metrics kosong tapi ada font_upm
        if ( empty( $metrics_lines ) && $upm ) {
            $metrics_lines = [ 'UPM: ' . $upm ];
        }
      ?>
      <?php if ( $metrics_lines ) : ?>
        <div class="border-t border-dark/10 pt-8 mb-10">
          <p class="font-monotracking-[0.22em] uppercase text-dark/30 mb-4">Base Metrics</p>
          <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <?php foreach ( $metrics_lines as $line ) :
              $parts = explode( ':', $line, 2 );
              if ( count( $parts ) !== 2 ) continue;
              [$key, $val] = $parts;
            ?>
              <div>
                <dt class="font-monotext-dark/30 mb-0.5"><?php echo esc_html( trim( $key ) ); ?></dt>
                <dd class="font-gontor text-[22px] text-dark"><?php echo esc_html( trim( $val ) ); ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </div>
      <?php endif; ?>

      <!-- Teks Lisensi (EN) + Badge -->
      <?php if ( $license_text || $license_image_url ) : ?>
        <div class="border-t border-dark/10 pt-8 flex flex-col sm:flex-row gap-6 items-start">
          <?php if ( $license_image_url ) : ?>
            <img src="<?php echo esc_url( $license_image_url ); ?>"
                 alt="<?php echo esc_attr( $license_display ); ?> Badge"
                 class="flex-none h-16 w-auto object-contain">
          <?php endif; ?>
          <?php if ( $license_text ) : ?>
            <p class="font-mono leading-[1.85] text-dark/40">
              <?php echo nl2br( esc_html( $license_text ) ); ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- ── Tab: Specimen — Weight Matrix ── -->
  <div id="tab-specimen" class="px-6 lg:px-16 py-14 hidden" data-tab-panel="specimen">
    <div class="flex items-end justify-between mb-6">
      <h2 class="font-gontor text-2xl lg:text-3xl font-semibold text-dark tracking-tight">
        <?php echo esc_html( $styles_count ); ?> Styles Family Matrix
      </h2>
      <span class="font-monotracking-[0.16em] text-dark/35 uppercase">48pt</span>
    </div>
    <div>
      <?php foreach ( $weight_labels as $weight => $label ) :
        // Cek apakah weight ini tersedia (ACF simpan sebagai string)
        if ( ! in_array( (string) $weight, $weights, true ) ) continue;
        $w         = esc_attr( $weight );
        $f         = esc_attr( $font_family );
        $row_style = "font-family:'{$f}';font-size:clamp(28px,4.5vw,52px);font-weight:{$w};";
      ?>
        <!-- Upright -->
        <div class="flex items-baseline gap-6 lg:gap-10 py-3 lg:py-[14px] border-t border-dark/10">
          <span class="flex-none w-10 font-mono text-[11px] text-dark/30"><?php echo esc_html( $weight ); ?></span>
          <span class="flex-1 min-w-0 overflow-hidden text-ellipsis whitespace-nowrap text-dark leading-none"
                style="<?php echo $row_style; ?>"><?php echo esc_html( $sample_text ); ?></span>
          <span class="flex-none font-mono text-[11px] text-dark/30 text-right"><?php echo esc_html( $label ); ?></span>
        </div>
        <!-- Italic -->
        <div class="flex items-baseline gap-6 lg:gap-10 py-3 lg:py-[14px] border-t border-dark/[0.05]">
          <span class="flex-none w-10 font-mono text-[11px] text-dark/15"><?php echo esc_html( $weight ); ?>i</span>
          <span class="flex-1 min-w-0 overflow-hidden text-ellipsis whitespace-nowrap text-dark/80 leading-none italic"
                style="<?php echo $row_style; ?>"><?php echo esc_html( $sample_text ); ?></span>
          <span class="flex-none font-mono text-[11px] text-dark/30 text-right"><?php echo esc_html( $label ); ?> Italic</span>
        </div>
      <?php endforeach; ?>
      <div class="border-t border-dark/10"></div>
    </div>
  </div>

  <!-- ── Tab: Preview interaktif ── -->
  <div id="tab-preview" class="px-6 lg:px-16 py-14 hidden" data-tab-panel="preview">
    <div>

      <!-- Controls -->
      <div class="flex flex-wrap items-center gap-3 mb-8 pb-6 border-b border-dark/10">

        <!-- Weight buttons -->
        <div class="flex flex-wrap gap-2" data-weight-selector>
          <?php foreach ( $weight_labels as $weight => $label ) :
            if ( ! in_array( (string) $weight, $weights, true ) ) continue; ?>
            <button type="button"
                    class="font-monotracking-[0.1em] uppercase px-3 py-1.5 border border-dark/20 hover:border-dark transition-colors"
                    data-weight="<?php echo esc_attr( $weight ); ?>">
              <?php echo esc_html( $label ); ?>
            </button>
          <?php endforeach; ?>
        </div>

        <!-- Right controls -->
        <div class="flex items-center gap-2 ml-auto flex-wrap">
          <button type="button"
                  class="font-monotracking-[0.1em] uppercase px-3 py-1.5 border border-dark/20 hover:border-dark italic transition-colors"
                  data-italic-toggle>
            Italic
          </button>
          <button type="button"
                  class="font-monotracking-[0.1em] uppercase px-3 py-1.5 border border-dark/20 hover:border-dark transition-colors"
                  data-bg-toggle>
            Dark
          </button>
          <span class="font-monotext-dark/30">12</span>
          <input type="range" min="12" max="200" value="48" class="w-28 accent-primary" data-font-size-slider>
          <span class="font-monotext-dark/30">200</span>
          <span class="font-mono text-[11px] text-dark/45 w-10 text-right tabular-nums" data-font-size-label>48px</span>
        </div>

      </div>

      <!-- Preview area -->
      <div contenteditable="true"
           class="min-h-36 py-4 outline-none border-b border-dark/10 focus:border-dark transition-colors text-dark"
           data-font-preview
           data-font-family="<?php echo esc_attr( $font_family ); ?>"
           style="font-family:'<?php echo esc_attr( $font_family ); ?>';font-size:48px;line-height:1.15;"
           aria-label="Ketik teks untuk preview font"
           spellcheck="false"><?php echo esc_html( $sample_text ); ?></div>

      <button type="button"
              class="mt-4 font-monotracking-[0.14em] uppercase text-dark/35 hover:text-dark transition-colors"
              data-copy-css>
        Copy CSS
      </button>

    </div>
  </div>

  <!-- ── Tab: Download ── -->
  <div id="tab-download" class="px-6 lg:px-16 py-14 hidden" data-tab-panel="download">
    <div id="download" class="w-1/2">

      <!-- Lisensi -->
      <p class="font-monotracking-[0.22em] uppercase text-dark/30 mb-1">Lisensi</p>
      <p class="font-gontor text-dark mb-2"><?php echo esc_html( $license_abbr ); ?></p>
      <p class="font-mono text-dark/50 leading-relaxed mb-8">
        <?php if ( $license === 'ofl' ) : ?>
          SIL Open Font License 1.1 — bebas untuk penggunaan personal dan komersial,
          termasuk produk berbayar, dengan syarat nama font tidak diubah.
        <?php else : ?>
          <?php echo esc_html( $license_display ); ?> license.
        <?php endif; ?>
      </p>

      <div class="flex flex-wrap gap-3">
        <!-- Download ZIP -->
        <?php if ( $font_zip ) : ?>
        <a href="<?php echo esc_url( $font_zip ); ?>"
           download
           class="inline-flex items-center gap-2.5 bg-primary text-white px-6 py-3.5 rounded-full font-mono tracking-[0.1em] uppercase hover:bg-dark transition-colors duration-300">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
          Download All Variants (.zip)
        </a>
        <?php endif; ?>

        <!-- Type Specimen Book -->
        <?php if ( $specimen_url ) : ?>
        <a href="<?php echo esc_url( $specimen_url ); ?>"
           target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2.5 bg-white text-dark border border-dark/20 px-6 py-3.5 rounded-full font-mono text-[11px] tracking-[0.1em] uppercase hover:border-dark transition-colors duration-300">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
          Type Specimen Book (PDF)
        </a>
        <?php endif; ?>
      </div>

    </div>
  </div>

</main>

<?php get_footer(); ?>
