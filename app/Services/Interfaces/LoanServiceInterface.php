<?php
    namespace App\Services\Interfaces;

    interface LoanServiceInterface
    {
        function list(string $status);
        function create(array $loan);
        function update(array $loan, int $id);
        function delete(int $id);
        function get(int $id);
    }
?>
