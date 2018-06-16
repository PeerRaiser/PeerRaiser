'use strict';

var gulp		 = require( 'gulp' ),
    sass		 = require( 'gulp-sass' ),
    autoprefixer = require( 'gulp-autoprefixer' ),
    minifycss	 = require( 'gulp-uglifycss' ),
    imagemin	 = require( 'gulp-imagemin' ),
    pngquant	 = require( 'imagemin-pngquant' ),
    watch		 = require( 'gulp-watch' ),
    concat	     = require( 'gulp-concat' ),
    lineec	     = require( 'gulp-line-ending-corrector' );

const AUTOPREFIXER_BROWSERS = [
    'last 2 version',
    '> 1%',
    'ie >= 9',
    'ie_mob >= 10',
    'ff >= 30',
    'chrome >= 34',
    'safari >= 7',
    'opera >= 23',
    'ios >= 7',
    'android >= 4',
    'bb >= 10'
];

// Public JS
gulp.task( 'public-scripts', function() {
    return gulp.src([
        './src/js/public/*.js'
        ])
        .pipe( concat('peerraiser-public.js') )
        .pipe( gulp.dest('./dist/js') );
} );

// Admin JS
gulp.task( 'admin-scripts', function() {
    return gulp.src([
        './src/js/admin/*.js'
    ])
        .pipe( concat('peerraiser-admin.js') )
        .pipe( gulp.dest('./dist/js') );
} );

// Public Sass
gulp.task( 'public-sass', function() {
    return gulp.src('./src/scss/public/**/*.scss')
        .pipe( sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )
        .pipe( minifycss( {
            maxLineLen: 0
        }))
        .pipe( lineec() )
        .pipe( gulp.dest('./dist/css') );
} );

// Admin Sass
gulp.task( 'admin-sass', function() {
    return gulp.src('./src/scss/admin/**/*.scss')
        .pipe( sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )
        .pipe( minifycss( {
            maxLineLen: 0
        }))
        .pipe( lineec() )
        .pipe( gulp.dest('./dist/css') );
} );

// Image minification
gulp.task( 'imagemin', function() {
    return gulp.src( './src/images/*' )
        .pipe( imagemin({
            progressive: true,
            svgoPlugins: [
                {removeViewBox: false},
                {cleanupIDs: false}
            ],
            use: [pngquant()]
        }) )
        .pipe( gulp.dest('./dist/images') );
} );

// Watch
gulp.task( 'watch', ['public-sass', 'admin-sass', 'public-scripts', 'admin-scripts', 'imagemin'], function() {
    gulp.watch('./src/scss/admin/**/*.scss', ['sass']);
    gulp.watch('./src/scss/public/**/*.scss', ['sass']);
    gulp.watch('./src/js/admin/*.js', ['scripts']);
    gulp.watch('./src/js/public/*.js', ['scripts']);
} );

// Default "gulp" task
gulp.task('default', ['public-sass', 'admin-sass', 'public-scripts', 'admin-scripts', 'imagemin']);