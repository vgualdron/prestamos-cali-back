<?php
    namespace App\Services\Interfaces;

    interface ReddirectionServiceInterface
    {
        function create(array $reddirection);
        function getCurrentByUser(int $user);
        function getByUserAndDate(int $user, string $date);
        function update(array $reddirection, int $id);
        function getByLending(int $lending);
        function delete(int $id);
    }
?>
