var $=jQuery.noConflict();
$(document).ready(function() {  
	if ($('div.raffle').length > 0) {
		var counter = 0;
		$(".raffleticket").on("click", function() {
			$(this).toggleClass("green");
			$(this).toggleClass('selected');
			var selectedIds = $('.selected').map(function() {
				return this.id;
			}).get();
			var id = $(this).attr('id');  
			if(!$(this).hasClass('selected')) {
				$('#paypal_form #input_' + id).remove();
				counter--;
				var ii = 1;
				$("input[name^='item_name']").each(function(i){
					$(this).attr('name', 'item_name_' + ii++);
				});
				var ii = 1;
				$("input[name^='amount']").each(function(i){
					$(this).attr('name','amount_' + ii++);
				});
				var ii = 1;
				$("input[name^='item_number']").each(function(i){
					$(this).attr('name','item_number_' + ii++);
				});
			} else {
				counter++;
				$('#paypal_form').append('<input type="hidden" name="item_name_'+counter+'" value="'+tickettext.pre_text+'-' + id + '-' + tickettext.post_text + '" id="input_'+id+'">');
				$('#paypal_form').append('<input type="hidden" name="amount_'+counter+'" value="' + $(".ticketprice").text() +'" id="input_'+id +'">');
				$('#paypal_form').append('<input type="hidden" name="item_number_'+counter+'" value="' + id +'" id="input_'+id +'">');
			}	
			$(".ticketsselected").text(selectedIds.length -1);
			$(".totalcost").text((selectedIds.length - 1)*$(".ticketprice").text()); 
			//$('[name=amount]').val((selectedIds.length - 1)*$(".ticketprice").text());
			//$('[name=item_name]').val((selectedIds.length - 1) + " Raffle Ticket(s)");
			//alert(selectedIds);
			//alert('<?php echo $pre_text; ?>');
		
		});
	}
});//]]>  
