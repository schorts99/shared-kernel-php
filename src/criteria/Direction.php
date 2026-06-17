<?php

namespace Schorts\SharedKernel\Criteria;

enum Direction: string
{
  case ASC = 'ASC';
  case DESC = 'DESC';
  case NONE = 'NONE';
}
