<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
  case CREATED = 'CREATED';
  case SIGNED = 'SIGNED';
  case BROADCASTED = 'BROADCASTED';
  case FAILED = 'FAILED';
}
