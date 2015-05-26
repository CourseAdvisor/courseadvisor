/*
 *  fontawesome-based starbar component by hmil
 *
 *  TODO: doc
 */

var StarBar = (function() {

  function noop() {}


  var defaults = {
    onChange: noop,
    value: 0,
    interactive: true,
    input: true,
    inputName: 'rating',
    clearable: false
  };

  function applyDefaults(opts) {
    var ret = {};
    var keys = Object.keys(defaults);
    for (var i in keys) {
      ret[keys[i]] = opts.hasOwnProperty(keys[i]) ? opts[keys[i]] : defaults[keys[i]];
    }
    return ret;
  }

  function StarBar(el, opts) {
    opts = applyDefaults(opts || {});

    var fillings = this._fillings = new Array(5);
    var star;
    var _this = this;

    this._changeCb = opts.onChange;
    this.value = opts.value;
    this._input = $('<input type="hidden" />');
    if (opts.input) {
      this._input.attr({name: opts.inputName}).val(this.value);
      el.append(this._input);
    }

    el.addClass('starbar');
    if (opts.interactive) el.addClass('clickable');
    if (opts.clearable) {
      var clearBtn = $('<a href="#" class="starbar-clear"><i class="fa fa-times"></i></a>');
      clearBtn.on('click', function(evt) {
        evt.preventDefault();
        _this.setValue(0);
      });
      el.parent().append(clearBtn);
    }

    for (var i = 0; i < 5; ++i) {
      star = makeStar(this, i+1);
      el.append(star.$el);
      fillings[i] = star.filling;
    }
    this.setValue(this.value, {silent: true});
  }

  StarBar.prototype.setValue = function(value, opts) {
    var oldValue = this.value;
    this.value = value;
    this._input.val(this.value);
    opts = opts || {silent: false};

    this.showValue(this.value);

    if (!opts.silent)
      this._changeCb(this.value, oldValue);

    return this;
  };

  StarBar.prototype.showValue = function(value) {
    var i;
    var fillings = this._fillings;

    for(i = 0 ; value >= 0.75 ; ++i, --value) {
      fillings[i].removeClass('fa-star-o fa-star-half-o').addClass('fa-star');
    }
    if (value > 0 && i < 5) {
      fillings[i].removeClass('fa-star fa-star-o').addClass('fa-star-half-o');
      ++i;
    }
    for ( ; i < 5 ; ++i) {
      fillings[i].removeClass('fa-star fa-star-half-o').addClass('fa-star-o');
    }
  }

  StarBar.prototype.getValue = function() {
    return this.value;
  };

  function makeStar(ctx, id) {

    var outer = $('<i />').addClass('fa fa-star-o fa-stack-2x');
    var filling = $('<i />').addClass('fa fa-star-o fa-stack-2x filling');

    var $el = $('<span />').addClass('fa-stack')
      .append(filling)
      .append(outer)
      .on('click', function() {
        ctx.setValue(id);
      }).on('mouseenter', function() {
        ctx.showValue(id);
      }).on('mouseleave', function() {
        ctx.showValue(ctx.value);
      });

    return {
      $el: $el,
      filling: filling
    };
  }

  return StarBar;
}());
