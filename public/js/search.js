var sectionsCheckboxes = $('input[type=checkbox].section-filter');
var sectionsHolder = $('input[type=hidden]#sections-filter-list');

var semestersCheckboxes = $('input[type=checkbox].semester-filter');
var semestersHolder = $('input[type=hidden]#semesters-filter-list');

$('#filters-form').submit(function() {
	var checked = [];
	sectionsCheckboxes.each(function() {
		if ($(this).is(':checked')) {
			checked.push($(this).data('section-id'));
		}
	});

	if (checked.length == sectionsCheckboxes.size()) {
		sectionsHolder.val('all');
	}
	else {
		sectionsHolder.val(checked.join("-"));
	}

	// Semesters
	checked.length = 0;
	semestersCheckboxes.each(function() {
		if ($(this).is(':checked')) {
			checked.push($(this).data('semester'));
		}
	});

	if (checked.length == semestersCheckboxes.size()) {
		semestersHolder.val('all');
	}
	else {
		semestersHolder.val(checked.join('-'));
	}
})

$('a.sections-check-all').click(function(e) {
	sectionsCheckboxes.prop('checked', true)
	return false;
})

$('a.semesters-check-all').click(function(e) {
	semestersCheckboxes.prop('checked', true);
	return false;
});

$('a.semesters-uncheck-all').click(function(e) {
	semestersCheckboxes.prop('checked', false);
	return false;
})

$('a.sections-uncheck-all').click(function(e) {
	sectionsCheckboxes.prop('checked', false);
	return false;
})

$('a.sections-check-mine').click(function(e) {
	sectionsCheckboxes.prop('checked', false);
	$('input[type=checkbox][data-is-student-section=1].section-filter').prop('checked', true);
	return false;
})