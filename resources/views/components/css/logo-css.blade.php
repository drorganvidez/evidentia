@if(Cookie::get('dark_mode') == 1)
    <img id="logo_css" src="{{asset('img/logo_dark.svg')}}" class="navbar-brand-img mx-auto" alt="Evidentia Logo">
@else
    <img id="logo_css" src="{{asset('img/logo_light.svg')}}" class="navbar-brand-img mx-auto" alt="Evidentia Logo">
@endif