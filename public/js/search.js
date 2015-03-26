var holder = $('input[type=hidden]#sections-filter-list');
var checkboxes = $('input[type=checkbox].section-filter');

$('#filters-form').submit(function() {
	var checked = [];
	checkboxes.each(function() {
		if ($(this).is(':checked')) {
			checked.push($(this).data('section-id'));
		}
	});

	if (checked.length == checkboxes.size()) {
		holder.val('all');
	}
	else {
		holder.val(checked.join("-"));
	}
})

$('a.sections-check-all').click(function(e) {
	checkboxes.prop('checked', true)
	return false;
})

$('a.sections-check-mine').click(function(e) {
	checkboxes.prop('checked', false);
	$('input[type=checkbox][data-is-student-section=1].section-filter').prop('checked', true);
	return false;
})

$('a.sections-uncheck-all').click(function(e) {
	checkboxes.prop('checked', false);
	return false;
})