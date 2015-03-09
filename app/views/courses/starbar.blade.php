<div class="starbar pull-left">
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