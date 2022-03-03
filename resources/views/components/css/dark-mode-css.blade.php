@if(Cookie::get('dark_mode') == 1)
    <link id="css_mode" rel="stylesheet" href="http://localhost/css/theme-dark.bundle.css" />
@else
    <link id="css_mode" rel="stylesheet" href="http://localhost/css/theme.bundle.css" />
@endif