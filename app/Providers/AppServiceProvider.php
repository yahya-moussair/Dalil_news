<?php

namespace App\Providers;

use App\Services\Clustering\ClusteringService;
use App\Services\Clustering\FakeClusteringService;
use App\Services\Clustering\OpenAiClusteringService;
use App\Services\Normalization\FakeNormalizationService;
use App\Services\Normalization\NormalizationService;
use App\Services\Normalization\OpenAiNormalizationService;
use App\Services\Transcription\FakeTranscriptionService;
use App\Services\Transcription\OpenAiTranscriptionService;
use App\Services\Transcription\TranscriptionService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TranscriptionService::class, function (Application $app): TranscriptionService {
            $driver = (string) config('dalil.drivers.transcription', 'fake');

            return $driver === 'openai'
                ? $app->make(OpenAiTranscriptionService::class)
                : $app->make(FakeTranscriptionService::class);
        });

        $this->app->bind(NormalizationService::class, function (Application $app): NormalizationService {
            $driver = (string) config('dalil.drivers.normalization', 'fake');

            return $driver === 'openai'
                ? $app->make(OpenAiNormalizationService::class)
                : $app->make(FakeNormalizationService::class);
        });

        $this->app->bind(ClusteringService::class, function (Application $app): ClusteringService {
            $driver = (string) config('dalil.drivers.clustering', 'fake');

            return $driver === 'openai'
                ? $app->make(OpenAiClusteringService::class)
                : $app->make(FakeClusteringService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
