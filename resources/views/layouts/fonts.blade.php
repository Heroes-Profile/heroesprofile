{{-- The site's fonts, loaded with the page rather than from inside app.css. Their Latin files are preloaded, so they're
     usually ready by the first paint and text doesn't reflow when they arrive; until then Roboto has a fallback sized to
     match it (app.css). The logo font waits for itself, in boxes already its width (nav), rather than showing the logo in
     another font. Google's file addresses are versioned: if Google moves to new ones, these preloads just go unused.
     Korean and Chinese pages add their Noto Sans in layouts/app. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="https://fonts.gstatic.com/s/roboto/v51/KFO7CnqEu92Fr1ME7kSn66aGLdTylUAMa3yUBA.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="https://fonts.gstatic.com/s/ruslandisplay/v27/Gw6jwczl81XcIZuckK_e3Upfdwxsn90.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ruslan+Display&display=block">
