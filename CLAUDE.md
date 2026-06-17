# Hasta Aksara — CLAUDE.md
> Otak dan memori project. Baca ini sebelum coding apapun.

## Identitas Project
- **Nama**: Hasta Aksara (hastaaksara)
- **Tagline**: Platform font dari ekosistem IKPM Gontor
- **Owner**: Webane Indonesia
- **Jenis**: WordPress Theme (font marketplace)
- **Instagram**: @hastaaksara

## Visi
Platform distribusi dan preview font berkualitas tinggi,
dimulai dari Gontor Font (karya IKPM Gontor).
Bukan sekadar website font — ini prasasti digital
peradaban tipografi Islam modern Nusantara.

## Design Language (dari docs/HastaAksara_website.pdf)
Wajib dibaca sebelum bikin UI apapun.

### Prinsip Layout (PENTING)
- **Header & Footer** : ikuti desain dari PDF — fixed navbar logo kotak merah, footer minimalis
- **Body / Container**: inspirasi Google Fonts — grid preview font dengan sample text panjang,
  setiap card menampilkan font dalam ukuran besar dengan kalimat contoh,
  klik card → halaman detail deskriptif font (bukan hanya specimen)
- Prototype acuan: google.com/fonts (struktur browse & detail-nya)

### Warna
- Primary Red  : #F22332 (merah Gontor)
- White        : #FFFFFF
- Dark         : #1A1A1A (teks utama)
- Purple accent: #7B6FA0 (tombol navigasi)
- Background   : #FFFFFF (halaman konten)

### Typography
- Display font : Gontor Font (serif, dipakai untuk judul besar)
- UI font      : System sans-serif atau Inter (untuk body/UI)
- Logo style   : "hasta. aksara" — lowercase, serif, kotak merah

### Estetika
- Minimalis, editorial, typographic-focused
- Banyak whitespace
- Tidak ada gambar dekoratif — font IS the hero
- Icon: SVG inline, minimalis, stroke style
- Tidak ada shadow tebal, tidak ada gradient kompleks

## Tech Stack
- Platform  : WordPress (PHP, shared hosting compatible)
- CSS       : Tailwind CSS v3
- JS        : Vanilla JS (tidak ada framework berat)
- Icon      : SVG inline manual
- Build     : Node.js untuk compile Tailwind saja
- ACF Pro   : INSTALLED — gunakan untuk semua meta fields CPT font
- No        : WooCommerce, jQuery (hindari jika bisa)

## Struktur Folder
hastaaksara/           ← root WordPress theme

├── CLAUDE.md          ← kamu sedang baca ini

├── docs/              ← referensi desain

│   └── HastaAksara_website.pdf

├── fonts/

│   └── gontor/

│       ├── otf/       ← master files

│       └── woff2/     ← web-ready (hasil konversi)

├── assets/

│   ├── css/

│   │   ├── input.css  ← Tailwind source

│   │   └── style.css  ← compiled output

│   ├── js/

│   │   ├── font-preview.js   ← fitur ketik & preview

│   │   └── main.js

│   └── fonts/         ← symlink atau copy dari /fonts

├── template-parts/

│   ├── font-card.php

│   ├── font-preview.php

│   └── header.php

├── style.css          ← WordPress theme header (wajib)

├── functions.php

├── index.php

├── single-font.php    ← halaman detail font

├── archive-font.php   ← direktori/browse font

└── page-preview.php   ← halaman preview interaktif

## Custom Post Type: `font`
Semua data font dikelola via WordPress CPT.
**Meta dikelola via ACF Pro** (bukan manual save_post).

### Fields (ACF field names = meta key):
- `font_family`     : nama CSS font-family (text)
- `font_slug`       : identifier unik — gontor-serif (text)
- `font_category`   : serif | sans-serif | display | arabic (select)
- `font_license`    : ofl | commercial | freeware (select)
- `font_version`    : versi font — 1.0, 1.1, dst (text)
- `font_designer`   : nama desainer (text)
- `font_foundry`    : nama foundry — Forcreator IKPM Gontor (text)
- `font_weights`    : weights tersedia (checkbox: 100,200,300,400,500,600,700,800)
- `font_files`      : path file woff2 per weight (repeater/group)
- `font_specimen`   : path PDF specimen book (file)
- `font_upm`        : Units per Em — 1926 (number)
- `is_variable`     : apakah variable font (true/false)
- `price`           : 0 = gratis, >0 = berbayar (number, roadmap)
- `font_status`     : active | draft | coming_soon (select)
- `font_sample_text`: kalimat preview default di card/browse (textarea)

