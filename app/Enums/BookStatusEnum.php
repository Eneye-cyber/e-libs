<?php

namespace App\Enums;

enum BookStatusEnum: string {
    case UNAVAILABLE = 'Unavailable';
    case COMPLETED = 'Completed';
    case INCOMPLETE = 'Incomplete';
}