<?php
    namespace App\Services\Interfaces;

    interface ReddirectionServiceInterface
    {
        function create(array $reddirection);
        function getCurrentByUser(int $user);
        function update(array $reddirection, int $id);
        function getByLending(int $lending);
        function delete(int $id);
    }
?>
