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
        PENALTIES_GRID: "#mod_assign_form_attemptpenalties",
        ADDPENALTY: "span.addpenalty",
        REMOVEPENALTY: "span.removepenalty",
        PENALTYITEM:"li.attemptpenalty"
    };

    var handleRemove = function(e) {
        window.console.log("Removing Penalty ");
        window.console.log(e);
        $(e.target).parent().remove();
    };

    var handleAdd = function() {
        window.console.log("Adding penalty");
        var cExistingPenalties = $(SELECTORS.PENALTYITEM).size();
        window.console.log(cExistingPenalties + " already");
        var newItemData = {
            penalty: ''
        };
        var newItem = templates.render('mod_assign/attemptpenalty', newItemData);
        newItem.done(function(source) {
            $(SELECTORS.PENALTIES_GRID).append(
                source
            );
        });
        newItem.fail(function() {

        });
    };

    return {
        initialize : function(){
            window.console.log("hello");
            var body = $('body');
            body.delegate(SELECTORS.REMOVEPENALTY, 'click', handleRemove);
            body.delegate(SELECTORS.ADDPENALTY, 'click', handleAdd);
        }
    };
});
