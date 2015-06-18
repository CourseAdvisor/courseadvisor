<b>>>> English below</b>

<p>Bonjour {{{ $review->student->firstname }}}, </p>

<p>Tu as récemment posté un avis sur CourseAdvisor, et nous t'en remercions.</p>

<p><b>Cours :</b> {{{ $review->course->name }}}<br />
<b>Notes :</b> {{{ $review->lectures_grade}}}/5 (cours) - {{{ $review->exercises_grade}}}/5 (exercices) - {{{ $review->content_grade}}}/5 (contenu)<br /></p>
<p>« <b>{{{ $review->title }}}</b><br />
<i>{{{ $review->comment }}}</i> »</p>

<p>Malheureusement, il semble que ton avis ne respecte pas les conditions de CourseAdvisor, et celui-ci a été refusé par un administrateur pour la raison suivante :</p>
<p><i>{{ $reasons['fr'] /* HTML volontaire */ }}</i></p>

<p>Nous t'encourageons à modifier ton avis en conséquence, puis à le reposter sur le site.</p>

<p>Meilleures salutations,<br />
L'équipe CourseAdvisor.</p>

<hr />
<p></p>
<p>Hi {{{ $review->student->firstname }}}, </p>

<p>You have recently posted a review on CourseAdvisor, and we thank you for that.</p>

<p><b>Course :</b> {{{ $review->course->name }}}<br />
<b>Ratings :</b> {{{ $review->lectures_grade}}}/5 (lectures) - {{{ $review->exercises_grade}}}/5 (exercises) - {{{ $review->content_grade}}}/5 (content)<br /></p>
<p>« <b>{{{ $review->title }}}</b><br />
<i>{{{ $review->comment }}}</i> »</p>

<p>Unfortunately, your review looks like it does not respect our review policy and has been rejected by an administrator, who gave the following reason:</p>
<p><i>{{ $reasons['en'] /* HTML volontaire */ }}</i></p>

<p>You are welcome to post again your review after having modified it accordingly.</p>

<p>Best,<br />
The CourseAdvisor team.</p>