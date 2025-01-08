<?php
    namespace App\Services\Interfaces;

    interface ExpenseServiceInterface
    {
        function list(string $status, string $items);
        function listByUser(int $user, string $status, string $items);
        function listByItem(string $status, int $item);
        function create(array $expense);
        function update(array $expense, int $id);
        function delete(int $id);
        function get(int $id);
    }
?>
