@if(Cookie::get('dark_mode') == 1)
    <img src="{{asset('img/logo_dark.svg')}}" class="navbar-brand-img mx-auto" alt="Evidentia Logo">
@else
    <img src="{{asset('img/logo_light.svg')}}" class="navbar-brand-img mx-auto" alt="Evidentia Logo">
@endif