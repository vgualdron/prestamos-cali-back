<?php
    namespace App\Services\Interfaces;
    
    interface UserServiceInterface
    {
        function list(int $displayAll);
        function listByRoleName(int $displayAll, string $name, int $city);
        function get(int $id);
        function create(array $user);
        function update(array $user, int $id);
        function delete(int $user);
        function updateProfile(array $user, int $id);
        function updatePushToken(string $token, int $id);
    }
?>