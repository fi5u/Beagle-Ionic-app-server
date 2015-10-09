var sys = require('system'),
    page = require('webpage').create(),
    redirectedUrl = '',
    url = {
        original: 'http://www.espn.com',
        redirected: ''
    },
    terms = 'abc def',
    stage = {
        redirectCheck: false,
        searchSent: false,
        resultsReturned: false
    },
    searchMethod = 'formSubmit';

// Set useragent to iphone 6
page.settings.userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A366 Safari/600.1.4';

function stripLastSlash(url) {
    return url.replace(/\/$/, '');
}

// Need to check jquery on every load - subsequent loads will not contain included jquery
function checkJquery() {
    if (hasJquery()) {
        console.log('has jquery');
        afterJquerySetup();
    } else {
        console.log('not has jquery, including it now');

        page.includeJs('http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', function() {
            afterJquerySetup();
        });
    }
}

function hasJquery() {
    var hasJquery = page.evaluate(function() {
        return typeof jQuery !== 'undefined';
    });
    return hasJquery;
}

function afterJquerySetup() {
    console.log('jQuery all setup');
    sendSearchRequest();
}

function sendSearchRequest() {
    var sendSearchResults;
    // jQuery is available
    console.log('getting first input');
    var sendSearch = page.evaluate(function(terms) {
        var getEl,
            getElNoForm,
            $el;

        // Get search box element
        function getElement(parentEl) {
            var textEl;

            if (jQuery(parentEl + 'input[type="search"]').length) {
                return {type: 'success', el: jQuery(parentEl + 'input[type="search"]').first()}
            } else if (jQuery(parentEl + 'input[type="text"]').length) {
                jQuery(parentEl + 'input[type="text"]').each(function() {
                    var $self = jQuery(this);
                    if (
                        ($self.is('[title]') && ($self.attr('title').toLowerCase().indexOf('search') > -1 || $self.attr('title').toLowerCase().indexOf('query') > -1)) ||
                        ($self.is('[id]') && ($self.attr('id').toLowerCase().indexOf('search') > -1 || $self.attr('id').toLowerCase().indexOf('query') > -1)) ||
                        ($self.is('[name]') && ($self.attr('name').toLowerCase().indexOf('search') > -1 || $self.attr('name').toLowerCase().indexOf('query') > -1)) ||
                        ($self.is('[placeholder]') && ($self.attr('placeholder').toLowerCase().indexOf('search') > -1 || $self.attr('placeholder').toLowerCase().indexOf('query') > -1)) ||
                        $self.is('[class*=search]') || $self.is('[class*=Search]') || $self.is('[class*=SEARCH]') ||
                        $self.is('[class*=query]') || $self.is('[class*=Query]') || $self.is('[class*=QUERY]')
                    ) {
                        textEl = $self;
                        return false; // exit from the .each loop
                    }
                });

                if (textEl) {
                    return {type: 'success', el: textEl}
                }
                return {type: 'success', el: jQuery(parentEl + 'input[type="text"]').first()}
            } else if (jQuery(parentEl + 'input').not('[type]').length) {
                return {type: 'success', el: jQuery(parentEl + 'input').not('[type]').first()}
            } else {
                return {type: 'fail', status: 'elnf'};
            }
        }

        // Get an element with form as parent
        getEl = getElement('form ');
        if (getEl.type === 'success') {
            $el = getEl.el;
            $el.val(terms);
            $el.closest('form').submit();
        } else {
            // Try to get element without a form parent
            getElNoForm = getElement('');
            if (getEl.type === 'success') {
                $el = getElNoForm.el;
                $el.val(terms);

                // Find submit button
                if ($('input[type="submit"]').length) {
                    if ($('input[type="submit"]').length === 1) {
                        $('input[type="submit"]').first().click();
                        return true;
                    } else {
                        // If next element is input type submit
                        if ($el.next('input[type="submit"]').length) {
                            $el.next('input[type="submit"]').click();
                            return true;
                        } else {
                            // Click the first input
                            $('input[type="submit"]').first().click();
                            return true;
                        }
                    }
                } else {
                    return JSON.stringify({type: 'fail', status: 'btnnf'});
                }
            } else {
                return JSON.stringify(getElNoForm);
            }
        }

        return true;
    }, terms);

    if (sendSearch !== true) {
        sendSearchResults = JSON.parse(sendSearch);
        phantomExit(sendSearchResults.status);
    }

    stage.searchSent = true;
}

function urlContainsSearchResults() {
    if (page.url.indexOf('abc') > -1 && page.url.indexOf('def') > -1) {
        return true;
    }
    return false;
}

function phantomExit(statusCode) {
    var response = {
        status: statusCode,
        url: page.url,
        title: page.title,
        redirectedUrl: url.redirected
    };

    console.log(JSON.stringify(response, undefined, 0));
    phantom.exit(1);
}


////////////////////////////////////////////////////////////////////////////////


page.onLoadFinished = function() {
    console.log('page loaded');

    if (stage.resultsReturned) {
        if (urlContainsSearchResults()) {
            phantomExit('success');
            return false;
        } else {
            console.log('returned url does not contain search string, waiting for another page load');
            stage.resultsReturned = false;
        }
    }

    if (!stage.redirectCheck) {
        console.log('attempting to redirect before 1.5 sec - exiting');
        return false;
    } else {
        console.log('redirect has been checked');
        url.redirected = page.url;
        if (stripLastSlash(url.redirected) !== stripLastSlash(url.original)) {
            console.log('redirect has occurred: url = ' + url.redirected);
        }
    }

    checkJquery();

    if (stage.searchSent) {
        console.log('search results returned');
        console.log(page.url);
        stage.resultsReturned = true;
    }

};


page.onError = function(msg, trace) {
    var msgStack = ['ERROR: ' + msg];
    if (trace && trace.length) {
        msgStack.push('TRACE:');
        trace.forEach(function(t) {
            msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
        });
    }
    // uncomment to log into the console
    console.error(msgStack.join('\n'));
};

////////////////////////////////////////////////////////////////////////////////

page.open(url.original, function(status) {
    if (status !== 'success') {
        console.log(status);
        phantomExit('ntwerr');
    }

    // Wait for any redirects
    setTimeout(function() {
        stage.redirectCheck = true;
        page.onLoadFinished();
    }, 1500);

    // Wait 10s and quit if still going
    setTimeout(function() {
        if (urlContainsSearchResults()) {
            phantomExit('success');
        } else {
            // console.log('formSubmit method did not work, trying the click button method');
            // if (searchMethod === 'formSubmit') {

            //     // Try search again by clicking button
            //     searchMethod = 'btnClick';

            //     // Reset the checks
            //     stage.searchSent = false;
            //     stage.resultsReturned = false;

            //     // If redirect took place, use that url, as we're not waiting for a redirect this time
            //     var urlToVisit = url.redirected !== '' ? url.redirected : url.original;
            //     page.open(urlToVisit, function(status) {
            //         console.log('page opened for the second time');
            //         if (status !== 'success') {
            //             phantomExit('ntwerr');
            //         }

            //         setTimeout(function() {
            //             if (urlContainsSearchResults()) {
            //                 phantomExit('success');
            //             } else {
            //                 phantomExit('timedout');
            //             }
            //         }, 10000);
            //     });
            // } else {
                phantomExit('timedout');
            // }
        }
    }, 10000);
});
