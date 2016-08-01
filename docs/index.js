var Metalsmith  = require('metalsmith');
var sass        = require('metalsmith-sass');
var markdown    = require('metalsmith-markdown');
var layouts     = require('metalsmith-layouts');
var rootPath    = require('metalsmith-rootpath');
var serve       = require('metalsmith-serve');
var watch       = require('metalsmith-watch');
var metallic    = require('metalsmith-metallic');
var sitemap     = require('metalsmith-mapsite');
var asset       = require('metalsmith-static');
var helpers     = require('metalsmith-register-helpers');
var headingsid  = require('metalsmith-headings-identifier');
var file        = require('./plugins/file/index.js');

var sassPaths = [
    'node_modules/foundation-sites/scss'
];


var siteBuild = Metalsmith(__dirname)
    // Allow for relative url generation.
    .metadata({
        title: 'Algolia Search Plugin for WordPress',
        url: 'https://github.com/algolia/algoliasearch-wordpress-plugin',
        version: '0.2.6',
        time: new Date().getTime(),
    })



    .source('./src')
    .destination('./build')

    .use(file())

    // Compile sass files.
    .use(sass({
        includePaths: sassPaths,
        outputDir: 'css/'
    }))

    // Copy vendor assets to the build.
    .use(asset({
        src: './node_modules/jquery/dist',
        dest: './vendor/jquery'
    }))

    .use(asset({
        src: './node_modules/foundation-sites/dist',
        dest: './vendor/foundation-sites'
    }))

    // Add Highlight.js for code snippets.
    .use(metallic())

    // Parse Markdown.
    .use(markdown())


    // Register custom handlebars helpers.
    .use(helpers({
        directory: 'helpers'
    }))


    .use(headingsid())

    // Inject rootPath in every file metadata to be able to make all urls relative.
    // Allows to deploy the website in a directory.
    .use(rootPath())

    .use(layouts({
        engine: 'handlebars',
        partials: 'partials'
    }))

    // Generate a sitemap.xml.
    .use(sitemap('https://community.algolia.com/wordpress/'));

if (process.env.NODE_ENV !== 'production') {
    siteBuild

    // Serve on localhost:8080.
    .use(serve())

    // Watch for changes.
    .use(
        watch({
            paths: {
                '${source}/**/*.md': true,
                '${source}/sass/**/*.scss': 'sass/app.scss',
                'layouts/**/*.html': '**/*.md',
                'partials/**/*.html': '**/*.md'
            },
            livereload: true,
        })
    );
}

// Display errors.
siteBuild.build(function(err, files) {
    if (err) { throw err; }
});
