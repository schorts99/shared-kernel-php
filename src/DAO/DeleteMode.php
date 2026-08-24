<?php

namespace Schorts\SharedKernel\DAO;

enum DeleteMode: string
{
  case SOFT = 'SOFT';
  case HARD = 'HARD';
}
