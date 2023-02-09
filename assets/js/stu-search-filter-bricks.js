function stuSearchFilterBricks() {
    jQuery(document).on('sf:ajaxfinish', '.searchandfilter', function(){
        bricksLazyLoad();
    });
}

document.addEventListener("DOMContentLoaded", function (e) {
	stuSearchFilterBricks();
});
