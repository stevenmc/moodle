// Standard license block omitted.
/*
 * @package    mod_assign
 * @copyright  2015 Michael Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module mod_assign/attempts
  */

define(['jquery', 'core/templates'], function($, templates) {

    var SELECTORS = {
        FIELD: "#id_attemptpenalties",
        MAINDIV: "",
        PENALTIES_GRID: "#mod_assign_form_attemptpenalties",
        ADDPENALTY: "div.addpenalty",
        REMOVEPENALTY: "div.removepenalty",
        PENALTYITEM:"li.attemptpenalty",
        PENALTYITEMVALUE: "input[type='text'].penaltyvalue",
        ATTEMPTSREOPENED:"#id_attemptreopenmethod",
        MAXATTEMPTS:"#id_maxattempts"
    };
    /*
     * Holds the actual values
     */
    var penalties = [];

    var handleRemove = function(e) {
        var penaltyItemTarget = $(e.target).parent().parent();
        penaltyItemTarget.remove();
        handleSubmission();
        var cExistingPenalties = $(SELECTORS.PENALTIES_GRID).children().length();
        if (cExistingPenalties === 0) {
            var newItem = templates.render('mod_assign/nopenaltiesitem');
            newItem.done(function(source) {
                $(SELECTORS.PENALTIES_GRID).empty();
                $(SELECTORS.PENALTIES_GRID).append(source);
            });
        }
        checkPenaltyState();
    };

    var handleAdd = function() {
        if (!checkPenaltyState()) {
            return;
        }
        var cExistingPenalties = $(SELECTORS.PENALTYITEM).length;
        var newItemData = {
            penalty: '0'
        };
        var newItem = templates.render('mod_assign/attemptpenalty', newItemData);
        newItem.done(function(source) {
            if (cExistingPenalties === 0) {
                $(SELECTORS.PENALTIES_GRID).empty();
            }
            $(SELECTORS.PENALTIES_GRID).append(
                source
            );
            // Store the value in the data.
            handleSubmission();
            checkPenaltyState();
        });
        newItem.fail(function() {
            handleSubmission();
        });
    };

    var handleSubmission = function() {
        var datafield = $(SELECTORS.FIELD);
        var existingPenalties = $(SELECTORS.PENALTYITEMVALUE);
        //$(SELECTORS.PENALTIES_GRID).children().length;
        var cExistingPenalties = $(SELECTORS.PENALTYITEMVALUE).length;
        var penalties = [];
        if (cExistingPenalties == 0 ) {
            
            datafield.val(JSON.stringify(penalties));
        } else {
            var i = 0;
            var isValid = true
            $('.penaltyerror').remove();
            existingPenalties.each(function(index, value) {
                penaltyElement = $(value);
                penaltyIsValid = true;
                penalty = parseInt(penaltyElement.val());
                if (isNaN(penalty)){
                    penaltyIsValid = false;
                    penaltyElement.after("<span class='penaltyerror'>Must between 0 - 100</span>");
                } else {
                    if (penalty < 0) {
                        penaltyIsValid = false;
                        penaltyElement.after("<span class='penaltyerror'>Must be > 0</span>");
                    } else if (penalty > 100) {
                        penaltyIsValid = false;
                        penaltyElement.after("<span class='penaltyerror'>Must be <= 100</span>");
                    }
                }
                
                
                isValid = isValid & penaltyIsValid;
                if (isValid) {
                    penalties.push(penalty);
                }
                if (i+1 == cExistingPenalties & isValid) {
                    datafield.val(JSON.stringify(penalties));
                }
                i++;
            });
        }
    }


    var checkPenaltyState = function() {
        if(!allowAdd()) {
            $(SELECTORS.ADDPENALTY).addClass('disabled');
            return false;
        } else {
            $(SELECTORS.ADDPENALTY).removeClass('disabled');
            return true;
        }
    };

    /**
     * Check if the add button should still be enabled or not
     */
    var allowAdd = function() {
        var attemptsReopened = $(SELECTORS.ATTEMPTSREOPENED + " option:selected").val();
        if (attemptsReopened == 'none') {
            return false;
        }
        var cExistingPenalties = $(SELECTORS.PENALTYITEM).length;
        var maxAttempts = $(SELECTORS.MAXATTEMPTS + " option:selected").val();
        if (maxAttempts == -1) {
            return true;
        } else {
            if (cExistingPenalties < maxAttempts) {
                return true;
            }
            if (cExistingPenalties > maxAttempts) {
                window.console.log("TODO Remove penalties beyond the end of the number of attempts");

            }
        }
        return false;
    };

    return {
        initialize : function(){
            var body = $('body');
            this.field = $(SELECTORS.FIELD);
            this.field.hide();
            var mainDivPromise = templates.render('mod_assign/attemptpenalties',[]);
            var me = this;

            mainDivPromise.done(function(source) {
                me.field.after(source);
                me.mainDiv = $(SELECTORS.PENALTIES_GRID);

                var value = me.field.val();
                var data = null;
                if (value !== '') {
                    try {
                        data = $.parseJSON(value);
                    }
                    catch(x) {
                        me.field.val('');
                    }
                    var cExistingPenalties = $(SELECTORS.PENALTYITEM).length;
                    var addPenaltyFunction = function(source) {
                        $(SELECTORS.PENALTIES_GRID).append(
                            source
                        );
                        checkPenaltyState();
                    };
                    $(SELECTORS.PENALTIES_GRID).empty();
                    for(var i = 0; i < data.length; i++) {
                        var newItemData = {
                            penalty: data[i]
                        };
                        var newItem = templates.render('mod_assign/attemptpenalty', newItemData);
                        newItem.done(addPenaltyFunction);
                    }
                }
                checkPenaltyState();

                body.on('click', SELECTORS.REMOVEPENALTY, handleRemove);

                body.on('click', SELECTORS.ADDPENALTY, handleAdd);

                body.on('change', SELECTORS.ATTEMPTSREOPENED, checkPenaltyState);

                body.on('change', SELECTORS.MAXATTEMPTS, checkPenaltyState);

                $(SELECTORS.PENALTIES_GRID).on('change', SELECTORS.PENALTYITEMVALUE, function() {
                    handleSubmission();
                    checkPenaltyState();
                    }
                );
                //body.on('change', SELECTORS.PENALTYITEMVALUE, checkPenaltyState);
            });
        }
    };
});
