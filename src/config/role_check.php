<?php

return [
    // Determines whether the role checking should be case-sensitive or not
    'case_sensitive' => false,  // By default, case insensitive
    
    // Custom error message template for unauthorized role access
    'error_message' => 'You do not have the authorized role to access this route. Your current role is ":user_role", but one of the following roles is required: :required_roles',
];
