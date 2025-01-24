<?php
    namespace App\Services\Interfaces;

    interface RoleServiceInterface
    {
        function list();
        function create(array $role);
        function update(array $role, int $id);
        function delete(int $id);
        function get(int $id);
    }
?>
