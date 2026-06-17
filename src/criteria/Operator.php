<?php

namespace Schorts\SharedKernel\Criteria;

enum Operator: string
{
  case EQUAL = 'EQUAL';
  case GREATER_THAN = 'GREATER_THAN';
  case LESS_THAN = 'LESS_THAN';
  case GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
  case LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
  case NOT_EQUAL = 'NOT_EQUAL';
  case IN = 'IN';
  case NOT_IN = 'NOT_IN';
  case LIKE = 'LIKE';
  case BETWEEN = 'BETWEEN';
  case GEO_RADIUS = 'GEO_RADIUS';
  case ARRAY_CONTAINS = 'ARRAY_CONTAINS';
  case ARRAY_CONTAINS_ANY = 'ARRAY_CONTAINS_ANY';
}