## Fitur — Prioritas

### ✅ FASE 1 (Sekarang):
**A. Font Preview Interaktif**
- Input text → render realtime dengan font
- Slider ukuran: 12px — 200px
- Toggle weight: Thin/Light/Regular/Medium/SemiBold/Bold/ExtraBold
- Toggle style: Normal/Italic (jika tersedia)
- Background toggle: putih/hitam (untuk test kontras)
- Copy CSS snippet

**D. Browse/Direktori Font**
- Grid card font
- Filter: kategori, lisensi, bahasa
- Search font name
- Sort: terbaru, populer, A-Z

### 🔜 FASE 2 (Roadmap):
**B. Upload Font (Admin only)**
- Upload via WP Admin custom UI
- Support: .otf → auto-convert atau manual upload .woff2
- Set metadata lengkap
- Preview sebelum publish

**C. Beli Font (Pending)**
- Integrasi payment (TBD)
- License management
- Download setelah bayar

## Gontor Font — Data Lengkap
- **Nama resmi**: Gontor Font
- **Klasifikasi**: Transitional Serif
- **Foundry**: Forcreator, IKPM Gontor
- **Lisensi**: SIL Open Font License 1.1 (GRATIS)
- **UPM**: 1926 (tahun berdirinya Gontor)
- **Weights**: 100 Thin, 200 ExtraLight, 300 Light,
              400 Regular, 500 Medium, 600 SemiBold,
              700 Bold, 800 ExtraBold
- **Total styles**: 16 (8 upright + 8 italic)
- **File master**: fonts/gontor/otf/
- **File web**: fonts/gontor/woff2/

## Konversi Font OTF → WOFF2
Jalankan sekali sebelum development:
```bash
# Install fonttools
pip install fonttools brotli

# Konversi per file
for f in fonts/gontor/otf/*.otf; do
  python3 -m fonttools ttLib.woff2 compress "$f" \
    -o "fonts/gontor/woff2/$(basename ${f%.otf}.woff2)"
done
```

## ACF Pro — Cara Daftarkan Fields
Gunakan `acf_add_local_field_group()` di functions.php (code-based, tidak via UI admin).
Ini portable — tidak perlu export/import JSON, langsung aktif saat theme aktif.
Field group key: `group_hasta_font_meta`.

## SOP Development (WAJIB DIIKUTI)

### CSS / Styling
- SELALU pakai Tailwind utility classes
- Custom CSS hanya untuk @font-face dan animasi kompleks
- Tidak ada inline style kecuali untuk font-size slider (JS)
- Warna pakai CSS variable yang didefinisikan di input.css:
```css
  :root {
    --color-primary: #E8291C;
    --color-dark: #1A1A1A;
    --color-purple: #7B6FA0;
  }
```

### JavaScript
- Vanilla JS only — tidak ada jQuery, tidak ada framework
- Satu file per fitur: font-preview.js, font-filter.js, dll
- Gunakan data attributes untuk binding:
  `data-font-preview`, `data-weight-selector`, dll

### PHP / WordPress
- Tidak ada plugin dependency untuk core features
- Custom Post Type didaftarkan di functions.php
- Template hierarchy WordPress standard
- Escape semua output: `esc_html()`, `esc_attr()`, `esc_url()`
- Prefix semua function: `hasta_*`

### File Naming
- PHP: `kebab-case.php`
- JS: `kebab-case.js`
- CSS: `kebab-case.css`
- Semua lowercase

## Status Project
- [x] Setup theme WordPress (style.css, functions.php)
- [x] Konversi OTF → WOFF2 (16 file di fonts/gontor/woff2/)
- [x] Daftarkan @font-face Gontor Font (assets/css/input.css)
- [x] Custom Post Type `font`
- [x] Tailwind setup + compile (assets/css/style.css, 16.5 KB)
- [x] Templates: header, footer, index, archive-font, single-font, font-card
- [x] JS: main.js (hamburger + tabs), font-preview.js (weight + size + copy CSS)
- [ ] ACF Pro field group untuk CPT font (code-based di functions.php)
- [ ] Archive redesign — Google Fonts style (sample text panjang per card)
- [ ] Single-font redesign — halaman deskriptif (bukan hanya specimen)
- [ ] Italic toggle + background toggle di font-preview.js
- [ ] page-preview.php (halaman preview standalone)
- [ ] Specimen PDF viewer/download
- [ ] Data Gontor Font pertama di WP Admin

## Lessons Learned
(diisi saat development berlangsung)