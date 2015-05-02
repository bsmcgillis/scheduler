$(document).ready(function(){

	// Move end datepicker enabled checkbox to 
	$('#id_rangeend_enabled').insertAfter($('#id_spandays'));
	$('#id_spandays').remove();

	// Remove the label that says 'Enabled' next to end date picker
	$("label[for=id_rangeend_enabled]").remove();
	
	// Change the label so that it is for the correct box 
	$('label[for=id_spandays]').attr('for', 'id_rangeend_enabled')

	// check for errors in span multiple days
	if($(".spandays .error").length == 0){
		// no errors, uncheck
		$("#id_rangeend_enabled").prop('checked', false);
	}
	else {
		// are errors, check 
		$("#id_rangeend_enabled").prop('checked', true);
	}

	// Update whether range is shown or not
	spandaysToggle();

	// In the single slot side, if there are errors or there is more than one appointment
	if($("#single_slot .error").length != 0 || $('#id_appointhead_1').length != 0){
		// Show the slot
		$('#single_slot').toggle();
		$('#repeated_slot').toggle();
	}

})

// Swaps which page is shown, repeated slot or single slot
function toggleForm () {
	$('#single_slot').slideToggle();
	$('#repeated_slot').slideToggle();
}

// Every time the enabled box is checked, toggle the view of the datepicker and days
$('#id_rangeend_enabled').click(function () {
	spandaysToggle();
});

// Toggle view of datepicker and days based on the status of the spandays checkbox
function spandaysToggle () {
	if($('#id_rangeend_enabled').prop('checked')) {
		$('.spandays').slideDown(); // show
	}
	else {
		$('.spandays').slideUp(); //hide
	}
}
