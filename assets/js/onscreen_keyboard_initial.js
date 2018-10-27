$('#register_container').on('focus', '#item, #customer, #supplier, #location, #amount_tendered, #comment, .keyboard', function(e) {

		$input = $(this);
	
		if ($input.attr('id') == 'item' && !submitting)
		{
				if($("#category_item_selection_wrapper").is(":visible"))
				{
					$("a.none.hide-grid").trigger('click');
				}
				$("#register_container").animate({ marginTop: '275px'}, 500, function(){
 					$("#item").popover('show');
 				});
		
		} else {
			$("#category_item_selection_wrapper, #register_container").promise().done(function(){
				
				$input.popover('show');
			
				if ($input.attr('id') == 'amount_tendered')
				{
					$("#amount_tendered").select();
				}
			});
		}
});

var dragging = false;

$('#register_container').on('touchend', function (event) {
    if (!$(event.target).closest('#item, #customer, #supplier, #location, #amount_tendered, #comment, .keyboard, .jqbtk-container, .item-suggestions, .suggestions').length) {
				if(!dragging)
				{
					$('#item, #customer, #supplier, #location, #amount_tendered, #comment, .keyboard').blur();
				}
    }
});

$(document).on("touchmove", function(){
      dragging = true;
});

$(document).on("touchend", function(){
      dragging = false;
});

$(document).on("touchcancel", function(){
      dragging = false;
});

$('#register_container').on('blur', '#item, #customer, #supplier, #location, #amount_tendered, #comment, .keyboard', function(e){
	
	$input = $(this);
	$input.popover('hide');

	if ($input.attr('id') == 'item')
	{
		if(!submitting)
		{
			$("#register_container").animate({ marginTop: '0px'}, 500);
		}
	}

});