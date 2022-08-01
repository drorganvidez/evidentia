@if(Cookie::get('dark_mode'))
    <link id="css_mode" rel="stylesheet" href="{{asset('css/theme-dark.bundle.css')}}" />
@else
    <link id="css_mode" rel="stylesheet" href="{{asset('css/theme.bundle.css')}}" />
@endif