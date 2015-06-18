// Acceptation
$('.moderate[data-action=accept]').click(function() {
	var review_id = $(this).data('review-id');
	var data = {
		review_id: review_id,
		decision: 'accept',
		_token: TOKEN
	};
	$.post('/admin/moderate', data);
	$('body').find('div.review[data-review-id='+review_id+']').fadeOut();
	decrementRemainingReviews();
})

// Rejection
$('#reasonModal').on('show.bs.modal', function(evt) {
	var link = $(evt.relatedTarget);
	var review_id = link.data('review_id');
	var modal = $(this);
	modal.find('.modal-body input#review_id').val(review_id);
	modal.find('.modal-footer .submit').click(clickHandler.bind(null, modal));
});

function clickHandler(modal) {
	var form = modal.find('form')
	var review_id = form.find('#review_id').val();
	var data = {
		decision: 'reject',
		review_id: review_id,
		reason: {
			fr: form.find('#reason_fr').val(),
			en: form.find('#reason_en').val()
		},
		_token: TOKEN
	};
	$.post('/admin/moderate', data);
	modal.modal('hide');
	$('body').find('div.review[data-review-id='+review_id+']').fadeOut();
	decrementRemainingReviews();
	form.find('#reason_fr').val('');
	en: form.find('#reason_en').val('');
}

function decrementRemainingReviews() {
	var countHolder = $('#reviews-count');
	countHolder.text((countHolder.text() - 1) || "No");
}