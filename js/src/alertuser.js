

// Return a flag (could be a string or a boolean) to the "onsubmit" part of form in renderer
// Should be returned to renderer. Might also have to add (or edit) to studentview:89

document.addEventListener("DOMContentLoaded", function(event) { 

});


function alert_user()
{
	var choice = false

    choice = confirm("You are registering an appointment for your group. If any members of this group have a conflicting or additional appointment where multiple appointments are not allowed, these appointments will automatically be dropped.");

    // Set value to 'choice' in input tag w/ id='choiceChanged'
    var element = document.getElementById("choiceChanged");
    element.value = choice;

    var sameelement = document.getElementById("choiceChanged"); // testing
    alert("You chose " + sameelement.value); // testing

    get_choice();
}

function get_choice()
{
	var choice = document.getElementById("choiceChanged").value;
	alert("The returned choice was " + choice);
}