document.addEventListener("DOMContentLoaded", function(event) { 

});

// Return a flag (could be a string or a boolean) to the "onsubmit" part of form in renderer
function alert_user()
{
	var choice = false

    choice = confirm("WARNING: You are registering an appointment, but you currently already have one. This new appointment will delete your, or your group's, old appointment.");

    // Set value to 'choice' in input tag w/ id='choiceChanged'
    var element = document.getElementById("choiceChanged");
    element.value = choice;

    return choice;
}