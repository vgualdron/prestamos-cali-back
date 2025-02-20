<?php
    namespace App\Services\Interfaces;
    
    interface UserServiceInterface
    {
        function list(int $displayAll);
        function listByRoleName(int $displayAll, string $name, int $city);
        function listByArea(int $area);
        function get(int $id);
        function create(array $user);
        function update(array $user, int $id);
        function delete(int $user);
        function updateProfile(array $user, int $id);
        function updatePushToken(string $token, int $id);
        function updateLocation(array $user, int $id);
    }
?>