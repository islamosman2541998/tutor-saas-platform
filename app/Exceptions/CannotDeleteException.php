<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a delete is blocked by a business rule (e.g. "can't delete a
 * stage that still has grades"). The message is written in Arabic and meant
 * to be shown to the user as-is — see Livewire components catching this and
 * surfacing $e->getMessage() directly via toast/SweetAlert instead of a
 * generic error page.
 */
class CannotDeleteException extends RuntimeException {}
