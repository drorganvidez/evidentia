function throw_alert(type, delay = 5000)
{
    $("#toast_"+type).show();
    $("#toast_"+type).delay(delay).fadeOut('slow');
}

function close_modal_autosaved()
{
    $('#modal_evidence_temp').hide();
}

function dark_mode_toggle()
{

    let manifest_url = location.origin + '/build/manifest.json'

    $.getJSON(manifest_url, function(manifest) {

        // Icon
        let icon_css_mode = $("#icon_css_mode");

        // Get CSS filenames
        let file_css_light_mode = location.origin + "/build/" + manifest['resources/sass/app_light_mode.scss']["file"];
        let file_css_dark_mode = location.origin  + "/build/" + manifest['resources/sass/app_dark_mode.scss']["file"];

        // Change CSS style
        let link_preload = $('link[rel=preload]')
        let link_stylesheet = $('link[rel=stylesheet]')

        if (link_preload.attr('href') === file_css_light_mode){
            link_preload.attr('href', file_css_dark_mode)
            link_stylesheet.attr('href', file_css_dark_mode)
            icon_css_mode.attr('class', 'fe fe-sun');
        } else {
            link_preload.attr('href', file_css_light_mode)
            link_stylesheet.attr('href', file_css_light_mode)
            icon_css_mode.attr('class', 'fe fe-moon');
        }

        // Change logo
        let logo_src = $("#logo_css").attr('src');
        if(logo_src.includes("logo_dark.svg")){
            logo_src = logo_src.replace('logo_dark.svg', 'logo_light.svg');
        }else{
            logo_src = logo_src.replace('logo_light.svg', 'logo_dark.svg');
        }
        $("#logo_css").attr('src', logo_src);
        
    });

}
