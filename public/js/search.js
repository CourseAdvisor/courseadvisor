var holder = $('input[type=hidden]#sections-filter-list');
var checkboxes = $('input[type=checkbox].section-filter');
/*checkboxes.change(function() {
	var checkbox = $(this);
	var wasChecked = checkbox.is(':checked');
	var sectionsList = holder.val().split("-");
	var clicked = checkbox.data('section-id')+"";
	var index = sectionsList.indexOf(clicked);

	if (wasChecked && index < 0) {
		sectionsList.push(clicked);
	}
	else if (!wasChecked && index >= 0) {
		sectionsList.splice(index, 1);
	}

	console.log(sectionsList);
	holder.val(sectionsList.join("-"));
});*/

$('#filters-form').submit(function() {
	var checked = [];
	checkboxes.each(function() {
		if ($(this).is(':checked')) {
			checked.push($(this).data('section-id'));
		}
	});

	holder.val(checked.join("-"));
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