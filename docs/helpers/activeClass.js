var Handlebars = require('handlebars');

module.exports = function (file, context){
    if(context.data.root.file !== file) {
        return '';
    }

    return new Handlebars.SafeString('class="active"');
}
