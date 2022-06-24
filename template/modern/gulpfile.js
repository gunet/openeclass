let gulp = require('gulp'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  $ = require('gulp-load-plugins')(),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  postcssInlineSvg = require('postcss-inline-svg'),
  browserSync = require('browser-sync').create()
  pxtorem = require('postcss-pxtorem'),
	postcssProcessors = [
		postcssInlineSvg({
      removeFill: true,
      paths: ['./node_modules/bootstrap-icons/icons']
    }),
		pxtorem({
			propList: ['font', 'font-size', 'line-height', 'letter-spacing', '*margin*', '*padding*'],
			mediaQuery: true
		})
  ];

const paths = {
  scss: {
    src: './scss/style.scss',
    dest: './css',
    watch: './scss/**/*.scss',
    bootstrap: './node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss',
  },
  js: {
    bootstrap: './node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js',
    dest: './js'
  }
}

// Compile sass into CSS & auto-inject into browsers
function styles () {
  return gulp.src([paths.scss.bootstrap, paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass({
      includePaths: [
        './node_modules/bootstrap-sass/assets/stylesheets'
      ]
    }).on('error', sass.logError))
    //.pipe($.postcss(postcssProcessors))
    .pipe(postcss([autoprefixer({
      browsers: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
    //.pipe(browserSync.stream())
}

// Move the javascript files into our js folder
function js () {
  return gulp.src([paths.js.bootstrap])
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// Static Server + watching scss/html files
function serve () {
  browserSync.init({
    proxy: 'https://eclass-new.cfserver3.net/',
  })

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload)
}

const build = gulp.series(styles, gulp.parallel(js, serve))

exports.styles = styles
exports.js = js
exports.serve = serve

exports.default = build
