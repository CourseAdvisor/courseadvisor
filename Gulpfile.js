var gulp = require('gulp');
var Q = require('q');

var clean = require('gulp-clean');
var less = require('gulp-less');
var LessPluginCleanCSS = require('less-plugin-clean-css');
var LessPluginAutoPrefix = require('less-plugin-autoprefix');


gulp.task('style', function() {
  return gulp.src('assets/style/courseadvisor.less')
    .pipe(less({
      plugins: [
        new LessPluginCleanCSS({ advanced: true }),
        new LessPluginAutoPrefix({ browsers: ["last 2 versions"] })
      ]
    }))
    .pipe(gulp.dest('./public/css'));
});

gulp.task('watch', function() {
  return gulp.watch('./assets/style/*.less', ['style']);  // Watch all the .less files, then run the less task
});

gulp.task('publish-thirdparty', ['clean-thirdparty'], function() {
  var deferred = Q.defer();
  var callbacks = 2;

  function collect() {
    if (--callbacks <= 0) {
      deferred.resolve();
    }
  }

  gulp.src('assets/bower_components/font-awesome/css/font-awesome.min.css')
    .pipe(gulp.dest('./public/css'))
    .on('finish', collect);

  gulp.src('assets/bower_components/font-awesome/fonts/*')
    .pipe(gulp.dest('./public/fonts'))
    .on('finish', collect);

  return deferred.promise;
});

gulp.task('clean', ['clean-thirdparty', 'clean-style']);

gulp.task('clean-style', function() {
  return gulp.src('public/css/courseadvisor.*', {read: false})
      .pipe(clean());
});

gulp.task('clean-thirdparty', function() {
  var deferred = Q.defer();
  var callbacks = 2;

  function collect() {
    if (--callbacks <= 0) {
      deferred.resolve();
    }
  }

  gulp.src('public/css/font-awesome.*', {read: false})
      .pipe(clean())
      .on('finish', collect);

  gulp.src('public/fonts/*', {read: false})
      .pipe(clean())
      .on('finish', collect);

  return deferred.promise;
});