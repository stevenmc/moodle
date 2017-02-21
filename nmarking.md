# Overview
A proposal to enhance the existing "allocated marker" and "marking workflow" tools to support 'n' number of markings and a process to moderate markings into a final assignment mark.

There are assessment cases where it would be desirable for an assignment to be marked by more than one person. At present, the Moodle assignment tool only supports and reports on a single grade.

This proposes that the assignment module would support the ability for an arbitrary number of gradings, by different users, to be recorded against an assignment, with a final moderation step that would allow a suitably authorized / delegated user to review all of the gradings and determine a “final grade”.

When configuring an assignment module, a teacher would be able to specify:

1. If the marking takes place sequentially or in parallel
2. If the marking rounds are independent or not (i.e. markers may or may not see other marker’s grading response)
3. the number of marking rounds required (1,2,3…n)
4. the default markers:
    * in order that they would mark submissions in a sequential mode or just the eligible markers in parallel this may be left blank, if any user with the grade capability can grade in any round
    * the reconciliation strategy to be taken in resolving a final grade:
        * Automated: Highest mark, lowest mark, average mark
        * Manual: A user must review all gradings and assert a final grade. 

Once an assignment is configured a teacher would be able to use a modified version of the allocated marker screen to override the allocated markers for a given student’s work.

In conjunction with not specifying default markers in the assignment settings, this would offer a workflow that supports the allocation of different markers to different groups of students within an assignment.

If an advanced grading method (such as a rubric) is configured, each marking round would require the marker to complete the configured method.

Once the required number of grading rounds has been completed for a submission, a final result is synthesized by another user (a moderator) or via an automated strategy.

If an advanced grading method is in use or is configured, a manual reconciliation would be performed by the moderator, where all of the grading rounds information is presented to them and they create the final grade response. In the case of advanced grading methods, this may present the grading methods “result” view for each marking round, and a “blank” version for the moderator to record the final verdict. It may be helpful to offer a mechanism that allows the responses from a previous round to be coped in to this version to reduce manual labour.

# Relationship to marking workflow.
It may be feasible to use the marking workflow to manage this process. When an assignment submission is marked as “Not marked”, the strategy is displayed to users.

Once set to “In Marking”, the module would enforce the configured rules regarding the number of marking rounds, order of markers etc.

Once the markers have completed the marking it may be feasible to automatically translate from “In marking” in to the “ready for review”. A moderator then may mark submission as “In Review”, and whilst in this state they are able to review each of the marking rounds, and create the final grade response.

Once they have completed the final grade response they could mark it as “Ready for Release”.

Only upon the transition into “Released” would the final grade response be propagated to the gradebook and the grading details become visible to the student (subject of course the Blind Marking lock out).

It should be noted that it may be feasible to automate the transition between marking workflow states based on the actions of graders & moderator:

* Once the correct number of gradings have been submitted, automatically transition into the “Ready for Review” state.
* The moderator starting opening the Grade UI of a submission that is in the “Ready for Review” (or using a dedicated “Moderate” option) would automatically transition the submission into the “In Review” state
* The moderator submitting the final grade response of a submission in the “In Review” state could transition the submission to either the “ready for release” or “released” state (this should probably be configurable at a server or instance level).

# Summary
Project size: medium
Audience: primary schools, universities, work places
Target users: teachers, administrators

# Goals
The goal of this project would be to support very common models of assessment where there are multiple rounds of marking on an assignment prior to the final synthesis of a grade.

# Use cases
* Double marking – An assignment is marked independently by two assessors (sequentially or in parallel) and the final result is determined as a combination of the 2 grades & feedback.
* Moderation  - An assignment is marked by a user and a 2nd assessor reviews the grading and either accepts it as the final grade & feedback or overrides it (either entirely or aspects of it). 

# Links to existing tracker issues, forum discussions, contrib plugins
* Forum discussion: https://moodle.org/mod/forum/discuss.php?d=347154
* MDL-49320: https://tracker.moodle.org/browse/MDL-49320

 
# Requirements
To come!

# Further reading
* Description of marking process on [BA Psychology:](bapsychmarking.md)
