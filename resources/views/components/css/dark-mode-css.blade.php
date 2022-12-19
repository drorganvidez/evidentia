@if(Cookie::get('dark_mode'))
    @vite(['resources/sass/app_dark_mode.scss', 'resources/js/app.js'])
@else
    @vite(['resources/sass/app_light_mode.scss', 'resources/js/app.js'])
@endif