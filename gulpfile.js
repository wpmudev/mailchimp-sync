var gulp = require('gulp'),
	bower = require('gulp-bower');

function get_mailchimp_api() {

	var files = [
		'bower_components/mailchimp-api-php/src/*.*',
		'bower_components/mailchimp-api-php/src/Mailchimp',
		'bower_components/mailchimp-api-php/src/Mailchimp/*.*',
	];

	return bower({ cmd: 'update'})
		.pipe( gulp.src( files, { base: './bower_components/mailchimp-api-php/src/' } ) )
		.pipe( gulp.dest( 'mailchimp-api' ) );
	
}

gulp.task( 'default', function() {});

gulp.task( 'update', function() {
	return get_mailchimp_api();
});

gulp.task( 'install', function() {
	return get_mailchimp_api();
});

