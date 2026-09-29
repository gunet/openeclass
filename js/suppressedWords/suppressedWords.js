/**
 * Memory cache for suppressed words to avoid repeated version checks.
 */
var cachedSuppressedWords = null;

/**
 * Global helper for retrieving suppressed words with localStorage caching.
 *
 * @param {string} action The action to perform ('words' or 'version').
 * @returns {jQuery.Promise} A promise that resolves with the data.
 */
function getSuppressedWords(action = 'words') {
    if (action === 'words') {
        var deferred = $.Deferred();
        var localVersion = localStorage.getItem('suppressed_words_version');
        var localWords = localStorage.getItem('suppressed_words_data');

        $.getJSON(urlAppend + 'main/ajax_suppressed_words.php', { action: 'version' })
            .done(function(remoteVersion) {
                if (localVersion === remoteVersion && localWords) {
                    deferred.resolve(JSON.parse(localWords));
                } else {
                    $.getJSON(urlAppend + 'main/ajax_suppressed_words.php', { action: 'words' })
                        .done(function(words) {
                            localStorage.setItem('suppressed_words_version', remoteVersion);
                            localStorage.setItem('suppressed_words_data', JSON.stringify(words));
                            deferred.resolve(words);
                        })
                        .fail(function() { deferred.reject(); });
                }
            })
            .fail(function() {
                if (localWords) {
                    deferred.resolve(JSON.parse(localWords));
                } else {
                    deferred.reject();
                }
            });
        return deferred.promise();
    }

    return $.ajax({
        url: urlAppend + 'main/ajax_suppressed_words.php',
        data: { action: action },
        method: 'GET',
        dataType: 'json'
    });
}

/**
 * Applies a blur effect to suppressed words within target elements.
 * Improved to handle Greek accents and case-insensitivity without breaking on Unicode.
 * 
 * @param {Array} words List of words to blur.
 * @param {string} selector The jQuery selector for target elements (defaults to '.card-body').
 */
function applyWordBlur(words, selector = '.card-body') {
    console.log('applyWordBlur');
    if (!words || words.length === 0) return;

    function removeAccents(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
    }

    var accentMap = {
        'α': '[αά]', 'ε': '[εέ]', 'η': '[ηή]', 'ι': '[ιίϊΐ]',
        'ο': '[οό]', 'υ': '[υύϋΰ]', 'ω': '[ωώ]',
        'Α': '[ΑΆ]', 'Ε': '[ΕΈ]', 'Η': '[ΗΉ]', 'Ι': '[ΙΊΪ]',
        'Ο': '[ΟΌ]', 'Υ': '[ΥΎΫ]', 'Ω': '[ΩΏ]'
    };

    var escapedWords = words.map(function(w) {
        var cleanWord = removeAccents(w);
        var escaped = cleanWord.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var pattern = escaped.replace(/[αεηιουωΑΕΗΙΟΥΩ]/g, function(m) {
            return accentMap[m] || m;
        });
        pattern = pattern.replace(/[σς]$/g, '[σς]');
        return pattern;
    });

    var regex = new RegExp('(' + escapedWords.join('|') + ')', 'gi');

    $(selector).each(function() {
        function walk(node) {
            var child, next;
            switch (node.nodeType) {
                case 1:
                case 9:
                case 11:
                    child = node.firstChild;
                    while (child) {
                        next = child.nextSibling;
                        if (!$(child).hasClass('censored-blur')) {
                            walk(child);
                        }
                        child = next;
                    }
                    break;
                case 3:
                    handleText(node);
                    break;
            }
        }

        function handleText(textNode) {
            var val = textNode.nodeValue;
            if (val.match(regex)) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = val.replace(regex, '<span class="censored-blur" data-original="$1">******</span>');
                
                while (tempDiv.firstChild) {
                    textNode.parentNode.insertBefore(tempDiv.firstChild, textNode);
                }
                textNode.parentNode.removeChild(textNode);
            }
        }

        walk(this);
    });
}

/**
 * Automatically fetches suppressed words and applies the blur effect.
 * Uses a memory cache to avoid repeated server calls during infinite scroll.
 * 
 * @param {string} selector The jQuery selector for target elements (defaults to '.card-body').
 */
function initSuppressedWordsBlur(selector = '.card-body') {
    console.log('initSuppressedWordsBlur');
    if (cachedSuppressedWords) {
        applyWordBlur(cachedSuppressedWords, selector);
        return $.Deferred().resolve(cachedSuppressedWords).promise();
    }
    return getSuppressedWords().done(function(data) {
        cachedSuppressedWords = data;
        applyWordBlur(data, selector);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Σφάλμα κατά τη λήψη των suppressed words:', textStatus, errorThrown);
    });
}
