{{--
  Standard markup used to display a difficulty

  parameters:
  - int difficulty (required): the difficulty on a scale from 1 to 5 (included).
    a value <= 1 means not applicable (bar is rendered in disabled state).
--}}
<?php
$difficulty_class = "NA";
$difficulty_text = trans('courses.difficulty-hint-na');
if ($difficulty > 3.5) {
    $difficulty_class = $difficulty_text = trans('courses.difficulty-hint-hard');
} else if ($difficulty > 2.5) {
    $difficulty_class = $difficulty_text = trans('courses.difficulty-hint-medium');
} else if ($difficulty >= 1) {
    $difficulty_class = $difficulty_text = trans('courses.difficulty-hint-easy');
}
?>
<div class="difficulty-bar {{ $difficulty_class == 'NA' ? 'disabled' : '' }} difficulty-{{{ $difficulty_class }}}"
    title="{{{ $difficulty_text }}}">
    <div class="fill"></div>
</div>
