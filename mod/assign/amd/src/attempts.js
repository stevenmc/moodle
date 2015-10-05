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
        PENALTYITEMVALUE: "li.attemptpenalty input[type='text'] .penaltyvalue",
        ATTEMPTSREOPENED:"#id_attemptreopenmethod",
        MAXATTEMPTS:"#id_maxattempts"
    };
    /**
     * Holds the actual values
     */
    var penalties = [];

    var handleRemove = function(e) {
        window.console.log("Removing Penalty ");
        window.console.log(e);

        var penaltyItemTarget = $(e.target).parent().parent();
        window.console.log(penaltyItemTarget);
        penaltyItemTarget.remove();
        var cExistingPenalties = $(SELECTORS.PENALTIES_GRID).children().size();
        window.console.log(cExistingPenalties);
        if (cExistingPenalties === 0) {
            window.console.log("No Penalties Remain in effect");
            var newItem = templates.render('mod_assign/nopenaltiesitem');
            newItem.done(function(source) {
                $(SELECTORS.PENALTIES_GRID).empty();
                $(SELECTORS.PENALTIES_GRID).append(source);
            });
        }
        checkPenaltyState();
    };

    var handleAdd = function() {
        window.console.log("Adding penalty");
        if (!checkPenaltyState()) {
            window.console.log("Cannot add new penalty");
            return;
        }
        var cExistingPenalties = $(SELECTORS.PENALTYITEM).size();
        window.console.log(cExistingPenalties + " already");
        var newItemData = {
            penalty: '0'
        };
        var newItem = templates.render('mod_assign/attemptpenalty', newItemData);
        newItem.done(function(source) {
            //check if empty
            if (cExistingPenalties === 0) {
                $(SELECTORS.PENALTIES_GRID).empty();
            }
            $(SELECTORS.PENALTIES_GRID).append(
                source
            );
            //store the value in the data
            checkPenaltyState();
        });
        newItem.fail(function() {
        });
    };

    var updatePenaltyValue = function(e) {
        window.console.log("Penalty Value Changed");
        window.console.log(e);
    }

    var checkPenaltyState = function() {
        window.console.log("Checking state of Penalties");
        if(!allowAdd()) {
            $(SELECTORS.ADDPENALTY).addClass('disabled');
            return false;
        } else {
            $(SELECTORS.ADDPENALTY).removeClass('disabled');
            return true;
        }
    };

    var attemptsReopenedChanged = function(e) {
        window.console.log("Attempts Re-opened Changed");
        window.console.log(e);
        checkPenaltyState();
    };
    var maxAttemptsChanged = function(e) {
        window.console.log("Max Attempts Changed");
        window.console.log(e);
        checkPenaltyState();

    };
    /**
     * Check if the add button should still be enabled or not
     */
    var allowAdd = function() {
        var attemptsReopened = $(SELECTORS.ATTEMPTSREOPENED +" option:selected").val();
        window.console.log(attemptsReopened);
        if (attemptsReopened == 'none') {
            return false;
        }
        var cExistingPenalties = $(SELECTORS.PENALTYITEM).size();
        var maxAttempts = $(SELECTORS.MAXATTEMPTS + " option:selected").val();
        window.console.log(maxAttempts);
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
            window.console.log("hello");
            var body = $('body');
            this.field = $(SELECTORS.FIELD);
            window.console.log(this.field);
            this.field.hide();
            var mainDivPromise = templates.render('mod_assign/attemptpenalties',[]);
            var me = this;

            mainDivPromise.done(function(source) {
                window.console.log(source);
                me.field.after(source);
                me.mainDiv = $(SELECTORS.PENALTIES_GRID);

                var value = me.field.val();
                window.console.log('Penalties values'+ value);
                var data = null;
                if (value !== '') {
                    try {
                        data = $.parseJSON(value);
                    }
                    catch(x) {
                        me.field.val('');
                    }
                }
                checkPenaltyState();
                //body.delegate(SELECTORS.REMOVEPENALTY, 'click', handleRemove);
                body.on('click', SELECTORS.REMOVEPENALTY, handleRemove);
                //body.delegate(SELECTORS.ADDPENALTY, 'click', handleAdd);
                body.on('click', SELECTORS.ADDPENALTY, handleAdd);
                //body.delegate(SELECTORS.ATTEMPTSREOPENED,'change', attemptsReopenedChanged);
                body.on('change', SELECTORS.ATTEMPTSREOPENED, attemptsReopenedChanged);
                //body.delegate(SELECTORS.MAXATTEMPTS,'change', maxAttemptsChanged);
                body.on('change', SELECTORS.MAXATTEMPTS, maxAttemptsChanged);

                body.on('change', SELECTORS.PENALTYITEMVALUE, updatePenaltyValue);
            });
        }
    };
});
