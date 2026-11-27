{{-- The site's fonts, loaded with the page rather than from inside app.css, so text doesn't redraw in a fallback
     first. Roboto swaps in over a fallback sized to match it (app.css); the logo font waits for itself rather than
     showing the logo in another font. Korean and Chinese pages add their Noto Sans in layouts/app. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ruslan+Display&display=block">
