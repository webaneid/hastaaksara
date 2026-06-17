// font-browse.js — archive: sample text input + size slider → update semua card preview
(function () {
  var input     = document.querySelector('[data-sample-input]');
  var sizeSlider = document.querySelector('[data-sample-size]');
  var sizeLabel = document.querySelector('[data-sample-size-label]');
  var cards     = document.querySelectorAll('[data-card-preview]');

  if ( !cards.length ) return;

  function updatePreviews() {
    var text = input ? ( input.value || input.placeholder ) : null;
    var size = sizeSlider ? sizeSlider.value + 'px' : null;
    cards.forEach( function (el) {
      if ( text !== null ) el.textContent = text;
      if ( size )          el.style.fontSize = size;
    } );
  }

  if ( input )      input.addEventListener( 'input', updatePreviews );
  if ( sizeSlider ) {
    sizeSlider.addEventListener( 'input', function () {
      if ( sizeLabel ) sizeLabel.textContent = this.value + 'px';
      updatePreviews();
    } );
  }
})();
