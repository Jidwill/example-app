<?php

namespace App\Services\FoodLookup;

use RuntimeException;

/**
 * Thrown when the lookup fails (API down, too slow, rate limited etc).
 * The message is safe to show to users.
 */
class FoodLookupException extends RuntimeException {}
