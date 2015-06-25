<p>Bonjour {{{ $review->student->firstname }}}, </p>

<p>{{{ ucfirst($who->fullname) }}} a posté un commentaire sur ton avis pour le cours {{{ $review->course->name_fr }}}.<br />
Clique <a href="{{{ LaravelLocalization::getLocalizedURL('fr', action('CourseController@show', [
    'id' => $review->course->id,
    'slug' => Str::slug($review->course->name_fr)
    ])) }}}#comment-{{{ $comment->id }}}">ici</a> pour voir le commentaire.</p>

<p>L'équipe CourseAdvisor.</p>

<hr />
<br>
<p>Hi {{{ $review->student->firstname }}}, </p>

<p>{{{ ucfirst($who->fullname) }}} has commented your review in the course {{{ $review->course->name_en }}}.<br />
Click <a href="{{{ LaravelLocalization::getLocalizedURL('en', action('CourseController@show', [
    'id' => $review->course->id,
    'slug' => Str::slug($review->course->name_en)
    ])) }}}#comment-{{{ $comment->id }}}">here</a> to see the comment.</p>

<p>The CourseAdvisor team.</p>
