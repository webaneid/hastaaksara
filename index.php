<?php get_header();

// Ambil font pertama yang aktif (prioritas: font_slug = gontor-serif)
$gontor_query = new WP_Query( [
    'post_type'      => 'font',
    'posts_per_page' => 1,
    'no_found_rows'  => true,
    'name'           => 'gontor-serif', // pakai WordPress post slug, bukan custom meta
] );

// Fallback: font apapun yang pertama
if ( ! $gontor_query->have_posts() ) {
    $gontor_query = new WP_Query( [
        'post_type'      => 'font',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ] );
}

$has_font    = $gontor_query->have_posts();
$font_url    = '#';
$font_title  = 'Gontor Font';
$font_cat    = '';
$font_upm    = '1926';

if ( $has_font ) {
    $gontor_query->the_post();
    $font_url      = get_permalink();
    $font_title    = get_the_title();
    $font_cat      = get_field( 'font_classification' ) ?: $font_cat;
    $font_upm      = get_field( 'font_upm' )            ?: $font_upm;
    $font_content  = get_the_content();
    $tax_terms     = get_the_terms( get_the_ID(), 'font_category' );
    $font_taxonomy = ( $tax_terms && ! is_wp_error( $tax_terms ) ) ? $tax_terms[0]->name : '';
    $font_license_image = get_field( 'font_license_image' ) ?: '';
    $font_license_image_url = is_array( $font_license_image ) ? $font_license_image['url'] : $font_license_image;
    $font_license_text  = get_field( 'font_license_text' ) ?: '';
    $font_foundry       = get_field( 'font_foundry' )      ?: '';
    wp_reset_postdata();
}
?>

<!-- ═══ HERO — Putih, full viewport ═══ -->
<section class="relative min-h-screen flex flex-col bg-white overflow-hidden"
         aria-label="<?php echo esc_attr( $font_title ); ?> Hero">

  <!-- Ornament background -->
  <div class="absolute inset-0 pointer-events-none select-none" aria-hidden="true">
    <img src="<?php echo esc_url( hasta_get_ornament_url() ); ?>"
         alt=""
         class="absolute right-[-10%] top-[-5%] w-[70vmin] lg:w-[55vmin] opacity-[0.06]">
  </div>

  <!-- Konten tengah -->
  <div class="flex flex-col px-6 lg:px-16 pt-48 pb-[80px] max-w-5xl w-full">
    <?php if ( $font_taxonomy ) : ?>
    <p class="font-mono text-[10px] tracking-[0.3em] uppercase text-dark/35 mb-8">
      <?php echo esc_html( $font_taxonomy ); ?>
    </p>
    <?php endif; ?>
    <h1 class="font-gontor font-semibold text-dark leading-[0.88] tracking-[-0.02em] text-[clamp(56px,9.5vw,120px)]">
      <?php echo nl2br( esc_html( $font_title ) ); ?>
    </h1>
    <?php if ( $font_cat ) : ?>
    <p class="mt-5 font-gontor italic text-dark/40 text-[clamp(16px,2vw,22px)] tracking-wide">
      <?php echo esc_html( $font_cat ); ?>
    </p>
    <?php endif; ?>
    <div class="flex flex-wrap gap-4 mt-10 lg:mt-14">
      <a href="<?php echo esc_url( $font_url ); ?>"
         class="inline-flex items-center gap-2.5 bg-primary text-white px-6 py-3.5 rounded-full font-mono text-[11px] tracking-[0.1em] uppercase hover:bg-dark transition-colors duration-300">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Download <?php echo esc_html( $font_title ); ?>
      </a>
      <a href="<?php echo esc_url( $font_url ); ?>#specimen"
         class="inline-flex items-center gap-2.5 bg-white text-dark border border-dark/20 px-6 py-3.5 rounded-full font-mono text-[11px] tracking-[0.1em] uppercase hover:border-dark transition-colors duration-300">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
        Type Specimen Book
      </a>
    </div>

  </div>

  <!-- ── Info lisensi + UPM + Foundry — full width ── -->
  <div id="section-font-footer" class="w-full px-6 lg:px-16 pt-[80px] pb-8 border-t border-dark/[0.08]">

    <!-- Baris 1: Badge lisensi — penuh -->
    <?php if ( $font_license_image_url ) : ?>
      <img src="<?php echo esc_url( $font_license_image_url ); ?>"
           alt="License Badge"
           class="h-10 w-auto object-contain mb-8">
    <?php endif; ?>

    <!-- Baris 2: 2 kolom sejajar -->
    <div class="flex items-start justify-between gap-12">

      <!-- Kiri: Teks Lisensi -->
      <?php if ( $font_license_text ) : ?>
      <div class="flex-1">
        <p class="font-mono text-[16px] leading-[1.9] text-dark/50">
          <?php echo nl2br( esc_html( $font_license_text ) ); ?>
        </p>
      </div>
      <?php endif; ?>

      <!-- Kanan: UPM + Foundry -->
      <?php if ( $font_upm ) : ?>
      <div class="flex-none text-right">
        <p class="font-mono text-[16px] font-bold tracking-[0.15em] uppercase text-dark mb-1">Units per Em (UPM)</p>
        <p class="font-mono text-[16px] text-dark/40"><?php echo esc_html( $font_upm ); ?></p>
        <?php if ( $font_foundry ) : ?>
          <p class="font-mono text-[16px] tracking-[0.2em] uppercase text-dark/30 mt-4">
            <?php echo esc_html( $font_foundry ); ?>
          </p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>

  </div>

  <!-- Scroll arrow -->
  <div class="absolute right-6 lg:right-16 bottom-8">
    <a href="#section-font-footer"
       class="w-10 h-10 rounded-full border border-dark/20 flex items-center justify-center text-dark/40 hover:border-dark hover:text-dark transition-all"
       aria-label="Scroll ke info font">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </a>
  </div>

</section>

<?php get_footer(); ?>
