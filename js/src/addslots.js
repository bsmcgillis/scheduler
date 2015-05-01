$(document).ready(function(){

	// If there are no errors
	if($(".error").length == 0){
		// Hide the selectors
		$("#id_spandays").prop('checked', false);
		$('.spandays').hide();
	}
	else {
		// Are errors, show the selectors
		$("#id_spandays").prop('checked', true);
		$('.spandays').show();
	}
})


function toggleForm () {
	$('#repeated_slot').slideToggle();
	$('#single_slot').slideToggle();
}

function toggleRepeat() {
	$('.spandays').toggle();
}