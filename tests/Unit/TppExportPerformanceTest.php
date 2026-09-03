<?php

namespace Tests\Unit;

use App\Exports\TppExport;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Tests\TestCase;

class TppExportPerformanceTest extends TestCase
{
    public function test_ekspor_tpp_menggunakan_query_bertahap_bukan_koleksi_penuh(): void
    {
        $export = new TppExport();

        $this->assertInstanceOf(FromQuery::class, $export);
        $this->assertNotInstanceOf(FromCollection::class, $export);
        $this->assertInstanceOf(Builder::class, $export->query());
    }
}
