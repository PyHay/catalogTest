<?php

namespace App\Service;

interface ProductServiceInterface
{
    public function getProductsByColumn($column);
    public function getProductsByFilter();
}