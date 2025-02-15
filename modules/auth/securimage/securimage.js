/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/*!
 * Securimage CAPTCHA Audio Library
 * https://www.phpcaptcha.org/
 *
 * Copyright 2015 phpcaptcha.org
 * Released under the BSD-3 license
 * See https://github.com/dapphp/securimage/blob/master/README.md
 */

function securimageRefreshCaptcha(captcha_image, captcha_audio)
{
    var captchaId = '';
    var chars     = "abcdefghijklmnopqrstuvwxyz0123456789";

    for (var i = 0; i < 40; ++i) {
        captchaId += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    document.getElementById(captcha_image + '_captcha_id').value = captchaId;
    document.getElementById(captcha_image).src = document.getElementById(captcha_image).src.replace(/([?|&])id=[a-zA-Z0-9]+/, '$1id=' + captchaId);

    if (captcha_audio != '' && typeof window[captcha_audio] == 'object') {
        window[captcha_audio].refresh(captchaId);
    }
}

var SecurimageAudio = function(options) {
    this.html5Support    = true;
    this.captchaId       = null;
    this.playing         = false;
    this.reload          = false;
    this.audioElement    = null;
    this.controlsElement = null;
    this.playButton      = null;
    this.playButtonImage = null;
    this.loadingImage    = null;

    if (options.audioElement) {
        this.audioElement = document.getElementById(options.audioElement);
    }
    if (options.controlsElement) {
        this.controlsElement = document.getElementById(options.controlsElement);
    }

    this.init();
}

SecurimageAudio.prototype.init = function() {
    var ua    = navigator.userAgent.toLowerCase();
    var ieVer = (ua.indexOf('msie') != -1) ? parseInt(ua.split('msie')[1]) : false;
    // ie 11+ detection
    if (!ieVer && null != (ieVer = ua.match(/trident\/.*rv:(\d+\.\d+)/))) {
        ieVer = parseInt(ieVer[1]);
    }

    if (ieVer) {
        if (ieVer < 9) {
            // no html5 audio support, hide player controls
            this.controlsElement.style.display = 'none';
            this.html5Support = false;
            return ;
        } else if ('' == this.audioElement.canPlayType('audio/wav')) {
            // check for mpeg <source> tag - if not found then fallback to flash
            var sources    = this.audioElement.getElementsByTagName('source');
            var mp3support = false;
            var type;

            for (var i = 0; i < sources.length; ++i) {
                type = sources[i].attributes["type"].value;
                if (type.toLowerCase().indexOf('mpeg') >= 0 || type.toLowerCase().indexOf('mp3') >= 0) {
                    mp3support = true;
                    break;
                }
            }

            if (false == mp3support) {
                // browser supports <audio> but does not support WAV audio
                this.html5Support = false;

                this.audioElement.parentNode.removeChild(this.audioElement);
                this.controlsElement.parentNode.removeChild(this.controlsElement);

                return ;
            }
        }
    }

    this.audioElement.addEventListener('playing', this.updateControls.bind(this), false);
    this.audioElement.addEventListener('ended',   this.audioStopped.bind(this), false);

    // find the element used as the play button and register click event to play/stop audio
    var children = this.controlsElement.getElementsByTagName('*');
    for (var i = 0; i < children.length; ++i) {
        var el = children[i];
        if (undefined != el.className) {
            if (el.className.indexOf('play_button') >= 0) {
                this.playButton = el;
                el.addEventListener('click', this.play.bind(this), false);
            } else if (el.className.indexOf('play_image') >= 0) {
                this.playButtonImage = el;
            } else if (el.className.indexOf('loading_image') >= 0) {
                this.loadingImage = el;
            }
        }
    }
}

SecurimageAudio.prototype.play = function(evt) {
    if (null != this.playButton) {
        this.playButton.blur();
    }

    if (this.reload) {
        this.replaceElements();
        this.reload = false;
    }

    try {
        if (!this.playing) {
            if (this.playButtonImage != null) {
                this.playButtonImage.style.display = 'none';
            }
            if (this.loadingImage != null) {
                this.loadingImage.style.display = '';
            }
            //TODO: FIX, most likely browser doesn't support audio type
            this.audioElement.onerror = this.audioError;
            try {
                this.audioElement.play();
            } catch(ex) {
                alert('Audio error: ' + ex);
            }
        } else {
            this.audioElement.pause();
            if (this.loadingImage != null) {
                this.loadingImage.style.display = 'none';
            }
            if (this.playButtonImage != null) {
                this.playButtonImage.style.display = '';
            }
            this.playing = false;
        }
    } catch (ex) {
        alert('Audio error: ' + ex);
    }

    if (undefined !== evt) {
        evt.preventDefault();
    }
    return false;
}

SecurimageAudio.prototype.refresh = function(captchaId) {
    if (!this.html5Support) {
        return;
    }

    if (undefined !== captchaId) {
        this.captchaId = captchaId;
    }

    this.playing = true;
    this.reload  = false;
    this.play(); // stops audio if playing
    this.reload  = true;

    return false;
}

SecurimageAudio.prototype.copyElementAttributes = function(newEl, el) {
    for (var i = 0, atts = el.attributes, n = atts.length; i < n; ++i) {
        newEl.setAttribute(atts[i].nodeName, atts[i].value);
    }

    return newEl;
}

SecurimageAudio.prototype.replaceElements = function() {
    var parent = this.audioElement.parentNode;
    parent.removeChild(this.audioElement);

    var newAudioEl = document.createElement('audio');
    newAudioEl.setAttribute('style', 'display: none;');
    newAudioEl.setAttribute('preload', 'false');
    newAudioEl.setAttribute('id', this.audioElement.id);

    for (var c = 0; c < this.audioElement.children.length; ++c) {
        if (this.audioElement.children[c].tagName.toLowerCase() != 'source') continue;
        var sourceEl = document.createElement('source');
        this.copyElementAttributes(sourceEl, this.audioElement.children[c]);
        var cid = this.captchaId;
        sourceEl.src = sourceEl.src.replace(/([?|&])id=[a-zA-Z0-9]+/, '$1id=' + cid);
        newAudioEl.appendChild(sourceEl);
    }

    this.audioElement = null;
    this.audioElement = newAudioEl;
    parent.appendChild(this.audioElement);

    this.audioElement.addEventListener('playing', this.updateControls.bind(this), false);
    this.audioElement.addEventListener('ended',   this.audioStopped.bind(this), false);
}

SecurimageAudio.prototype.updateControls = function() {
    this.playing = true;
    if (this.loadingImage != null) {
        this.loadingImage.style.display = 'none';
    }
    if (this.playButtonImage != null) {
        this.playButtonImage.style.display = '';
    }
}

SecurimageAudio.prototype.audioStopped = function() {
    this.playing = false;
}

SecurimageAudio.prototype.audioError = function(err) {
    var msg = null;
    switch(err.target.error.code) {
        case err.target.error.MEDIA_ERR_ABORTED:
            break;
        case err.target.error.MEDIA_ERR_NETWORK:
            msg = 'A network error caused the audio download to fail.';
            break;
        case err.target.error.MEDIA_ERR_DECODE:
            alert('An error occurred while decoding the audio');
            break;
        case err.target.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
            alert('The audio format is not supported by your browser.');
            break;
        default:
            alert('An unknown error occurred trying to play the audio.');
            break;
    }
    if (msg) {
        alert('Audio playback error: ' + msg);
    }
}
