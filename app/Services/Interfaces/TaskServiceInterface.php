<?php
    namespace App\Services\Interfaces;

    interface TaskServiceInterface
    {
        function list(string $status);
        function create(array $task);
        function update(array $task, int $id);
        function delete(int $id);
    }
?>
