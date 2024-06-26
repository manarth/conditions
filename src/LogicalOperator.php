<?php

declare(strict_types=1);

namespace Drupal\conditions;

/**
 * Logical operators which can be used with the conditions ecosystem.
 */
enum LogicalOperator : string {
  case And = '&&';
  case Not = '!';
  case Or  = '||';
  case Xor = 'xor';
}
