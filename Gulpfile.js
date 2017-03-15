'use strict';

var gulp           =  require('gulp'),
    concat         =  require('gulp-concat'),
    uglify         =  require('gulp-uglify'),

    newer          =  require('gulp-newer'),
    imagemin       =  require('gulp-imagemin'),
    rename         =  require('gulp-rename'),
    
    sass           =  require('gulp-sass'),
    sassGlob       =  require('gulp-sass-glob'),
    sourcemaps     =  require('gulp-sourcemaps'),
    cssmin         =  require('gulp-cssmin'),
    autoprefixer   =  require('gulp-autoprefixer'),
    
    browserSync    =  require('browser-sync').create(),

    // This two modules are for handling the Delete Event on Watch
    del            =  require('del'),
    path           =  require('path');

var bwpath  = ('bower_components/');

/* -------------- Sass/Css -------------- */

gulp.task('sass', function () {
  gulp.src('src/sass/main.scss')
    .pipe(sassGlob())
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('build/css'))
    .pipe(cssmin())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('build/css'))
    .pipe(browserSync.reload({stream:true}));
});

gulp.task('sass-styleguide', function () {
  gulp.src('src/sass/styleguide/main.scss')
    .pipe(sassGlob())
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('build/css/styleguide'))
    .pipe(cssmin())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('build/css/styleguide'))
    .pipe(browserSync.reload({stream:true}));
});

/* -------------- Imagemin -------------- */

gulp.task('imagemin', function () {
  gulp.src('src/images/**')
    .pipe(newer('build/images/'))
    .pipe(imagemin({
      progressive: true,
    }))
    .pipe(gulp.dest('build/images/'))
    .pipe(browserSync.reload({stream:true}));
});

/* -------------- Js -------------- */

gulp.task('headjs', function() {
  gulp.src([])
    .pipe(sourcemaps.init({loadMaps: true}))
    .pipe(concat('head.min.js'))
    .pipe(sourcemaps.write('../js'))
    .pipe(gulp.dest('build/js'));
});

gulp.task('footerjs', function() {
  gulp.src([
    bwpath + 'jquery/dist/jquery.min.js',
    bwpath + 'foundation-sites/dist/foundation.min.js',
  ])
  .pipe(sourcemaps.init({loadMaps: true}))
  .pipe(concat('footer.min.js'))
  .pipe(sourcemaps.write('../js'))
  .pipe(gulp.dest('build/js'));
});

gulp.task('blockjs', function() {
  gulp.src('src/js/blocks/*.js')
    .pipe(gulp.dest('build/js/blocks'));
});

gulp.task('styleguidejs', function() {
  gulp.src('src/js/styleguide/*.js')
    .pipe(gulp.dest('build/js/styleguide'));
});

/* -------------- Watch -------------- */

gulp.task('watch', function () {
  gulp.watch('src/sass/**/**/*.scss', ['sass']);
  gulp.watch('src/sass/styleguide/**/*.scss', ['sass-styleguide']);
  gulp.watch('src/js/blocks/**/*.js', ['scripts']);
  gulp.watch('src/js/vendor/**/*.js', ['scripts']);
  gulp.watch('src/js/styleguide/**/*.js', ['styleguidejs']);
  gulp.watch('src/views/**/*.twig', browserSync.reload);

  // Handling the Delete Event on Watch
    var watcher = gulp.watch('src/images/**', ['imagemin']);
    watcher.on('change', function (event) {
      if (event.type === 'deleted') {
        var filePathFromSrc = path.relative(path.resolve('src'), event.path);
        var destFilePath = path.resolve('build', filePathFromSrc);
        del.sync(destFilePath);
      }
    });

});

/* -------------- BrowserSync -------------- */

gulp.task('browser-sync', function() {
  browserSync.init({
    proxy: "localhost/itcss",
    open: false
  });
});

gulp.task('scripts', [
  'headjs',
  'footerjs',
  'blockjs',
  'styleguidejs'
]);

gulp.task('default', [
  'sass',
  'sass-styleguide',
  'scripts',
  'browser-sync',
  'watch'
]);
