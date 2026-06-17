<?php get_header();

$filter_cat  = isset( $_GET['kategori'] ) ? sanitize_text_field( wp_unslash( $_GET['kategori'] ) ) : '';
$filter_lic  = isset( $_GET['lisensi'] )  ? sanitize_text_field( wp_unslash( $_GET['lisensi'] ) )  : '';
$filter_sort = isset( $_GET['urut'] )     ? sanitize_text_field( wp_unslash( $_GET['urut'] ) )      : 'date';
$search      = get_search_query();
$paged       = get_query_var( 'paged' ) ?: 1;

$query_args = [
    'post_type'      => 'font',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => $filter_sort === 'title' ? 'title' : 'date',
    'order'          => $filter_sort === 'title' ? 'ASC' : 'DESC',
];
if ( $search ) $query_args['s'] = $search;
// Filter kategori pakai taxonomy
if ( $filter_cat ) {
    $query_args['tax_query'] = [ [
        'taxonomy' => 'font_category',
        'field'    => 'slug',
        'terms'    => $filter_cat,
    ] ];
}
if ( $filter_cat ) {
    $query_args['tax_query'] = [ [
        'taxonomy' => 'font_category',
        'field'    => 'slug',
        'terms'    => $filter_cat,
    ] ];
}
if ( $filter_lic ) {
    $query_args['meta_query'] = [ [ 'key' => 'font_license', 'value' => $filter_lic ] ];
}

$fonts    = new WP_Query( $query_args );
$all_cats = get_terms( [ 'taxonomy' => 'font_category', 'hide_empty' => false ] );
?>

