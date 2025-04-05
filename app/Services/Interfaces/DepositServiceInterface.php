<?php
    namespace App\Services\Interfaces;

    interface DepositServiceInterface
    {
        function create(array $deposit);
        function update(array $deposit, int $id);
        function delete(int $id);
    }
?>
