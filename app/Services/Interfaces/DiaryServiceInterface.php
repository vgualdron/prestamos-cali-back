<?php
    namespace App\Services\Interfaces;

    interface DiaryServiceInterface
    {
        function list(string $date, int $user, string $moment);
        function listDayByDay(string $date, int $user, string $moment);
        function listVisitsReview(string $date);
        function getStatusCases(int $idNew);
        function getByUserAndDate(int $user, string $date);
        function approveVisit(array $diary);
        function get(int $id);
        function create(array $diary);
        function update(array $diary, int $id);
        function updateStatus(array $diary, int $id);
        function delete(int $user);
    }
?>
