// Based on: "Copyright 2006-2007 javascript-array.com"
(function() {

  var timeout = 500;
  var closetimer  = 0;
  var ddmenuitem  = 0;


  var prevLoad = document.body.onload;
  document.body.onload = function() {
    if (prevLoad) prevLoad();

    var d = document.querySelectorAll('[data-dropdown]');
    for (var i = 0 ; i < d.length ; i++) {
      mkdropdown(d[i]);
    }
  }

  function mkdropdown(el) {
    var target = document.getElementById(el.getAttribute('data-dropdown'));

    el.addEventListener('mousedown', function(evt){
      mopen(target);
    }, false);
    el.addEventListener('mouseleave', mclosetime, false);

    target.addEventListener('mouseenter', mcancelclosetime, true);
    target.addEventListener('mouseleave', mclosetime, true);
  }


  // open hidden layer
  function mopen(id)
  {
    // cancel close timer
    mcancelclosetime();

    // close old layer
    if(ddmenuitem) ddmenuitem.style.display = 'none';

    // get new layer and show it
    ddmenuitem = (typeof id === 'string') ? document.getElementById(id) : id;
    ddmenuitem.style.display = 'block';

  }
  // close showed layer
  function mclose()
  {
    if(ddmenuitem) ddmenuitem.style.display = 'none';
  }

  // go close timer
  function mclosetime()
  {
    closetimer = window.setTimeout(mclose, timeout);
  }

  // cancel close timer
  function mcancelclosetime()
  {
    if(closetimer)
    {
      window.clearTimeout(closetimer);
      closetimer = null;
    }
  }
}());
