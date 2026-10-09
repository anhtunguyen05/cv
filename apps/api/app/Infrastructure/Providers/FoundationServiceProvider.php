<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Auth\Contracts\UserRepository;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\Contracts\ProfileReader;
use App\Application\Cv\Contracts\ProfileRepository;
use App\Application\Cv\Contracts\TemplateCatalog;
use App\Application\Cv\Contracts\VersionRepository;
use App\Application\Dashboard\Contracts\DashboardSummaryReader;
use App\Application\Evidence\Contracts\EvidenceAnswerWorkflow;
use App\Application\Evidence\Contracts\EvidenceInterviewWorkflow;
use App\Application\JobFit\Contracts\AnalysisWorkflow;
use App\Application\JobFit\Contracts\JobDescriptionWorkflow;
use App\Application\JobFit\Contracts\MatchWorkflow;
use App\Application\OperationalSafety\Contracts\AuditEventAppender;
use App\Application\Patch\Contracts\PatchProviderClient;
use App\Application\Patch\Contracts\PatchWorkflow;
use App\Application\Patch\PatchProposalProvider;
use App\Infrastructure\Http\Patch\DeterministicFakePatchProposalProvider;
use App\Infrastructure\Http\Patch\LaravelPatchProviderClient;
use App\Infrastructure\Http\Patch\RemotePatchProposalProvider;
use App\Infrastructure\Persistence\Auth\EloquentUserRepository;
use App\Infrastructure\Persistence\Cv\EloquentIdempotencyStore;
use App\Infrastructure\Persistence\Cv\EloquentProfileReader;
use App\Infrastructure\Persistence\Cv\EloquentProfileRepository;
use App\Infrastructure\Persistence\Cv\EloquentTemplateCatalog;
use App\Infrastructure\Persistence\Cv\EloquentVersionRepository;
use App\Infrastructure\Persistence\Dashboard\EloquentDashboardSummaryReader;
use App\Infrastructure\Persistence\OperationalSafety\EloquentAuditEventStore;
use App\Infrastructure\Workflow\Evidence\EloquentEvidenceAnswerWorkflow;
use App\Infrastructure\Workflow\Evidence\EloquentEvidenceInterviewWorkflow;
use App\Infrastructure\Workflow\JobFit\EloquentAnalysisWorkflow;
use App\Infrastructure\Workflow\JobFit\EloquentJobDescriptionWorkflow;
use App\Infrastructure\Workflow\JobFit\EloquentMatchWorkflow;
use App\Infrastructure\Workflow\Patch\EloquentPatchWorkflow;
use App\Shared\Application\Contracts\AdvisoryLock;
use App\Shared\Application\Contracts\TransactionManager;
use App\Shared\Infrastructure\Persistence\LaravelAdvisoryLock;
use App\Shared\Infrastructure\Persistence\LaravelTransactionManager;
use Illuminate\Support\ServiceProvider;

final class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(AdvisoryLock::class, LaravelAdvisoryLock::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(DashboardSummaryReader::class, EloquentDashboardSummaryReader::class);
        $this->app->bind(AuditEventAppender::class, EloquentAuditEventStore::class);
        $this->app->bind(ProfileReader::class, EloquentProfileReader::class);
        $this->app->bind(IdempotencyStore::class, EloquentIdempotencyStore::class);
        $this->app->bind(ProfileRepository::class, EloquentProfileRepository::class);
        $this->app->bind(VersionRepository::class, EloquentVersionRepository::class);
        $this->app->bind(TemplateCatalog::class, EloquentTemplateCatalog::class);
        $this->app->bind(JobDescriptionWorkflow::class, EloquentJobDescriptionWorkflow::class);
        $this->app->bind(AnalysisWorkflow::class, EloquentAnalysisWorkflow::class);
        $this->app->bind(MatchWorkflow::class, EloquentMatchWorkflow::class);
        $this->app->bind(EvidenceInterviewWorkflow::class, EloquentEvidenceInterviewWorkflow::class);
        $this->app->bind(EvidenceAnswerWorkflow::class, EloquentEvidenceAnswerWorkflow::class);
        $this->app->bind(PatchWorkflow::class, EloquentPatchWorkflow::class);
        $this->app->bind(PatchProposalProvider::class, function ($app): PatchProposalProvider {
            if (config('ai.patch_provider', 'fake') === 'remote') {
                $provider = $app->make(RemotePatchProposalProvider::class);

                return $provider;
            }

            return $app->make(DeterministicFakePatchProposalProvider::class);
        });
        $this->app->singleton(PatchProviderClient::class, LaravelPatchProviderClient::class);
    }
}
