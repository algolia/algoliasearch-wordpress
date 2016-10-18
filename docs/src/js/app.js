

 window.onload = function(){
  if ( navigator.userAgent.indexOf('Safari') != -1 ) {
    document.body.classList.add('safari')
  }
 }

$(function () {

    var ACTIONS = {
        PREV: 'PREV',
        NEXT: 'NEXT'
    };
    var $prevBtn = $('.press__tweet__arrow--prev');
    var $nextBtn = $('.press__tweet__arrow--next');
    var $tweets = $('.press__tweet');
    var $tweetsContainer = $('.press__tweets');

    var currentIndex = Math.floor($tweets.length / 2);
    function setCurrentTweet(action) {
        switch (action) {
        case ACTIONS.PREV:
            if (currentIndex <= $tweets.length) {
                currentIndex--;
            }
            break;
        case ACTIONS.NEXT:
            if (currentIndex >= 0) {
                currentIndex++;
            }
            break;
        default:
            currentIndex = action;
        }
        centerTweets(action);
    }
    function centerTweets() {
        var action = arguments.length <= 0 || arguments[0] === undefined ? ACTIONS.PREV : arguments[0];
        var $centerElement = $($tweets[currentIndex]);
        var tweetSize = $(_.first($tweets)).width();
        var _$centerElement$posit = $centerElement.position();
        var left = _$centerElement$posit.left;
        var containerSize = $tweetsContainer.width();
        var gap = action === ACTIONS.NEXT ? tweetSize / 3 : tweetSize - 325;
        var offset = -(containerSize / 2) + left + gap;
        $tweets.removeClass('press__tweet--current');
        $centerElement.addClass('press__tweet--current');
        removeClickableTweets();
        $(_.first($tweetsContainer)).css('transform', 'translateX(' + (offset > 0 ? '-' + offset : 0) + 'px)');
    }
    function loadTweets() {
        var promises = [];
        $tweets.each(function (index, tweet) {
            var $tweet = $(tweet);
            var tweetId = $tweet.attr('data-tweet-id');
            promises.push(twttr.widgets.createTweet(tweetId, tweet, {
                cards: 'hidden',
                conversation: 'none'
            }));
        });
        return promises;
    }
    function removeLoadingClass() {
        $tweetsContainer.removeClass('press__tweets--loading');
    }
    function bindEvents() {
        $prevBtn.on('click', _.partial(setCurrentTweet, ACTIONS.PREV));
        $nextBtn.on('click', _.partial(setCurrentTweet, ACTIONS.NEXT));
        $(window).on('resize', _.debounce(centerTweets, 100));
    }

    function showTab(e) {
        window.location.hash = e.target.hash;
        window.scrollTo(0, 0);
        centerTweets();
    }

    function removeClickableTweets() {
        $tweets.find('iframe').contents().find('.EmbeddedTweet').removeClass('js-clickToOpenTarget');
        $tweets.nextAll('.press__tweet--current').find('iframe').contents().find('.EmbeddedTweet').addClass('js-clickToOpenTarget');
    }
    twttr.ready(function () {
        Promise.all(loadTweets()).then(removeLoadingClass).then(centerTweets).then(removeClickableTweets);
    });
    bindEvents();
});