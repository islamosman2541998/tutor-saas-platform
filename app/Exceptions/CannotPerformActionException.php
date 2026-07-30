<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * General-purpose "blocked by a business rule" exception for actions other
 * than delete (see CannotDeleteException for that specific case). Message
 * is written in Arabic and meant to be shown to the user as-is.
 */
class CannotPerformActionException extends RuntimeException {}
