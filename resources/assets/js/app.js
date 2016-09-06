$('.dropdown-toggle').click(function () {
    $(this).siblings().closest('ul').toggle();
});