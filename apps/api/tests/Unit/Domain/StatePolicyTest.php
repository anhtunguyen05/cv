<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Evidence\Enums\EvidenceInterviewStatus;
use App\Domain\Evidence\Policies\EvidenceInterviewLifecycle;
use App\Domain\JobFit\Policies\JobDescriptionLifecycle;
use App\Domain\OperationalSafety\Enums\AuditFailureCategory;
use App\Domain\OperationalSafety\Enums\AuditLatencyCategory;
use App\Domain\OperationalSafety\Policies\AuditEventConsistency;
use App\Domain\Patch\Enums\PatchStatus;
use App\Domain\Patch\Policies\PatchLifecycle;
use PHPUnit\Framework\TestCase;

final class StatePolicyTest extends TestCase
{
    public function test_evidence_interview_transitions_are_explicit(): void
    {
        self::assertTrue(EvidenceInterviewLifecycle::canTransition(EvidenceInterviewStatus::Active, EvidenceInterviewStatus::Completed));
        self::assertTrue(EvidenceInterviewLifecycle::canTransition(EvidenceInterviewStatus::Active, EvidenceInterviewStatus::Expired));
        self::assertFalse(EvidenceInterviewLifecycle::canTransition(EvidenceInterviewStatus::Completed, EvidenceInterviewStatus::Active));
    }

    public function test_patch_transitions_are_explicit(): void
    {
        self::assertTrue(PatchLifecycle::canTransition(PatchStatus::Pending, PatchStatus::Applied));
        self::assertTrue(PatchLifecycle::canTransition(PatchStatus::Pending, PatchStatus::Rejected));
        self::assertFalse(PatchLifecycle::canTransition(PatchStatus::Applied, PatchStatus::Pending));
    }

    public function test_job_description_revision_and_audit_rules_are_pure(): void
    {
        self::assertTrue(JobDescriptionLifecycle::canUpdate(false));
        self::assertFalse(JobDescriptionLifecycle::canUpdate(true));
        self::assertSame(3, JobDescriptionLifecycle::nextRevision(2));
        self::assertTrue(AuditEventConsistency::isConsistent(
            'succeeded',
            AuditFailureCategory::None,
            AuditLatencyCategory::Fast,
        ));
        self::assertFalse(AuditEventConsistency::isConsistent(
            'succeeded',
            AuditFailureCategory::Timeout,
            AuditLatencyCategory::Fast,
        ));
    }
}
