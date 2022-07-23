function throw_alert(type, delay = 5000)
{
    $("#toast_"+type).show();
    $("#toast_"+type).delay(delay).fadeOut('slow');
}