@if(Cookie::get('dark_mode') == 1)
    <img id="logo_css" src="{{asset('img/logo_dark.svg')}}" class="img-fluid" width="150px" alt="Evidentia Logo">
@else
    <img id="logo_css" src="{{asset('img/logo_light.svg')}}" class="img-fluid" width="150px" alt="Evidentia Logo">
@endif