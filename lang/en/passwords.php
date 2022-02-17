<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'reset' => 'Your password has been reset!',
    'sent' => 'We have emailed your password reset link!',
    'throttled' => 'Please wait before retrying.',
    'token' => 'This password reset token is invalid.',
    'user' => "We can't find a user with that email address.",
    'mail' => [
        'generic_recipient' => 'recipient',
        'greeting' => 'Dear :name,',
        'subject' => '[:name] Password Reset Notification',
        'intro' => 'We received a password reset request for your :name account. To reset your password open the application and enter the following token:',
        'token' => 'Your password reset token is: **:token**',
        'outro1' => 'If you did not request this reset, then no further action will be required. The reset token will expire 60 minutes after being requested.',
        'outro2' => 'Thank you for using :name.',
        'salutation' => "Greetings,  \n   \n   \nTeam :name"
    ],
];
