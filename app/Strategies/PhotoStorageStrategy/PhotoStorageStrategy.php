<?php

namespace App\Strategies\PhotoStorageStrategy;

interface PhotoStorageStrategy
{

   // public function getPhoto(): string;
    public function get($path): string;
    public function store($photo): string;
    public function remove($path): bool;
}
