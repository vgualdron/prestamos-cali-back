<?php
    namespace App\Services\Interfaces;
    
    interface ExpenseServiceInterface
    {
        function list(string $status);
        function create(array $expense);
        function update(array $expense, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>