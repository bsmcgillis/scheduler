/*
 * Created May 4, 2015 by Too Many Cooks
 *      Braden Caywood
 *      Derek Johnson
 *      Blake McGillis
 *      Greg Smith
 *      Andrew Van Tassell
 */
document.addEventListener("DOMContentLoaded", function(event) { 

});

// Return a flag (could be a string or a boolean) to the "onsubmit" part of form in renderer
function alert_user()
{
	var choice = false

    choice = confirm("WARNING: You are registering an appointment, but you currently already have one. This new appointment will delete your, or your group's, old appointment.");

    return choice;
}