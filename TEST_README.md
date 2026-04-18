# Unit Tests for UPHS Events

## Running the Tests

To run the unit tests for event addition, use the following command in the terminal from this folder:
C:\xampp\php\php.exe test_add_event.php

If you have PHP added to your system PATH, you can also use:
php test_add_event.php

this execute test script and output to terminal

The test:
- Valid event addition
- Invalid event addition (e.g., empty name)

Each test case will display success or error messages accordingly.

Note for the status thing incase forgotten:
0: Deleted/Disabled - Events that have been soft-deleted
1: Approved/Accepted - Events approved by admins/deans and visible to users
2: Declined/Rejected - Events that were reviewed and rejected by admins/deans
3: Pending/Submitted - Events submitted by organizations awaiting approval

ad-hoc PHP test script in test_add_event.php:

calls the event-insert function directly
checks database insertion results
reports success/failure in plain text
