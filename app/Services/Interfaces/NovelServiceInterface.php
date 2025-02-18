<?php
    namespace App\Services\Interfaces;

    interface NovelServiceInterface
    {
        function list(string $status);
        function listForUpdate(string $status, string $query);
        function listReds(int $city, int $user);
        function create(array $novel);
        function update(array $novel, int $id);
        function updateStatus(array $novel, int $id);
        function delete(int $id);
        function get(int $id);
        function getByPhone(string $phone);
    }
?>
