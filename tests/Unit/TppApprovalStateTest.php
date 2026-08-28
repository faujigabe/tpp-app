<?php

namespace Tests\Unit;

use App\Models\TppApproval;
use PHPUnit\Framework\TestCase;

class TppApprovalStateTest extends TestCase
{
    public function test_hanya_draft_yang_dapat_diedit_dan_dikirim(): void
    {
        $draft = new TppApproval(['status' => TppApproval::STATUS_DRAFT]);
        $submitted = new TppApproval(['status' => TppApproval::STATUS_SUBMITTED]);
        $locked = new TppApproval(['status' => TppApproval::STATUS_LOCKED]);

        $this->assertTrue($draft->canBeEdited());
        $this->assertTrue($draft->canBeSubmitted());
        $this->assertFalse($submitted->canBeEdited());
        $this->assertFalse($submitted->canBeSubmitted());
        $this->assertFalse($locked->canBeEdited());
        $this->assertFalse($locked->canBeSubmitted());
    }

    public function test_hanya_submitted_yang_dapat_dikunci(): void
    {
        $draft = new TppApproval(['status' => TppApproval::STATUS_DRAFT]);
        $submitted = new TppApproval(['status' => TppApproval::STATUS_SUBMITTED]);
        $locked = new TppApproval(['status' => TppApproval::STATUS_LOCKED]);

        $this->assertFalse($draft->canBeLocked());
        $this->assertTrue($submitted->canBeLocked());
        $this->assertFalse($locked->canBeLocked());
    }

    public function test_submitted_dan_locked_dapat_dibuka_kembali(): void
    {
        $draft = new TppApproval(['status' => TppApproval::STATUS_DRAFT]);
        $submitted = new TppApproval(['status' => TppApproval::STATUS_SUBMITTED]);
        $locked = new TppApproval(['status' => TppApproval::STATUS_LOCKED]);

        $this->assertFalse($draft->canBeUnlocked());
        $this->assertTrue($submitted->canBeUnlocked());
        $this->assertTrue($locked->canBeUnlocked());
    }

    public function test_status_tidak_dikenal_gagal_aman_dan_tidak_dapat_diedit(): void
    {
        $approval = new TppApproval(['status' => 'status-rusak']);

        $this->assertSame(TppApproval::STATUS_LOCKED, $approval->normalizedStatus());
        $this->assertFalse($approval->canBeEdited());
        $this->assertFalse($approval->canBeSubmitted());
        $this->assertFalse($approval->canBeLocked());
        $this->assertTrue($approval->canBeUnlocked());
    }

    public function test_status_kosong_tetap_diperlakukan_sebagai_draft_baru(): void
    {
        $approval = new TppApproval();

        $this->assertSame(TppApproval::STATUS_DRAFT, $approval->normalizedStatus());
        $this->assertTrue($approval->canBeEdited());
        $this->assertTrue($approval->canBeSubmitted());
    }
}
