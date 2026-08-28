<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class SafeExcelValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && preg_match('/^[\x00-\x20]*[=+\-@]/u', $value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
