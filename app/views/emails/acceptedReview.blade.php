<b>>>> English below</b>

<p>Bonjour {{{ $review->student->firstname }}}, </p>

<p>Tu as récemment posté un avis anonyme sur CourseAdvisor. Nous avons le plaisir de t'informer que ton avis a été accepté,
et que celui-ci est visible dès maintenant sur la page du cours
<i><a href="{{{ action('CourseController@show',  [
	'slug' => Str::slug($review->course->name),
	'id' => $review->course->id
])}}}">
	{{{ $review->course->name_fr }}}</a></i>.
</p>

<p>En te remerciant pour ta contribution, <br />
L'équipe CourseAdvisor.</p>

<hr />
<p></p>
<p>Hi {{{ $review->student->firstname }}}, </p>

<p>You have recently posted an anonymous review on CourseAdvisor. We have the pleasure to inform you that your review has been accepted. It is visible on the
<i><a href="{{{ action('CourseController@show',  [
	'slug' => Str::slug($review->course->name),
	'id' => $review->course->id
])}}}">
	{{{ $review->course->name_en }}}</a></i> course page.</p>

<p>Thank you for your contribution,<br />
The CourseAdvisor team.</p>
