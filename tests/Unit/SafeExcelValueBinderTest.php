<?php

namespace Tests\Unit;

use App\Exports\SafeExcelValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

class SafeExcelValueBinderTest extends TestCase
{
    /**
     * @dataProvider dangerousValues
     */
    public function test_teks_yang_berpotensi_menjadi_formula_dipaksa_sebagai_string(string $value): void
    {
        $spreadsheet = new Spreadsheet();
        $cell = $spreadsheet->getActiveSheet()->getCell('A1');

        (new SafeExcelValueBinder())->bindValue($cell, $value);

        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        $this->assertSame($value, $cell->getValue());
        $spreadsheet->disconnectWorksheets();
    }

    public static function dangerousValues(): array
    {
        return [
            'sama dengan' => ['=HYPERLINK("https://contoh.invalid")'],
            'plus' => ['+1+1'],
            'minus' => ['-1+1'],
            'at' => ['@SUM(1,1)'],
            'spasi sebelum formula' => ["  =1+1"],
            'tab sebelum formula' => ["\t=1+1"],
        ];
    }

    public function test_angka_tetap_disimpan_sebagai_numerik(): void
    {
        $spreadsheet = new Spreadsheet();
        $cell = $spreadsheet->getActiveSheet()->getCell('A1');

        (new SafeExcelValueBinder())->bindValue($cell, 1250000.5);

        $this->assertSame(DataType::TYPE_NUMERIC, $cell->getDataType());
        $this->assertSame(1250000.5, $cell->getValue());
        $spreadsheet->disconnectWorksheets();
    }
}
