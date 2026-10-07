<?php

declare(strict_types=1);

namespace Tests\Unit\JobFit;

use App\Application\JobFit\JobDescriptionAnalyzer;
use PHPUnit\Framework\TestCase;

final class JobDescriptionAnalyzerTest extends TestCase
{
    public function test_reviewed_and_held_out_fixture_corpus_is_executable_and_repeatable(): void
    {
        $fixture = json_decode(
            (string) file_get_contents($this->fixturePath('analysis-v1.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $examples = $fixture['examples'] ?? [];
        $heldOut = $fixture['held_out_counterexamples'] ?? [];

        self::assertSame(JobDescriptionAnalyzer::SCHEMA_VERSION, $fixture['analysis_schema_version'] ?? null);
        self::assertSame(JobDescriptionAnalyzer::RULE_VERSION, $fixture['analysis_rule_version'] ?? null);
        self::assertGreaterThanOrEqual(40, count($examples));
        self::assertGreaterThanOrEqual(20, count(array_filter($examples, static fn (array $case): bool => ($case['language'] ?? '') === 'en')));
        self::assertGreaterThanOrEqual(20, count(array_filter($examples, static fn (array $case): bool => ($case['language'] ?? '') !== 'en')));
        self::assertGreaterThanOrEqual(12, count($heldOut));
        self::assertCount(count($examples), array_unique(array_column($examples, 'id')));

        $analyzer = new JobDescriptionAnalyzer;
        foreach ([...$examples, ...$heldOut] as $case) {
            $first = $analyzer->analyze((string) $case['raw_text'], $case['role'] ?? null);
            $second = $analyzer->analyze((string) $case['raw_text'], $case['role'] ?? null);

            self::assertSame($first, $second, (string) $case['id']);
            self::assertSame(
                json_encode($first, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($second, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                (string) $case['id'],
            );
            $this->assertExpectedSignals($first, $case['expected'] ?? [], (string) $case['id']);
        }
    }

    public function test_analysis_is_deterministic_and_canonicalized(): void
    {
        $analyzer = new JobDescriptionAnalyzer;
        $source = "Requirements:\r\n- Vue 3 and TypeScript\r\nNice to have:\r\n- Docker";
        $first = $analyzer->analyze($source, 'Frontend Engineer');
        $second = $analyzer->analyze(str_replace("\r\n", "\n", $source), 'Frontend Engineer');

        self::assertSame($first, $second);
        self::assertSame('1.0.0', JobDescriptionAnalyzer::RULE_VERSION);
        self::assertSame('detected', $first['required_skills']['state']);
        self::assertSame(['typescript', 'vue'], array_column($first['required_skills']['items'], 'signal_id'));
        self::assertSame(['docker'], array_column($first['nice_to_have_skills']['items'], 'signal_id'));
    }

    public function test_negated_signal_is_not_detected(): void
    {
        $analysis = (new JobDescriptionAnalyzer)->analyze('Requirements: Vue is not required. No Docker experience needed.');

        self::assertSame('absent', $analysis['required_skills']['state']);
        self::assertSame([], $analysis['required_skills']['items']);
    }

    public function test_unicode_headings_and_word_boundaries_are_supported(): void
    {
        $analysis = (new JobDescriptionAnalyzer)->analyze("Trách nhiệm:\n- Build ứng dụng web.\nYêu cầu:\n- Vue và TypeScript.\nƯu tiên:\n- Docker.\nInternal stakeholder communication.");

        self::assertSame(['typescript', 'vue'], array_column($analysis['required_skills']['items'], 'signal_id'));
        self::assertSame(['docker'], array_column($analysis['nice_to_have_skills']['items'], 'signal_id'));
        self::assertSame('detected', $analysis['domain_context']['state']);
        self::assertSame('absent', $analysis['seniority']['state']);
    }

    public function test_inline_and_preferred_only_headings_keep_skill_importance(): void
    {
        $analyzer = new JobDescriptionAnalyzer;

        $inline = $analyzer->analyze('Requirements: Vue and TypeScript');
        self::assertSame(['typescript', 'vue'], array_column($inline['required_skills']['items'], 'signal_id'));

        $preferredOnly = $analyzer->analyze('Nice to have: Vue');
        self::assertSame('absent', $preferredOnly['required_skills']['state']);
        self::assertSame(['vue'], array_column($preferredOnly['nice_to_have_skills']['items'], 'signal_id'));
    }

    public function test_repeated_negated_aliases_remain_excluded(): void
    {
        $analysis = (new JobDescriptionAnalyzer)->analyze("Requirements:\n- Vue.js is not required.\n- Vue.js is not needed.");

        self::assertSame('absent', $analysis['required_skills']['state']);
        self::assertSame([], $analysis['required_skills']['items']);
    }

    public function test_seniority_does_not_treat_generic_experience_as_senior_and_marks_conflicts_unknown(): void
    {
        $analyzer = new JobDescriptionAnalyzer;

        self::assertSame('absent', $analyzer->analyze('Requirements: Có kinh nghiệm với Laravel.')['seniority']['state']);
        self::assertSame('unknown', $analyzer->analyze('Requirements: Junior or Senior frontend engineer.')['seniority']['state']);
        self::assertSame('absent', $analyzer->analyze('Requirements: No senior experience required.')['seniority']['state']);
    }

    public function test_overlapping_soft_skill_aliases_are_canonicalized(): void
    {
        $analysis = (new JobDescriptionAnalyzer)->analyze('Strong communication skills and teamwork required.');

        self::assertSame(['communication', 'teamwork'], $analysis['soft_skills']['items']);
    }

    /** @param array<string,mixed> $analysis @param array<string,mixed> $expected */
    private function assertExpectedSignals(array $analysis, array $expected, string $caseId): void
    {
        foreach ([
            'required' => 'required_skills',
            'preferred' => 'nice_to_have_skills',
            'responsibilities' => 'responsibilities',
            'soft_skills' => 'soft_skills',
            'domain_context' => 'domain_context',
        ] as $expectedKey => $actualKey) {
            if (! isset($expected[$expectedKey])) {
                continue;
            }
            self::assertSame($expected[$expectedKey]['state'], $analysis[$actualKey]['state'], $caseId.' '.$expectedKey.' state');
            if (array_key_exists('signal_ids', $expected[$expectedKey])) {
                self::assertSame($expected[$expectedKey]['signal_ids'], array_column($analysis[$actualKey]['items'], 'signal_id'), $caseId.' '.$expectedKey.' ids');
            }
        }
        foreach (['role', 'seniority'] as $key) {
            if (isset($expected[$key])) {
                self::assertSame($expected[$key]['state'], $analysis[$key]['state'], $caseId.' '.$key.' state');
                if (array_key_exists('value', $expected[$key])) {
                    self::assertSame($expected[$key]['value'], $analysis[$key]['value'], $caseId.' '.$key.' value');
                }
            }
        }
    }

    private function fixturePath(string $name): string
    {
        $candidates = [
            dirname(__DIR__, 3).'/../../docs/contracts/jd/fixtures/'.$name,
            '/workspace/docs/contracts/jd/fixtures/'.$name,
        ];
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        self::fail('Analysis fixture is unavailable. Checked: '.implode(', ', $candidates));
    }
}
