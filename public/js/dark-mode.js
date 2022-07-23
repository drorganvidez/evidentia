function dark_mode_toggle(){

    let href = $("#css_mode").attr('href');
    var icon_css_mode = $("#icon_css_mode");

    // Change CSS style
    if(href.includes("theme.bundle.css")){
        // si el CSS está en modo light
        icon_css_mode.attr("class", "fe fe-sun");
        href = href.replace('theme.bundle.css', 'theme-dark.bundle.css');

    }else{
        // si el CSS está en modo dark
        icon_css_mode.attr("class", "fe fe-moon");
        href = href.replace('theme-dark.bundle.css', 'theme.bundle.css');

    }
    $("#css_mode").attr('href', href);

    // Change logo
    let logo_src = $("#logo_css").attr('src');
    if(logo_src.includes("logo_dark.svg")){
        // si el logo está en modo light
        logo_src = logo_src.replace('logo_dark.svg', 'logo_light.svg');
    }else{
        // si el logo está en modo dark
        logo_src = logo_src.replace('logo_light.svg', 'logo_dark.svg');
    }
    $("#logo_css").attr('src', logo_src);

    /*
    setTimeout(function(){
        var icon_css_mode = $("#icon_css_mode");

        if(icon_css_mode.attr("class") === "fe fe-sun"){
            icon_css_mode.attr("class", "fe fe-moon");
        }else{
            icon_css_mode.attr("class", "fe fe-sun");
        }

    },2000);

    //$("#css_mode").attr('href','theme.bundle.css');

    /*

    if (dark_mode == 1){
        $('link[href="theme-dark.bundle.css"]').attr('href','theme.bundle.css');
    }else{
        $('link[href="theme.bundle.css"]').attr('href','theme-dark.bundle.css');
    }


     */


}