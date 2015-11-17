var getFacetWidget = function (facet, templates) {
    if (facet.type === 'conjunctive') {
        return algoliaBundle.instantsearch.widgets.refinementList({
            container: facet.wrapper.appendChild(document.createElement('div')),
            facetName: facet.name,
            templates: templates,
            operator: 'and'
        });
    }

    if (facet.type === 'disjunctive') {
        return algoliaBundle.instantsearch.widgets.refinementList({
            container: facet.wrapper.appendChild(document.createElement('div')),
            facetName: facet.name,
            templates: templates,
            operator: 'or'
        });
    }

    if (facet.type == 'slider') {
        delete(templates.item);
        return algoliaBundle.instantsearch.widgets.rangeSlider({
            container: facet.wrapper.appendChild(document.createElement('div')),
            facetName: facet.name,
            templates: templates
        });
    }
};

function transformHit($, hit) {

    var fields = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'text'];

    if (hit.post_type != 'page' && hit.post_type != 'post')
        return hit;

    /**
     * Reconstructing the content attribute
     * that has been split at indexing time for better relevance
     */
    var content_matches = {};

    var noHighlights = false;

    var highlight_hit = hit._highlightResult;

    for (var i = 0; i < fields.length; i++)
    {
        if (highlight_hit.post_content[fields[i]] != undefined)
        {
            for (var j = 0; j < highlight_hit.post_content[fields[i]].length; j++)
            {
                for (var k = 0; k < highlight_hit.post_content[fields[i]][j].value.matchedWords.length; k++)
                {
                    if (content_matches[highlight_hit.post_content[fields[i]][j].value.matchedWords[k]] == undefined)
                    {
                        content_matches[highlight_hit.post_content[fields[i]][j].value.matchedWords[k]] = {i: i, type: fields[i], order: highlight_hit.post_content[fields[i]][j].order, count : highlight_hit.post_content[fields[i]][j].value.matchedWords.length, value: highlight_hit.post_content[fields[i]][j].value.value};
                    }
                    else
                    {
                        if (i == content_matches[highlight_hit.post_content[fields[i]][j].value.matchedWords[k]].i
                            && highlight_hit.post_content[fields[i]][j].value.matchedWords.length > content_matches[highlight_hit.post_content[fields[i]][j].value.matchedWords[k]].count)
                        {
                            content_matches[highlight_hit.post_content[fields[i]][j].value.matchedWords[k]] = {i: i, type: fields[i], order: highlight_hit.post_content[fields[i]][j].order, count : highlight_hit.post_content[fields[i]][j].value.matchedWords.length, value: highlight_hit.post_content[fields[i]][j].value.value};
                        }
                    }
                }
            }
        }
    }

    content_matches = $.map(content_matches, function(value, index) {
        return [value];
    });

    if (content_matches.length == 0)
    {
        noHighlights = true;

        for (var i = 0; i < fields.length; i++)
        {
            if (hit.post_content[fields[i]] != undefined)
            {
                for (var j = 0; j < hit.post_content[fields[i]].length; j++)
                {
                    hit.post_content[fields[i]][j].type = fields[i];
                    content_matches.push(hit.post_content[fields[i]][j]);
                }
            }
        }
    }

    content_matches.sort(function (a, b) {
        if (a.order < b.order)
            return -1;
        return 1;
    });

    if (noHighlights === true)
    {
        for (var i = 0; i < content_matches.length; i++)
        {
            if (content_matches[i].type == 'text')
            {
                content_matches = content_matches.slice(0, i + 1);

                if (i === content_matches.length - 1)
                    noHighlights = false;

                break;
            }
        }
    }

    if (hit._highlightResult == undefined)
        hit._highlightResult = {};

    if (hit._highlightResult.post_content == undefined)
        hit._highlightResult.post_content = {};

    hit._highlightResult.post_content.value = "";

    var separator = "<div>[...]</div>";
    var old_order = -1;
    for (i = 0; i < content_matches.length; i++)
    {
        if (old_order != content_matches[i].order)
        {
            old_order = content_matches[i].order;

            var balise = content_matches[i].type != "text" ? content_matches[i].type : "div";

            if (i == 0 && content_matches[i].order > 0)
                hit._highlightResult.post_content.value += separator;

            hit._highlightResult.post_content.value += "<div>";
            hit._highlightResult.post_content.value += "<" + balise + '>';
            hit._highlightResult.post_content.value += content_matches[i].value;
            hit._highlightResult.post_content.value += "</" + balise + '>';
            hit._highlightResult.post_content.value += "</div>";

            hit._highlightResult.post_content.value += separator;
        }
    }

    return hit;
}
