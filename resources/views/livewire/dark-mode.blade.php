<div>
    <div class="me-4 d-none d-md-flex">
        <a wire:click="toggle_css_mode" onclick="dark_mode_toggle()" class="navbar-user-link">
            <span wire:ignore  class="icon">

                @if($is_dark_mode == "true")
                    <i id="icon_css_mode" class="fe fe-sun"></i>
                @else
                    <i id="icon_css_mode" class="fe fe-moon"></i>
                @endif


            </span>
        </a>
    </div>

</div>