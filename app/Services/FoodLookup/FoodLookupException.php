<?php

namespace App\Services\FoodLookup;

use RuntimeException;

/**
 * Thrown when a food lookup can't be done (API down, too slow, rate limited...).
 * The message is safe to show to users.
 */
class FoodLookupException extends RuntimeException {}
