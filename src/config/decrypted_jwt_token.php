<?php

return [
    'user_id_prop' => 'sub',
    'user_name_prop' => 'name',
    'user_role_prop' => 'role',
    'user_roles_prop' => 'roles',
    'token_expiry_prop' => 'exp',
    'jwt_secret_key' => env('JWT_SECRET'),
    'jwt_algorithm' => env('JWT_ALGORITHM', 'HS256'),
];
