//Back Button
function goBack(event) {
    event.preventDefault();
    window.history.back();
  }

//Download APK
function downloadFile() {
        const fileUrl = 'PZ_tp.png'; // Replace with the actual file URL
        const a = document.createElement('a');
        a.href = fileUrl;
        a.download = 'PlayZone Logo'; // Replace with the desired file name
        a.click();
    }

    //Nav Dropdown
$(document).ready(function(){
    $('.navbar-nav .dropdown').hover(function() {
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(200);
    }, function() {
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(200);
    });
});
