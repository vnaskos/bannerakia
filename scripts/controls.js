jQuery(document).ready(function($) {

	$(document).on('click','.add-button' ,function() {
		from = $(this).prev().closest(".unordered-list");
		to = $(this).parent().next().children(".ordered-list");
		$selected = from.children('option:selected');
		$selected = $selected.map(function(){return '<option value="'+this.value+'">'+this.text+'</option>' }).get();
		to.append($selected);
		updateOrder(to);
	});

	$(document).on('click','.remove-button' ,function() {
		from = $(this).parent().children(".ordered-list");
		from.children('option:selected').remove();
		updateOrder(from);
	});

	$(document).on('click','.up-button' ,function() {
		to = $(this).parent().children(".ordered-list");
		to.children('option:selected:first-child').prop("selected", false);
		before = to.children('option:selected:first').prev();
		to.children('option:selected').detach().insertBefore(before);
		updateOrder(to);
	});

	$(document).on('click','.down-button' ,function() {
		to = $(this).parent().children(".ordered-list");
		to.children('option:selected:last-child').prop("selected", false);
		after = to.children('option:selected:last').next();
		to.children('option:selected').detach().insertAfter(after);
		updateOrder(to);
	});

	function updateOrder(to) {
		var optionValues = [];
	
		to.children('option').each(function() {
			optionValues.push($(this).val());
		});
	
		$('.hidden-order').val(optionValues);
	}

});
