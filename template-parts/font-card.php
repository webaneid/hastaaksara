<?php
/**
 * font-card.php — Google Fonts style card
 */
$font_family    = $args['font_family']  ?? get_the_title();
$font_status    = $args['font_status']  ?? 'active';
$sample_text    = $args['sample_text']  ?? '';
$styles_count   = $args['styles_count'] ?? 16;

$classification = get_post_meta( get_the_ID(), 'font_classification', true );
$specimen_url   = get_post_meta( get_the_ID(), 'font_specimen', true );
$default_sample = 'Hampir sebelum kami menyadarinya, kami telah meninggalkan tanah.';
if ( ! $sample_text ) $sample_text = $default_sample;
?>
<article class="group px-6 lg:px-10 py-8 hover:bg-neutral-50 transition-colors cursor-pointer"
         onclick="window.location='<?php the_permalink(); ?>'">

  <!-- Nama font + klasifikasi -->
  <div class="mb-6">
    <h2 class="font-gontor text-[15px] font-medium text-dark group-hover:text-primary transition-colors leading-tight">
      <a href="<?php the_permalink(); ?>" onclick="event.stopPropagation()">
        <?php the_title(); ?>
      </a>
    </h2>
    <?php if ( $classification ) : ?>
      <p class="font-mono text-[10px] tracking-[0.12em] uppercase text-dark/30 mt-1">
        <?php echo esc_html( $classification ); ?>
      </p>
    <?php endif; ?>
  </div>

  <!-- Preview teks dalam font tsb -->
  <div class="text-dark leading-snug overflow-hidden mb-7"
       data-card-preview
       style="font-family:'<?php echo esc_attr( $font_family ); ?>';font-size:40px;line-height:1.25;">
    <?php echo esc_html( $sample_text ); ?>
  </div>

  <!-- Tombol aksi -->
  <?php if ( $font_status !== 'coming_soon' ) : ?>
    <div class="flex flex-wrap items-center gap-3" onclick="event.stopPropagation()">

      <a href="<?php echo esc_url( get_permalink() ); ?>#download"
         class="inline-flex items-center gap-2 border border-dark/20 text-dark/60 hover:border-dark hover:text-dark
                font-mono text-[10px] tracking-[0.12em] uppercase px-4 py-2 transition-colors">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        Download
      </a>

      <?php if ( $specimen_url ) : ?>
        <a href="<?php echo esc_url( $specimen_url ); ?>"
           target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2 border border-dark/20 text-dark/60 hover:border-dark hover:text-dark
                  font-mono text-[10px] tracking-[0.12em] uppercase px-4 py-2 transition-colors">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
          </svg>
          Type Specimen
        </a>
      <?php endif; ?>

    </div>
  <?php else : ?>
    <span class="font-mono text-[10px] tracking-[0.14em] uppercase text-dark/25">Segera Hadir</span>
  <?php endif; ?>

</article>
