var cheerio = require('cheerio');
var _ = require("lodash");

var util = require("util");
var extname = require('path').extname;

/**
 * Expose `plugin`.
 */

module.exports = plugin;

/**
 * A Metalsmith plugin to add an id + anchor to all headings on a page
 * ideal for permalinks
 * adapted from code and idea by remy sharp
 * (blog post: http://remysharp.com/2014/08/08/automatic-permalinks-for-blog-posts/)
 * (src file: https://github.com/remy/permalink/blob/master/permalink.js) !
 *
 * @param {Object} options
 * @return {Function}
 */

function plugin(options) {

    var opts = (typeof options === 'object') && options || {};

    // set default options or args
    opts.allow = opts.allow || false;
    opts.headingClass = opts.headingClass || '';

    return function(files, metalsmith, done) {
        setImmediate(done);

        Object.keys(files).forEach(function(file) {
            if (!html(file)) return;

            // should we check if headingsIdentifier should be run based on file metakey?
            if (opts.allow !== false) {
                // metakey provided in options, check if it's false, abort!
                if (files[file][opts.allow] !== true) {
                    return;
                }
            }

            var data = files[file];

            // load contents with cheerio to parse html nodes
            var $ = cheerio.load(data.contents.toString());

            // Set context if opts.context is provided
            var context = opts.context ? $(opts.context) : undefined;

            // Set selector if opts.selector is provided
            var selector = opts.selector || "h1,h2,h3,h4,h5,h6";

            $(selector, context).each(function(index, element) {

                // for each heading, check its id (and set if undefined) then append the anchor
                var id = $(element).attr("id");

                if (!id) {
                    return;
                }

                $(element).attr("data-magellan-target", id); // set the id
            });

            data.contents = new Buffer($.html()); // fixes #4 - we always need to use a buffer
            files[file] = data;
        });
    };
}

/**
 * Check if a `file` is html.
 *
 * @param {String} file
 * @return {Boolean}
 */

function html(file) {
    return /\.html?/.test(extname(file));
}
