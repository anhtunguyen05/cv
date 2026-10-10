<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LayerBoundaryTest extends TestCase
{
    private static function appPath(string $path): string
    {
        return dirname(__DIR__, 2).'/app/'.$path;
    }

    public function test_domain_is_framework_independent(): void
    {
        $this->assertNoForbiddenImports(
            self::appPath('Domain'),
            [
                'App\\Application\\',
                'App\\Infrastructure\\',
                'App\\Models\\',
                'App\\Presentation\\',
                'Illuminate\\',
                'Laravel\\',
                'DB::',
                'Http::',
                'GuzzleHttp\\',
            ],
            'App\\Domain',
        );
    }

    public function test_application_does_not_depend_on_adapters_or_frameworks(): void
    {
        $this->assertNoForbiddenImports(
            self::appPath('Application'),
            [
                'App\\Infrastructure\\',
                'App\\Models\\',
                'Illuminate\\',
                'Laravel\\',
                'DB::',
                'Http::',
                'GuzzleHttp\\',
            ],
            'App\\Application',
        );
    }

    public function test_presentation_does_not_depend_on_persistence_directly(): void
    {
        $this->assertNoForbiddenImports(
            self::appPath('Presentation'),
            [
                'App\\Infrastructure\\Persistence\\',
                'App\\Models\\',
                'Illuminate\\Database\\',
                'Illuminate\\Support\\Facades\\DB',
                'DB::',
                'Http::',
            ],
            'App\\Presentation',
        );
    }

    public function test_legacy_models_namespace_has_no_eloquent_models(): void
    {
        $legacyModels = self::appPath('Models');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($legacyModels));
        $phpFiles = [];

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }

        self::assertSame([], $phpFiles, 'Eloquent models must live under Infrastructure/Persistence.');
    }

    /** @param list<string> $forbidden */
    private function assertNoForbiddenImports(string $root, array $forbidden, string $expectedNamespace): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $violations = [];
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            preg_match('/^\s*namespace\s+([^;]+);/mi', $contents, $namespaceMatch);
            $namespace = trim($namespaceMatch[1] ?? '');
            if (! str_starts_with($namespace, $expectedNamespace)) {
                $violations[] = sprintf('%s declares namespace %s; expected %s', $file->getPathname(), $namespace, $expectedNamespace);
            }

            preg_match_all('/^\s*use\s+([^;]+);/mi', $contents, $matches);
            $imports = array_map(
                static fn (string $use): string => trim(preg_replace('/\s+as\s+.+$/i', '', $use)),
                $matches[1] ?? [],
            );

            foreach ($forbidden as $reference) {
                $importViolation = false;
                foreach ($imports as $import) {
                    if (str_starts_with($import, $reference)) {
                        $violations[] = sprintf('%s imports %s', $file->getPathname(), $import);
                        $importViolation = true;
                    }
                }

                // Catch adapter calls and fully-qualified references that bypass an
                // import (for example DB::table() or \App\Infrastructure\...).
                if (! $importViolation && str_contains($contents, $reference)) {
                    $violations[] = sprintf('%s references %s', $file->getPathname(), $reference);
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }
}
