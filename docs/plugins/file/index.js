'use strict';

/**
 * Expose `plugin`.
 */
module.exports = plugin;

/**
 * Metalsmith plugin that sets a `file` variable on each file's metadata.
 * This allows you to find the orginal file name for url generation.
 *
 * @return {Function}
 */
function plugin() {
    return function (files, metalsmith, done) {
        Object.keys(files).forEach(function (file) {
            setImmediate(done);
            files[file].file = file;
        });
    };
}

