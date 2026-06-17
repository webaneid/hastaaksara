<?php
/**
 * footer.php — Site-wide footer (minimalis, satu baris)
 */
$instagram_url     = get_option( 'hasta_instagram' ) ?: 'https://instagram.com/hastaaksara';
$footer_copyright  = get_option( 'hasta_footer_copyright' );
$copyright_text    = $footer_copyright ?: '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' );
?>

<footer class="border-t border-dark/10 py-6 px-6 lg:px-16 flex items-center justify-between gap-4 text-[16px]">
  <p class="font-mono tracking-[0.18em] text-dark/35">
    <?php echo esc_html( $copyright_text ); ?>
  </p>
  <a href="<?php echo esc_url( $instagram_url ); ?>"
     target="_blank"
     rel="noopener noreferrer"
     class="flex items-center gap-2 group"
     aria-label="Instagram">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
         class="text-primary flex-none" aria-hidden="true">
      <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
      <circle cx="12" cy="12" r="4"/>
      <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none"/>
    </svg>
    <span class="font-mono tracking-[0.18em] text-dark/35 group-hover:text-dark transition-colors">
      <?php
        // Ambil username dari URL: https://instagram.com/hastaaksara → @hastaaksara
        $handle = rtrim( parse_url( $instagram_url, PHP_URL_PATH ), '/' );
        echo esc_html( '@' . ltrim( $handle, '/' ) );
      ?>
    </span>
  </a>
</footer>

<?php wp_footer(); ?>
</body>
</html>
