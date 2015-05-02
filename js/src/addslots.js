$(document).ready(function(){

	// Move end datepicker enabled checkbox to 
	$('#id_rangeend_enabled').insertAfter($('#id_spandays'));
	$('#id_spandays').remove();

	// Remove the label that says 'Enabled' next to end date picker
	$("label[for=id_rangeend_enabled]").remove();
	
	// Change the label so that it is for the correct box 
	$('label[for=id_spandays]').attr('for', 'id_rangeend_enabled')

	// If there are no errors
	if($(".error").length == 0){
		// Hide the selectors and ensure checkbox is unchecked
		$("#id_rangeend_enabled").prop('checked', false);
		$('.spandays').hide();
	}
	else {
	
		// Are errors, check checkbox and show 
		$("#id_rangeend_enabled").prop('checked', true);
		$('.spandays').show();
	}
})

// Swaps which page is shown, repeated slot or single slot
function toggleForm () {
	$('#repeated_slot').slideToggle();
	$('#single_slot').slideToggle();
}

// Every time the enabled box is checked, toggle the view of the datepicker and days
$('#id_rangeend_enabled').click(function () {
	$('.spandays').toggle();
});

// Toggle view of datepicker and days
function spandaysToggle () {
	$('.spandays').toggle();
}
