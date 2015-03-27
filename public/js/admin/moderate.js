$('a.moderate').click(function() {
	var link = $(this);
	var countHolder = $('#reviews-count');
	var reviewElement = link.closest('div.review');
	var actualCount = countHolder.text();
	countHolder.text(actualCount - 1);

	$.get('/admin/moderate/' + link.data('review-id') + '/' + link.data('action')).success(handleResponse);
	reviewElement.fadeOut();
	return false;
})

function handleResponse() {
	console.log("OK");
}