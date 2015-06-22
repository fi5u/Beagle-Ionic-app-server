// STATUS CODES
// badurl:  URL not found
// elnf:    Search input element not found
// success: Returned a URL with search string successfully
// badrtn:  Returned URL does not contain the search terms
// ntwerr:  Network errors occurred when accessing the url

var page  = require('webpage').create(),
    args = require('system').args,
    pageTitle,
    response = {},
    timeoutDur = 3000;

var isDebug = false,
    debug = {
        getUrl: {
            true: 'http://www.tfl.gov.uk',
            false: args[1]
        },
        getWord1: {
            true: 'kakku',
            false: args[2]
        },
        getWord2: {
            true: 'kasvis',
            false: args[3]
        }
    };

var urlToTest = debug.getUrl[isDebug];

page.onError = function(msg, trace) {
    var msgStack = ['ERROR: ' + msg];
    if (trace && trace.length) {
        msgStack.push('TRACE:');
        trace.forEach(function(t) {
            msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
        });
    }
    // uncomment to log into the console
    //console.error(msgStack.join('\n'));
};

page.open(urlToTest, function(status) {
    var searchTerms = [debug.getWord1[isDebug], debug.getWord2[isDebug]],
        search = performSearch(searchTerms, 'closestForm');

    if (status !== 'success') {
        phantomExit('Unable to access network', 'badurl');
    }

    pageTitle = page.evaluate(function() {
        return document.title;
    });

    if (!search) {
        buttonClick(searchTerms);
    } else {
        page.sendEvent('keypress', page.event.key.Enter);

        setTimeout(function() {
            // Try the search again by clicking the submit button
            buttonClick(searchTerms);
        }, timeoutDur);
    }

    page.onLoadFinished = function(status) { // Search result loaded
        var newSearch;
        if (status === 'success') {
            if (page.url.indexOf(searchTerms[0]) > -1 && page.url.indexOf(searchTerms[1]) > -1) {
                phantomExit('Query URL successfully returned', 'success');
            } else {
                page.onLoadFinished = function(status) {
                    if (status === 'success') {
                        if (page.url.indexOf(searchTerms[0]) > -1 && page.url.indexOf(searchTerms[1]) > -1) {
                            phantomExit('Query URL successfully returned', 'success');
                        } else {
                            phantomExit('Template could not be generated', 'badrtn');
                        }
                    } else {
                        if (page.url.indexOf(searchTerms[0]) > -1 && page.url.indexOf(searchTerms[1]) > -1) {
                            phantomExit('Query URL successfully returned', 'success');
                        } else {
                            phantomExit('Template could not be generated', 'ntwerr');
                        }
                    }
                };
                // Try the search again by clicking the submit button
                newSearch = performSearch(searchTerms, 'buttonClick');
                if (!newSearch) {
                    phantomExit('Search element not found3', 'elnf');
                }
            }
        } else {
            phantomExit('Network errors occurred', 'ntwerr');
        }
    };

    function buttonClick(searchTerms) {
        var newSearch = performSearch(searchTerms, 'buttonClick');
        if (!newSearch) {
            phantomExit('Search element not found2', 'elnf');
        } else {
            setTimeout(function() {
                if (page.url.indexOf(searchTerms[0]) > -1 && page.url.indexOf(searchTerms[1]) > -1) {
                    phantomExit('Query URL successfully returned', 'success');
                } else {
                    phantomExit('Template could not be generated', 'ntwerr');
                }
            }, timeoutDur);
        }
    }

    function performSearch(searchTerms, submitType) {
        var search = page.evaluate(function(searchTerms, searchSubmitType) {
            var inputs = document.getElementsByTagName('input'),
                searchElement,
                checkElements,
                closestForm,
                submits,
                i,
                closest = function(el, selector) {
                    var matchesFn;
                    // find vendor prefix
                    ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
                        if (typeof document.body[fn] == 'function') {
                            matchesFn = fn;
                            return true;
                        }
                        return false;
                    });
                    // traverse parents
                    while (el!==null) {
                        if (el.parentElement!==null && el.parentElement[matchesFn](selector)) {
                            return el.parentElement;
                        }
                        el = el.parentElement;
                    }
                    return null;
                };

            for (i = 0; i < inputs.length; i++) {
                if (inputs[i].type.toLowerCase() === 'search') {
                    searchElement = inputs[i];
                    break;
                }
                if (
                    (inputs[i].title.toLowerCase().indexOf('search') > -1 ||
                     inputs[i].title.toLowerCase().indexOf('query') > -1 ||
                     inputs[i].id.toLowerCase().indexOf('search') > -1 ||
                     inputs[i].id.toLowerCase().indexOf('query') > -1 ||
                     inputs[i].className.toLowerCase().indexOf('search') > -1 ||
                     inputs[i].className.toLowerCase().indexOf('query') > -1 ||
                     (inputs[i].hasAttribute('name') && inputs[i].getAttribute('name').toLowerCase().indexOf('search') > -1) ||
                     (inputs[i].hasAttribute('name') && inputs[i].getAttribute('name').toLowerCase().indexOf('query') > -1) ||
                     (inputs[i].hasAttribute('placeholder') && inputs[i].getAttribute('placeholder').toLowerCase().indexOf('search') > -1) ||
                     (inputs[i].hasAttribute('placeholder') && inputs[i].getAttribute('placeholder').toLowerCase().indexOf('query') > -1)
                    ) && (inputs[i].type.toLowerCase() === 'text' || !inputs[i].hasAttribute('type'))
                ) {
                    searchElement = inputs[i];
                    break;
                }
            }
            if (!searchElement) {
                // No search found yet, just get the first text box
                searchElement = document.querySelectorAll('input[type="text"]')[0];
            }
            if (!searchElement) {
                // If no text box then grab the first input that doesn't have a type
                checkElements = document.querySelectorAll('input');
                for (var i = checkElements.length - 1; i >= 0; i--) {
                    if (!checkElements[i].hasAttribute('type')) {
                        searchElement = checkElements[i];
                        break;
                    }
                };
            }
            if (searchElement) {
                // Fill in the input element
                searchElement.value = searchTerms[0] + ' ' + searchTerms[1];

                // Submit element's own form
                closestForm = closest(searchElement, 'form');
                if (searchSubmitType === 'closestForm') {
                    // Sometimes this can just return the same page
                    searchElement.focus();
                } else {
                    submits = document.querySelectorAll('[type="submit"]');
                    if (submits.length === 1) {
                        submits[0].click();
                        return true;
                    } else {
                        for (i = 0; i < submits.length; i++) {
                            if (closest(submits[i], 'form') === closestForm) {
                                submits[i].click();
                                return true;
                            }
                        }

                        /* type=image can also be submit button */
                        submits = document.querySelectorAll('[type="image"]');
                        if (submits.length === 1) {
                            submits[0].click();
                            return true;
                        } else {
                            for (i = 0; i < submits.length; i++) {
                                if (closest(submits[i], 'form') === closestForm) {
                                    submits[i].click();
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                }
                return true;
            }
            return false;
        }, searchTerms, submitType);

        return search;
    }
});

function phantomExit(description, code) {
    response.content = {
        status: code,
        url: page.url,
        title: pageTitle
    };

    console.log(JSON.stringify(response.content, undefined, 0));
    phantom.exit(1);
}