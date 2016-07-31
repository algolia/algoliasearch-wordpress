var Handlebars = require('handlebars');

module.exports = function (file, context){

    var classes = ['ac-nav-menu-list-item'];

    var isDocsPage = file === 'docs'
        && context.data.root.file !== 'frequently-asked-questions.md'
        && context.data.root.file !== 'index.md'
    
    if(isDocsPage || context.data.root.file === file) {
        classes.push('active');
    }

    classes = classes.join(' ');

    return new Handlebars.SafeString('class="' + classes + '"');
}
