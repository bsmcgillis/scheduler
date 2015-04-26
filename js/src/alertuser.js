document.addEventListener("DOMContentLoaded", function(event) { 

});


function alert_user()
{
    choice = confirm("You are registering an appointment for your group. If any members of this group have a conflicting or additional appointment where multiple appointments are not allowed, these appointments will automatically be dropped.");
    alert("You chose " + choice);
    $.post('view.php', {accept: choice}); 
}