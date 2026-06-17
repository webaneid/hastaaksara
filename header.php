<?php
/**
 * header.php — Overlay header, ikuti desain PDF
 *
 * Hierarki logo: ACF hasta_logo → WP Custom Logo → teks fallback
 * Header transparan di atas hero, opaque saat di-scroll
 */
$canonical_url = is_singular()     ? get_permalink()
               : ( is_front_page() ? home_url( '/' )
               : get_pagenum_link() );

$logo_url      = hasta_get_logo_url();
$logo_right    = hasta_get_logo_right_url();
$tagline_right = hasta_get_tagline_right();
$bg_header_url = get_template_directory_uri() . '/assets/images/bg-header.webp';
$is_front      = is_front_page() && ! is_paged();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="canonical" href="<?php echo esc_url( $canonical_url ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white antialiased font-gontor' ); ?>>
<?php wp_body_open(); ?>

<!-- ── Fixed header overlay ─────────────────────────────── -->
<header
  class="fixed top-0 left-0 right-0 z-50 px-6 lg:px-16 py-7
         flex items-center justify-between bg-primary text-white
         transition-shadow duration-300"
  style="background-image: url('<?php echo esc_url( $bg_header_url ); ?>'); background-repeat: repeat; background-size: auto;"
  data-header
  data-front="<?php echo $is_front ? '1' : '0'; ?>"
>

  <!-- Logo kiri -->
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
     class="flex-none leading-none"
     aria-label="Hasta Aksara — Beranda">
    <?php if ( $logo_url ) : ?>
      <img src="<?php echo esc_url( $logo_url ); ?>"
           alt="hasta aksara"
           class="h-20 w-auto object-contain">
    <?php else : ?>
      <div class="font-gontor font-medium text-[clamp(32px,4vw,48px)] leading-[1.0] tracking-[-0.01em]">
        hasta.<br>aksara
      </div>
    <?php endif; ?>
  </a>

  <!-- Kanan: logo atau tagline -->
  <?php if ( $logo_right ) : ?>
    <img src="<?php echo esc_url( $logo_right ); ?>"
         alt="Logo partner"
         class="h-20 w-auto object-contain opacity-90">
  <?php else : ?>
    <span class="font-mono text-[10px] tracking-[0.32em] uppercase opacity-50">
      <?php echo wp_kses_post( $tagline_right ); ?>
    </span>
  <?php endif; ?>

</header>
