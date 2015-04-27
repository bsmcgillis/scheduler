choice = false

// Return a flag (could be a string or a boolean) to the "onsubmit" part of form in renderer
// Should be returned to renderer. Might also have to add (or edit) to studentview:89

document.addEventListener("DOMContentLoaded", function(event) { 

});


function alert_user()
{
    choice = confirm("You are registering an appointment for your group. If any members of this group have a conflicting or additional appointment where multiple appointments are not allowed, these appointments will automatically be dropped.");
    alert("You chose " + choice);
    // $.post('view.php', {accept: choice}); 
    $('#choiceChanged').val(choice);
}