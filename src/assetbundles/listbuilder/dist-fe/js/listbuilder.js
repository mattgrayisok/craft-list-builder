/**
 * List Builder plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Matt Gray
 * @copyright Copyright (c) 2018 Matt Gray
 * @link      https://mattgrayisok.com
 * @package   ListBuilder
 * @since     1.0.0
 */

var lbFuncWrapper = function(){
    //Allow popups to be closed
    var closeButtons = document.querySelectorAll('.lb-popup-close');
    closeButtons.forEach(function(form) {
        form.addEventListener("click", function(e) {
            e.preventDefault();
            form.parentNode.parentNode.classList.remove("is-displaying");
        });
    });

    //Exit intent tracking
    var exitIntentPopups = document.querySelectorAll('.lb-exit-intent');
    document.addEventListener('mouseout', function(evt) {
      if (evt.toElement === null && evt.relatedTarget === null && evt.clientY <= 0) {
        exitIntentPopups.forEach(function(popup){
            var shortcode = popup.getAttribute('data-source-code');
            var localStorageName = 'lb-popup-seen-'+shortcode;
            if(localStorage.getItem(localStorageName) !== "true"){
                popup.classList.add('is-displaying');
                localStorage.setItem(localStorageName, "true");
            }
        });
      }
    });

    //Popup delayed display timers
    var allPopups = document.querySelectorAll('.lb-popup');
    allPopups.forEach(function(popup){
        var shortcode = popup.getAttribute('data-source-code');
        var localStorageName = 'lb-popup-seen-'+shortcode;
        var delay = popup.getAttribute('data-show-delay');
        if(delay != '' && !isNaN(delay)){
            setTimeout(function(){
                if(localStorage.getItem(localStorageName) !== "true"){
                    popup.classList.add('is-displaying');
                    localStorage.setItem(localStorageName, "true");
                }
            }, parseInt(delay) * 1000);
        }
    });

    //Make forms submit using ajax where appropriate
    var allForms = document.querySelectorAll('.lb-form[data-ajax-enabled="1"]');
    allForms.forEach(function(form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            form.parentNode.classList.remove("lb-status--success");
            form.parentNode.classList.remove("lb-status--error");
            form.parentNode.classList.add("lb-status--loading");
            ajaxPost(this, function(){
                form.parentNode.classList.remove("lb-status--loading");
                var response = JSON.parse(this.responseText);
                if(response.status == 'success'){
                    submitSuccess(form);
                }else{
                    var errorMsg = "There was a problem submitting your subscription!";
                    if(response.hasOwnProperty('message')){
                        errorMsg = response.message;
                    }
                    submitError(form, errorMsg);
                }
            },
            function(){
                //Error
                form.parentNode.classList.remove("lb-status--loading");
                form.parentNode.classList.add("lb-status--error");
                var errorMsg = "There was a problem submitting your subscription!";
                submitError(form, errorMsg);
            })
        });
    });

    function submitSuccess(form){
        form.parentNode.classList.remove("lb-status--error");
        form.parentNode.classList.add("lb-status--success");
        for (var i = 0, element; el = form.elements[i++];) {
            if(!el.name) continue;
            if(el.type == 'hidden') continue;
            if(el.type == 'checkbox') continue;
            if(el.disabled) continue;
            el.value = "";
        }
    }

    function submitError(form, msg){
        form.parentNode.classList.remove("lb-status--success");
        form.parentNode.classList.add("lb-status--error");
        var errorMsg = form.querySelector('.lb-error-message');
        if(errorMsg){
            errorMsg.textContent = msg;
        }
    }

    function ajaxPost (form, callback, error) {
        var url = form.action,
            xhr = new XMLHttpRequest();
        if(typeof url != 'string'){
            url = form.action.getAttribute('value');
        }

        var params = '';
        for (var i = 0, element; el = form.elements[i++];) {
            if(!el.name) continue;
            if(el.disabled) continue;
            if((el.type == 'checkbox' || el.type == 'radio') && !el.checked) continue;
            params += encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value) + '&';
        }
        params = params.substring(0, params.length - 1);
        xhr.open("POST", url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.onload = callback.bind(xhr);
        xhr.onerror = error.bind(xhr);
        xhr.send(params);
    }
};

lbFuncWrapper();
