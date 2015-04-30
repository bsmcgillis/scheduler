$(document).ready(function(){
	toggleRepeat();
})




function toggleForm () {
	$('#repeated_slot').slideToggle();
	$('#single_slot').slideToggle();
}

function toggleRepeat() {
	$('.spandays').toggle();
}