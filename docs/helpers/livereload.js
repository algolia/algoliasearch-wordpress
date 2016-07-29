var Handlebars = require('handlebars');

module.exports = function (){
    if(process.env.NODE_ENV === 'production') {
        return '';
    }

    return new Handlebars.SafeString('<script src="http://localhost:35729/livereload.js"></script>');
}
