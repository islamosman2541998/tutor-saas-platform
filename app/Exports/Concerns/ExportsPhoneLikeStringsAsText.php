<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Excel auto-detects digit-only strings as numbers. That silently strips
 * leading zeros from phone numbers/codes and drops a leading "+" on
 * international numbers (e.g. "+201012345678" becomes 201012345678). Any
 * mapped value that looks like a phone number, receipt number, or code —
 * a run of 4+ digits, optionally with a leading "+" — is forced into a text
 * cell instead of letting PhpSpreadsheet's own numeric heuristics decide.
 *
 * Applied via WithCustomValueBinder on export classes that map phone/code
 * columns; other values fall through to the library's default binder.
 */
trait ExportsPhoneLikeStringsAsText
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && preg_match('/^\+?\d{4,}$/', trim($value))) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }
}
