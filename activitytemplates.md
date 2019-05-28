# Enhancement to Activity Chooser.

Allow administrators to save a module configuration as a starting template to create new activities from.

The administrator would simply complete the same configuration form as would be displayed to a teacher adding a new activity.

However when being set up as a template activity each setting would have additional checkbox allowing the admin to specify the configuration setting as “locked” - Teacher cannot change the value when creating an activity from the template.

(This is similar to the way that module defaults can be “locked” in plugin configuration).
This may require some fields, such as date / date time selectors to also display a duration selector that could specify some sort of relative modification that is applied (e.g. +3 days to the default value).

The administrator would be obliged to provide a sensible template name and some details explaining the purpose of the template.

When a teacher goes to add a new activity, any configured “template” activities would appear below their “vanilla” plugin item, with the defined template name displayed. When selected the explanation would be presented in the activity chooser details pane.

If the user double clicks or selects “OK” with the template activity chosen, the standard edit module form would be displayed, with the appropriate form elements configured and modified in line with the template’s values.

Much of the infrastructure already exists to support the activity chooser extension part:
* implementing <modname>_get_shortcuts() and returning a list of elements can add items to the chooser

This would be a case of adding some additional handling to the get_module_metadata() function in /course/lib.php, providing a UI to capture the configuration values and persisting the values of the template.
