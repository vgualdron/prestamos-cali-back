<?php
    namespace App\Services\Interfaces;

    interface WorkplanServiceInterface
    {
        function list(string $date);
        function create(array $district);
        function update(array $district, int $id);
        function delete(int $id);
        function get(int $id);
    }
?>
