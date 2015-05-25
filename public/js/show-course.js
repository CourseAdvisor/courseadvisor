$('form#create-review-form input[type=checkbox]#anonymous').change(function() {
	var checkbox = $(this);
	debugger;
	if (checkbox.is(':checked')) {
		$('form#create-review-form #anonymous-warning').removeClass('hidden');
	}
});

var modal = $('#edit-review-modal');

if (window.location.hash.indexOf('!edit') == 1) {
	var id = window.location.hash.split("-")[1];
	showEditModal($('a.edit-review[data-review-id='+id+']').first());
}

$('a.edit-review').click(function() {
	showEditModal($(this));
	return false;
})

modal.on('hidden.bs.modal', function() {
	window.location.hash = "#!";
})

function showEditModal(openingLink) {
	modal.modal('show');
	var reviewElement = openingLink.closest('div.review');
	var reviewId = openingLink.data('review-id');
	var reviewGrades = {
		exercises: openingLink.data('review-exercises-grade'),
		lectures: openingLink.data('review-lectures-grade'),
		content: openingLink.data('review-content-grade')
	};
	var reviewContent = reviewElement.find('p.review-content').text();
	var reviewTitle = openingLink.data('review-title');
	var reviewDifficulty = openingLink.data('review-difficulty') || 0;
	var reviewAnonymous = openingLink.data('review-anonymous');

	window.location.hash = "#!edit-" + reviewId;

	modal.find('input[type=hidden][name=review-id]').val(reviewId);
	modal.find('input[name=title]').val(reviewTitle);
	modal.find('textarea[name=comment]').val(reviewContent);
	modal.find('input[name=difficulty][value='+reviewDifficulty+']').attr('checked', 'checked');
	modal.find('input[name=reviewId]').val(reviewId);

	if (reviewAnonymous === 1) {
		modal.find('input#anonymous').attr('checked', 'checked');
	}
	for(var gradeType in reviewGrades) {
		modal.find('[data-starbar^=' + gradeType + '_grade]').data('starbar').setValue(reviewGrades[gradeType]);
	}
}