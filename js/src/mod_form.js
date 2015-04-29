/*
* Hide the grade options when the page first loads. Have grab the parent element and hide children
* because the labels don't have IDs. And elements are not grouped together very well.
*/
$(document).ready(function() {
    
    //Grab the parent element
    var parentElement = $("#id_modgrade_scale").parent();
    
    //Remove line break elements to maintain formatting
    parentElement.children(':nth-child(3)').hide();
    parentElement.children(':nth-child(6)').hide();
    parentElement.children(':nth-child(9)').hide();
    
    //Remove the Scale lable and dropdown
    parentElement.children(':nth-child(4)').hide();
    parentElement.children(':nth-child(5)').hide();
    
    //Remove the maximum points label and dropdown    
    parentElement.children(':nth-child(7)').hide();
    parentElement.children(':nth-child(8)').hide();        
});

$('#id_modgrade_type').on('change', function() {
    
    //Get the selected value
    var selection = this.value;
    
    //Get the parent element
    var parentElement = $("#id_modgrade_scale").parent();
    
    if (selection == "scale"){
        //Make sure point elements stay hidden
        parentElement.children(':nth-child(6)').hide();
        parentElement.children(':nth-child(7)').hide();
        parentElement.children(':nth-child(8)').hide();
        
        //Show scale elements
        parentElement.children(':nth-child(3)').show();
        parentElement.children(':nth-child(4)').show();
        parentElement.children(':nth-child(5)').show();  
    }
    else if (selection == "point") {
        //Make sure scale elements stay hidden
        parentElement.children(':nth-child(3)').hide();
        parentElement.children(':nth-child(4)').hide();
        parentElement.children(':nth-child(5)').hide(); 
        
        //Show point elements
        parentElement.children(':nth-child(6)').show();
        parentElement.children(':nth-child(7)').show();
        parentElement.children(':nth-child(8)').show();
    }  
    else if (selection == "none") {
        //If it's none, hide everyone again
        parentElement.children(':nth-child(3)').hide();
        parentElement.children(':nth-child(6)').hide();
        parentElement.children(':nth-child(9)').hide();

        parentElement.children(':nth-child(4)').hide();
        parentElement.children(':nth-child(5)').hide();
    
        parentElement.children(':nth-child(7)').hide();
        parentElement.children(':nth-child(8)').hide(); 
    }
});
