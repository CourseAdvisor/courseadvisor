{{--
    Starbar partial
    ===============

Shows a 5 star bar for grading.

parameters:
- int $grade: # of active stars (0 to 5)
- string $comment_unsafe: unescaped comment appended after the starbar
- bool $disabled: when true, the bar is grayed out. Useful for showing that the grade is not applicable (ie. no data yet)

--}}

<?php $grade = isset($grade) ? $grade : 0; ?>

<div class="starbar pull-left{{{ isset($disabled) && $disabled == TRUE ? ' disabled' : '' }}}" >
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
</div>

@if(isset($comment_unsafe))
  <span class="starbar-comment">{{ $comment_unsafe }}</span>
@endif
