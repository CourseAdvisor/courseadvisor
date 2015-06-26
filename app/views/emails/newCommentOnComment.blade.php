<p>Bonjour {{{ $parent->student->firstname }}}, </p>

<p>{{{ ucfirst($who->fullname) }}} a répondu à ton commentaire « {{{ Str::words($parent->body, 13) }}} ».<br />
Clique <a href="{{{ LaravelLocalization::getLocalizedURL('fr', action('CourseController@show', [
    'id' => $review->course->id,
    'slug' => Str::slug($review->course->name_fr)
    ])) }}}#comment-{{{ $comment->id }}}">ici</a> pour voir le commentaire.</p>

<p>L'équipe CourseAdvisor.</p>

<hr />
<br>
<p>Hi {{{ $parent->student->firstname }}}, </p>

<p>{{{ ucfirst($who->fullname) }}} has replied to your comment "{{{ Str::words($parent->body, 13) }}}".<br />
Click <a href="{{{ LaravelLocalization::getLocalizedURL('en', action('CourseController@show', [
    'id' => $review->course->id,
    'slug' => Str::slug($review->course->name_en)
    ])) }}}#comment-{{{ $comment->id }}}">here</a> to see the comment.</p>

<p>The CourseAdvisor team.</p>