<main class="min-h-screen pt-20 lg:pt-24">

  <!-- ── Controls bar: sample text + size + sort (sticky) ── -->
  <div class="sticky top-14 z-30 bg-white border-b border-dark/10 px-6 lg:px-10 py-3">
    <div class="flex items-center gap-4 flex-wrap">

      <input type="text"
             value="Hampir sebelum kami menyadarinya, kami telah meninggalkan tanah."
             placeholder="Ketik teks preview..."
             class="flex-1 min-w-[200px] bg-transparent border-b border-dark/20 pb-1.5
                    font-mono text-[12px] text-dark placeholder-dark/25
                    focus:outline-none focus:border-dark transition-colors"
             data-sample-input>

      <div class="flex items-center gap-2 flex-none">
        <input type="range" min="20" max="120" value="40"
               class="w-24 accent-primary" data-sample-size>
        <span class="font-mono text-[11px] text-dark/40 w-10 tabular-nums"
              data-sample-size-label>40px</span>
      </div>

      <form method="get" class="flex-none">
        <?php if ( $filter_cat ) : ?><input type="hidden" name="kategori" value="<?php echo esc_attr( $filter_cat ); ?>"><?php endif; ?>
        <?php if ( $filter_lic ) : ?><input type="hidden" name="lisensi"  value="<?php echo esc_attr( $filter_lic ); ?>"><?php endif; ?>
        <select name="urut" onchange="this.form.submit()"
                class="bg-transparent font-mono text-[11px] text-dark/45 focus:outline-none cursor-pointer border-b border-dark/20 pb-1.5">
          <option value="date"  <?php selected( $filter_sort, 'date' ); ?>>Terbaru</option>
          <option value="title" <?php selected( $filter_sort, 'title' ); ?>>A–Z</option>
        </select>
      </form>

    </div>
  </div>

  <div class="flex">

    <!-- ── Sidebar filter (desktop) ── -->
    <aside class="hidden lg:flex flex-col flex-none w-48 xl:w-56 border-r border-dark/10 px-6 py-8 sticky top-[6.5rem] self-start max-h-[calc(100vh-6.5rem)] overflow-y-auto"
           aria-label="Filter font">

      <!-- Search -->
      <form method="get" class="mb-7" role="search">
        <?php if ( $filter_cat ) : ?><input type="hidden" name="kategori" value="<?php echo esc_attr( $filter_cat ); ?>"><?php endif; ?>
        <?php if ( $filter_lic ) : ?><input type="hidden" name="lisensi"  value="<?php echo esc_attr( $filter_lic ); ?>"><?php endif; ?>
        <input type="search" name="s"
               value="<?php echo esc_attr( $search ); ?>"
               placeholder="Cari font..."
               class="w-full bg-transparent border-b border-dark/20 pb-1.5 font-mono text-[11px]
                      text-dark placeholder-dark/25 focus:outline-none focus:border-dark transition-colors">
      </form>

      <p class="font-mono text-[10px] tracking-[0.22em] uppercase text-dark/30 mb-3">Kategori</p>
      <a href="<?php echo esc_url( remove_query_arg( 'kategori' ) ); ?>"
         class="block py-1.5 font-mono text-[11px] transition-colors <?php echo ! $filter_cat ? 'text-primary font-medium' : 'text-dark/40 hover:text-dark'; ?>">
        Semua
      </a>
      <?php if ( ! is_wp_error( $all_cats ) ) foreach ( $all_cats as $cat ) : ?>
        <a href="<?php echo esc_url( $cat->slug === $filter_cat ? remove_query_arg( 'kategori' ) : add_query_arg( 'kategori', $cat->slug ) ); ?>"
           class="block py-1.5 font-mono text-[11px] transition-colors <?php echo $cat->slug === $filter_cat ? 'text-primary font-medium' : 'text-dark/40 hover:text-dark'; ?>">
          <?php echo esc_html( $cat->name ); ?>
          <span class="text-dark/20">(<?php echo esc_html( $cat->count ); ?>)</span>
        </a>
      <?php endforeach; ?>

      <div class="border-t border-dark/10 mt-5 pt-5">
        <p class="font-mono text-[10px] tracking-[0.22em] uppercase text-dark/30 mb-3">Lisensi</p>
        <a href="<?php echo esc_url( remove_query_arg( 'lisensi' ) ); ?>"
           class="block py-1.5 font-mono text-[11px] transition-colors <?php echo ! $filter_lic ? 'text-primary font-medium' : 'text-dark/40 hover:text-dark'; ?>">
          Semua
        </a>
        <?php foreach ( [
            'ofl'        => 'OFL',
            'apache'     => 'Apache 2.0',
            'cc0'        => 'Public Domain',
            'freeware'   => 'Freeware',
            'commercial' => 'Commercial',
            'custom'     => 'Custom',
        ] as $slug => $label ) : ?>
          <a href="<?php echo esc_url( $slug === $filter_lic ? remove_query_arg( 'lisensi' ) : add_query_arg( 'lisensi', $slug ) ); ?>"
             class="block py-1.5 font-mono text-[11px] transition-colors <?php echo $slug === $filter_lic ? 'text-primary font-medium' : 'text-dark/40 hover:text-dark'; ?>">
            <?php echo esc_html( $label ); ?>
          </a>
        <?php endforeach; ?>
      </div>

    </aside>

    <!-- ── Daftar font ── -->
    <div class="flex-1 min-w-0 divide-y divide-dark/[0.08]">
      <?php if ( $fonts->have_posts() ) : ?>

        <?php while ( $fonts->have_posts() ) : $fonts->the_post();
          $weights_raw   = get_field( 'font_weights' );
          $styles_count  = is_array( $weights_raw ) ? count( $weights_raw ) * 2 : 16;
          get_template_part( 'template-parts/font-card', null, [
            'font_family'  => get_field( 'font_family' )      ?: get_the_title(),
            'license'      => get_field( 'font_license' )     ?: 'ofl',
            'font_status'  => get_field( 'font_status' )      ?: 'active',
            'foundry'      => get_field( 'font_foundry' )     ?: '',
            'sample_text'  => get_field( 'font_sample_text' ) ?: '',
            'styles_count' => $styles_count,
          ] );
        endwhile;
        wp_reset_postdata(); ?>

        <?php if ( $fonts->max_num_pages > 1 ) : ?>
          <nav class="px-6 lg:px-10 py-10 flex justify-center gap-2" aria-label="Pagination">
            <?php echo wp_kses_post( paginate_links( [
              'total'     => $fonts->max_num_pages,
              'current'   => $paged,
              'prev_text' => '&larr;',
              'next_text' => '&rarr;',
            ] ) ); ?>
          </nav>
        <?php endif; ?>

      <?php else : ?>
        <div class="px-6 lg:px-10 py-28 text-center">
          <p class="font-gontor text-3xl text-dark/20 mb-2">Belum ada font.</p>
          <p class="font-mono text-[11px] text-dark/25">Koleksi segera hadir.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php get_footer(); ?>
