// font-preview.js — single-font.php tab Preview
(function () {
  var preview     = document.querySelector('[data-font-preview]');
  var sizeSlider  = document.querySelector('[data-font-size-slider]');
  var sizeLabel   = document.querySelector('[data-font-size-label]');
  var weightBtns  = document.querySelectorAll('[data-weight-selector] [data-weight]');
  var italicBtn   = document.querySelector('[data-italic-toggle]');
  var bgBtn       = document.querySelector('[data-bg-toggle]');
  var copyCssBtn  = document.querySelector('[data-copy-css]');

  if ( !preview ) return;

  var fontFamily   = preview.dataset.fontFamily || 'Gontor';
  var currentWeight = '400';
  var currentStyle  = 'normal';
  var darkBg        = false;

  // ── Weight selector ─────────────────────────────────────
  function activateWeight( btn ) {
    weightBtns.forEach( function (b) {
      b.classList.remove( 'bg-dark', 'text-white', 'border-dark' );
      b.classList.add( 'border-dark/20' );
    } );
    btn.classList.add( 'bg-dark', 'text-white', 'border-dark' );
    btn.classList.remove( 'border-dark/20' );
    currentWeight = btn.dataset.weight;
    preview.style.fontWeight = currentWeight;
  }

  weightBtns.forEach( function (btn) {
    btn.addEventListener( 'click', function () { activateWeight( btn ); } );
  } );

  var defaultBtn = document.querySelector('[data-weight="400"]') || weightBtns[0];
  if ( defaultBtn ) activateWeight( defaultBtn );

  // ── Italic toggle ────────────────────────────────────────
  if ( italicBtn ) {
    italicBtn.addEventListener( 'click', function () {
      currentStyle = currentStyle === 'normal' ? 'italic' : 'normal';
      preview.style.fontStyle = currentStyle;
      italicBtn.classList.toggle( 'bg-dark',   currentStyle === 'italic' );
      italicBtn.classList.toggle( 'text-white', currentStyle === 'italic' );
      italicBtn.classList.toggle( 'border-dark', currentStyle === 'italic' );
    } );
  }

  // ── Background toggle (putih / hitam) ────────────────────
  if ( bgBtn ) {
    bgBtn.addEventListener( 'click', function () {
      darkBg = !darkBg;
      preview.style.background = darkBg ? '#1A1A1A' : '';
      preview.style.color      = darkBg ? '#FFFFFF' : '';
      preview.style.padding    = darkBg ? '24px' : '';
      bgBtn.textContent        = darkBg ? 'Light' : 'Dark';
    } );
  }

  // ── Size slider ──────────────────────────────────────────
  if ( sizeSlider ) {
    sizeSlider.addEventListener( 'input', function () {
      preview.style.fontSize = this.value + 'px';
      if ( sizeLabel ) sizeLabel.textContent = this.value + 'px';
    } );
  }

  // ── Copy CSS ─────────────────────────────────────────────
  if ( copyCssBtn ) {
    copyCssBtn.addEventListener( 'click', function () {
      var size = sizeSlider ? sizeSlider.value + 'px' : '48px';
      var css  = "font-family: '" + fontFamily + "', Georgia, serif;\n"
               + "font-weight: " + currentWeight + ";\n"
               + "font-style: "  + currentStyle  + ";\n"
               + "font-size: "   + size + ";";

      if ( navigator.clipboard ) {
        navigator.clipboard.writeText( css ).then( function () {
          var orig = copyCssBtn.textContent;
          copyCssBtn.textContent = 'Copied!';
          setTimeout( function () { copyCssBtn.textContent = orig; }, 1500 );
        } );
      }
    } );
  }
})();
