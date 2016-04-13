$(function() {
    /**
     * any form with the class 'spinner' should show an overlay with a loader bar
     */
    $('form.spinner').submit(
        function() {
            spinnerOverlayShow();
        }
    );

    /**
     * shows a full screen loading screen to prevent page interaction
     */
    spinnerOverlayShow = function() {
        $('#wrapper').append('<div id="overlay-submit"><div class="loader"></div></div>');
    };  
    
    /**
     * Hides the pseudo label for text and password input fields on focus.
     * Use with:
     *   <input type="text" value="" name="user">
     *   <label class="pseudo-label">Username</label>
     */
    var formSelector = 'input[type=text], input[type=password], textarea';
    
    inputAction = function() {
        $(formSelector).each(function() {
            var plabel = $(this).next('label.pseudo-label');
            var t = $(this);

            plabel.bind("click", function() {
                t.focus();
            });

            t.bind("focus", function() {
                plabel.animate({ opacity: .5 }, 200);
            }).bind("keyup change blur paste", function() {
                checkLabels();
            });
        });

        // We don't have label information right away if we're autocompleting. Wait a moment then check for labels
        setTimeout(checkLabels, 50);
    };

    checkLabels = function() {
        $(formSelector).each(function() {
            plabel = $(this).next('label.pseudo-label');
            if ($(this).val() == "") {
                plabel.show().animate({ opacity: 1 }, 200);
            } else {
                plabel.hide();
            }
        });
    };

    /**
     * Does the same thing as inputAction but with select boxes
     * Make the first <option> blank to add functionality, then add pseudo-class right after </select>
     */
    selectAction = function() {
        $('select').each(function() {
            if ($(this).val() == '') {
                var plabel = $(this).next('label.pseudo-label');
                $(this).change(function() {
                    if (!$(this).val() == '') {
                        plabel.hide();
                    } else {
                        plabel.show();
                    }
                });
            } else {
                $(this).next('label.pseudo-label').hide();
            }
        });
    };

    // initial UX function calls
    inputAction();
    selectAction();
    fileAction();
    bindOverlay();
});

/**
 * Does the same thing as inputAction but with file upload inputs.
 * Usage:
 *   <dt class="upload">
 *       <input type="file" />
 *       <label class="pseudo-label">Choose MAC ID's</label>
 *       <span class="green-button"><span>upload</span></span>
 *       <span class="validation-arrow">&nbsp;</span>
 *   </dt>
 */
fileAction = function() {
    $('input[type=file]').change(function() {
        $(this).next('label.pseudo-label').html($(this).val().replace(new RegExp(/C:\\fakepath\\/g),'')).css('color','#000');
        $(this).parent().find('span.green-button').addClass('selected');
    });
};

/**
 * Shows overlay modal.
 * Add class "overlay-link" to an anchor tag to trigger overlay, use data="overlayID" to specify which content to load
 * Content is AJAX'd from sales-application/views/overlays
 * Add class "close" to any button, link, or div to close the overlay
 * Call the overlay using:
 *     <a class="overlay-link" data="overlayID">click for overlay</a>
 * Overlay content format is:
 *     <div class="overlay" id="overlayID">
 *         <div class="main">
 *             <h1>Overlay Content</h1>
 *             Here is the overlay content
 *         </div>
 *         <div class="CTA">
 *             <button class="grey-button close">okay</button>
 *         </div>
 *     </div>
 */
bindOverlay = function() {
    $('.overlay-link').unbind("click", bindOverlayLink);
    $('.overlay-link').bind("click", bindOverlayLink);
};

bindOverlayLink = function() {
    var data = $(this).attr("data");
    var size = $(this).attr("size");
    var bind = $(this).attr("bind");

    var target = "target=" + data + ".phtml";
    
    if ($(this).attr("param")) {
        target = target + '&param=' + $(this).attr("param");
    }
    
    if ($('#overlay-scrim').length) {
        $('#overlay-scrim').remove();
        $("#overlay").remove();
    }
    
    if (size == 'large') {
        $('body').append('<div id="overlay-scrim">','<div id="overlay"><div class="overlay large"></div></div>');
    } else {
        $('body').append('<div id="overlay-scrim">','<div id="overlay"><div class="overlay"></div></div>');
    }

    $('#overlay .overlay').empty().append('<div class="loading">');
    
    $.ajax({
        type:    'POST',
        url:     '/ajax/content',
        timeout: 10000,
        data:    target,
        success: function(d,s){
            $('#overlay .overlay').empty().append(d, '<div id="close" class="close">x</div>');
            $('#overlay .overlay .close').bind('click', function() {
                $("#overlay-scrim, #overlay .overlay").hide();
            });
            
            if (bind == "true") {
                bindActions();
            }
        },
        error: function(o,s,e){
            $('#overlay .overlay').empty().append('<div class="main">We are unable to process your request at this time. Please try again.</div>', '<div id="close" class="close">x</div>');
            $('#overlay .overlay .close').bind('click', function() {
                $("#overlay-scrim, #overlay .overlay").hide();
            });
        }
    });
    var y = $(window).scrollTop() + 100;
    $("#overlay").css({top: y});
    $("#overlay-scrim, #overlay .overlay").show();
}