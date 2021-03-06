 module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg :   grunt.file.readJSON( '../package.json' ),
        namespace     : grunt.option( "slug" ),
        curl: {
            '../assets/js/handlebars.min-latest.js' : 'http://builds.handlebarsjs.com.s3.amazonaws.com/handlebars.min-latest.js'
        },
        copy: {
            main: {
                files:[
                    {
                        expand: false,
                        cwd: './',
                        src: 'uix-bootstrap.php',
                        dest: '../<%= pkg.namespace %>-bootstrap.php'
                    },
                    {
                        expand: true,
                        cwd: 'includes',
                        src: '**',
                        dest: '../includes/'
                    },
                    {
                        expand: true,
                        cwd: 'assets/css',
                        src: '**',
                        dest: '../assets/css/'
                    },
                    {
                        expand: true,
                        cwd: 'assets/js',
                        src: '**',
                        dest: '../assets/js/'
                    },
                    {
                        expand: true,
                        cwd: 'assets/svg',
                        src: '**',
                        dest: '../assets/svg/'
                    },
                    {
                        expand: true,
                        cwd: 'assets/controls',
                        src: '**',
                        dest: '../assets/controls/'
                    },
                    {
                        expand: true,
                        cwd: 'classes/uix/',
                        src: '**',
                        dest: '../classes/<%= pkg.namespace %>/'
                    }
                ]
            }
        },
        rename: {
            core: {
                src: '../classes/<%= pkg.namespace %>/ui/uix.php',
                dest: '../classes/<%= pkg.namespace %>/ui/<%= pkg.namespace %>.php'
            }
        },
        uglify: {
            min: {
                files: grunt.file.expandMapping( [
                    'assets/js/*.js',
                    '!assets/js/*.min.js',
                    '!assets/js/*.min-latest.js'
                ], '../assets/js/', {
                    rename : function ( destBase, destPath ) {
                        return destBase + destPath.replace( '.js', '.min.js' );
                    },
                    flatten: true
                } )
            }
        },
        cssmin: {
            options: {
                keepSpecialComments: 0
            },
            minify : {
                expand: true,
                cwd   : '../assets/css/',
                src   : ['*.css', '!*.min.css'],
                dest  : '../assets/css/',
                ext   : '.min.css'
            }
        },
        clean: {
            build: ["etc/**", "node_modules/**",".git/**",".gitignore","composer.json","Gruntfile.js","package.json"],
        },

    });

     //load modules
     grunt.loadNpmTasks( 'grunt-curl' );
     grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
     grunt.loadNpmTasks( 'grunt-contrib-uglify' );
     grunt.loadNpmTasks( 'grunt-contrib-copy' );
     grunt.loadNpmTasks( 'grunt-contrib-clean' );
     grunt.loadNpmTasks( 'grunt-rename' );
     //installer tasks
     grunt.registerTask( 'default', [ 'curl', 'copy', 'rename', 'cssmin', 'uglify', 'clean' ] );

 };
