var randomString = function(n) {
	var str = "";
	var alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	for (var i = 0; i < n; ++i) {
		var index = Math.floor(Math.random() * alphabet.length);
		str += alphabet.charAt(index);
	}
	return str;
}

var fillRandomReview = function() {
	var $review = $('body').find('form#create-review-form');

	$review.find('input[name=title]').val(randomString(10));
	$review.find('textarea[name=comment]').val(randomString(100));
	$review.find('#difficulty-1').attr('checked', 'checked');
}

/* Event handling stuff */
var pressed = {
	r: false,
	alt: false
};

$(document).keydown(function(evt) {
	if (evt.keyCode == 'R'.charCodeAt(0))
		pressed.r = true;
	else if (evt.altKey)
		pressed.alt = true;
})

$(document).keyup(function (evt) {
	if (evt.keyCode == 'R'.charCodeAt(0))
		pressed.r = false;
	else if (evt.altKey)
		pressed.alt = false;
})

Object.observe(pressed, function(changes) {
	if (pressed.r && pressed.alt) {
		fillRandomReview();
	}
});