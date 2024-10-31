'use strict';

// Config
// ========================================= //

// Config
var config = require('./config.json');

// Require
var gulp = require('gulp');
var gutil = require('gulp-util');
var plumber = require('gulp-plumber');
var sourcemaps = require('gulp-sourcemaps');
var sass = require('gulp-sass');
var del = require('del');
var autoprefixer = require('gulp-autoprefixer');
var browserSync = require('browser-sync').create();

// scss to css [local]
gulp.task('myposeo:styles', function() {
    //del(config.styles.local_build + '/*');

    return gulp.src(config.styles.local_libs)
        .pipe(plumber())
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: config.browserslist,
            cascade: false
        }))
        .pipe(gulp.dest(config.styles.local_build))
        .pipe(browserSync.stream());
});
