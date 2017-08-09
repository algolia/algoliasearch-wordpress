var Metalsmith  = require('metalsmith');
var sass        = require('metalsmith-sass');
var fs          = require('fs');
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
var imagemin    = require('metalsmith-imagemin');
var algoliaComponents = require('algolia-frontend-components');
var webpack = require('metalsmith-webpack')

var communityHeaderData = JSON.parse(fs.readFileSync('./component_data/communityHeader.json').toString());

var sassPaths = [
    'node_modules/foundation-sites/scss',
    'node_modules/algolia-components/dist/communityHeader.css'
];

var siteBuild = Metalsmith(__dirname)
    // Allow for relative url generation.
    .metadata({
        title: 'Algolia Search Plugin for WordPress',
        url: 'https://github.com/algolia/algoliasearch-wordpress',
        version: '2.6.1',
        time: new Date().getTime(),
        tweets:['666409672006606848','675635141713248256','684325213329305600','669552193419259904','672084577805012992','714625225359425536','669555344725696512','688027404741308417','783838738791227392','782584336323227648','787040561215582208','698839453469544448','687060441881796608','705467858961223680','665028633048821760','654785137272459265','661567388983279617','708574926962294784','707863195025858560'],
        header: algoliaComponents.communityHeader(communityHeaderData)
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
        dest: './deps/jquery'
    }))

    .use(asset({
        src: './node_modules/foundation-sites/dist',
        dest: './deps/foundation-sites'
    }))

    .use(asset({
        src: './node_modules/algolia-frontend-components/dist/_communityHeader.js',
        dest: './js/communityHeader.js'
    }))

    .use(imagemin({
        gifsicle: {},
        jpegrecompress: { quality: 'medium' },
        pngquant: { quality: '65-80' },
        svgo: {}
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
                '${source}/img/**/*.*': true,
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
