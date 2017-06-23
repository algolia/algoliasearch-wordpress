module.exports = function( grunt ) {

  'use strict';
  var banner = '/**\n * <%= pkg.homepage %>\n * Copyright (c) <%= grunt.template.today("yyyy") %>\n * This file is generated automatically. Do not edit.\n */\n';
  // Project configuration
  grunt.initConfig( {

    pkg: grunt.file.readJSON( 'package.json' ),

    addtextdomain: {
      options: {
        textdomain: 'algolia',
      },
      target: {
        files: {
          src: [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**', '!vendor/**', '!build/**' ]
        }
      }
    },

    wp_readme_to_markdown: {
      your_target: {
        files: {
          'README.md': 'readme.txt'
        },
        options: {
          screenshot_url: "https://ps.w.org/search-by-algolia-instant-relevant-results/assets/{screenshot}.png"
        }
      },
    },

    makepot: {
      target: {
        options: {
          domainPath: '/languages',
          mainFile: 'algolia.php',
          potFilename: 'algolia.pot',
          potHeaders: {
            poedit: true,
            'x-poedit-keywordslist': true
          },
          type: 'wp-plugin',
          updateTimestamp: true
        }
      }
    },
  } );

  grunt.loadNpmTasks( 'grunt-wp-i18n' );
  grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );

  // Register tasks
  grunt.registerTask( 'default', [
    'makepot',
    'wp_readme_to_markdown'
  ]);

  grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );

  grunt.util.linefeed = '\n';

};
