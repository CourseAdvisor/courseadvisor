{{--
    Starbar partial
    ===============

Shows a 5 star bar for grading.

parameters:
- int $grade: # of active stars (0 to 5)
- string $comment_unsafe: unescaped comment appended after the starbar
- bool $disabled: when true, the bar is grayed out. Useful for showing that the grade is not applicable (ie. no data yet)
- compact: show only one star
--}}

<?php
  $grade = isset($grade) ? $grade : 0;
  if (isset($compact) && $compact == TRUE && !isset($comment_unsafe) && (!isset($disabled) || $disabled != TRUE)) {
    $comment_unsafe = (round($grade*2)/2).'/5';
  }
?>

<div class="starbar pull-left {{{ isset($disabled) && $disabled == TRUE ? 'disabled' : '' }}}" >
@if(isset($compact) && $compact == TRUE)
  <span class="fa-stack">
    @if($grade >= 3.5)
      <i class="fa fa-star fa-stack-2x filling"></i>
    @elseif($grade > 2)
      <i class="fa fa-star-half-o fa-stack-2x filling"></i>
    @else
      <i class="fa fa-star-o fa-stack-2x filling"></i>
    @endif
    <i class="fa fa-star-o fa-stack-2x"></i>
  </span>
@else
  @for($i = 1; $i <= 5; ++$i)
    <span class="fa-stack">
      @if($grade >= $i)
        <i class="fa fa-star fa-stack-2x filling"></i>
      @elseif($grade < $i && $grade > $i-1)
        <i class="fa fa-star-half-o fa-stack-2x filling"></i>
      @else
        <i class="fa fa-star-o fa-stack-2x filling"></i>
      @endif
      <i class="fa fa-star-o fa-stack-2x"></i>
    </span>
  @endfor
@endif
</div>

@if(isset($comment_unsafe))
  <span class="starbar-comment {{{ isset($disabled) && $disabled == TRUE ? 'disabled' : '' }}}">{{ $comment_unsafe }}</span>
@endif
