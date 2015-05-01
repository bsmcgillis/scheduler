$(document).ready(function(){
	$("#id_spandays").attr('value', 0)
	toggleRepeat();
})




function toggleForm () {
	$('#repeated_slot').slideToggle();
	$('#single_slot').slideToggle();
}

function toggleRepeat() {
	$('.spandays').toggle();
}