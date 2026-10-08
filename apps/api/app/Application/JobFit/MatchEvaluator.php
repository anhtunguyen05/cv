<?php

declare(strict_types=1);

namespace App\Application\JobFit;

/**
 * Evaluates a match from in-memory analysis and CV snapshot data.
 *
 * This boundary deliberately has no model, repository, or persistence
 * dependency so product matching and synthetic quality evaluation use the
 * same deterministic calculation.
 */
final class MatchEvaluator
{
    public const ENGINE_VERSION = '1.0.0';

    public function __construct(private readonly JobDescriptionAnalyzer $analyzer) {}

    /**
     * @param  array<string,mixed>  $analysis
     * @param  array<string,mixed>  $snapshot
     * @return array{overall_score:float|int,matched_skills:array<int,array<string,mixed>>,missing_skills:array<int,array<string,mixed>>,weak_evidence:array<int,array<string,mixed>>,recommendations:array<int,array<string,mixed>>}
     */
    public function evaluate(array $analysis, array $snapshot): array
    {
        $required = $this->items($analysis['required_skills'] ?? null);
        $preferred = $this->items($analysis['nice_to_have_skills'] ?? null);
        $requiredIds = array_fill_keys(array_column($required, 'signal_id'), true);
        $preferred = array_values(array_filter($preferred, static fn (array $item): bool => ! isset($requiredIds[$item['signal_id']])));
        $matched = [];
        $missing = [];
        $weak = [];
        $categoryValues = [];
        $all = array_merge(
            array_map(static fn (array $item): array => [$item, 'required'], $required),
            array_map(static fn (array $item): array => [$item, 'preferred'], $preferred),
        );
        $evidenceTotal = 0.0;
        $evidenceCount = 0;

        foreach ($all as [$item, $importance]) {
            $evidence = $this->evidence($item['signal_id'], $snapshot);
            $classification = [
                'signal_id' => $item['signal_id'],
                'label' => $item['label'],
                'importance' => $importance,
                'evidence_level' => $evidence['level'],
                'source_references' => $evidence['references'],
            ];
            if ($evidence['level'] === 'strong') {
                $matched[] = $classification;
            } elseif ($evidence['level'] === 'weak') {
                $weak[] = $classification;
            } else {
                $missing[] = $classification;
            }
            $value = $evidence['level'] === 'strong' ? 1.0 : ($evidence['level'] === 'weak' ? 0.5 : 0.0);
            $evidenceTotal += $value;
            $evidenceCount++;
            $categoryValues[$importance][] = $value;
        }

        foreach (['required' => 50.0, 'preferred' => 10.0] as $category => $weight) {
            if (($categoryValues[$category] ?? []) !== []) {
                $categoryValues['weighted'][] = [$weight, array_sum($categoryValues[$category]) / count($categoryValues[$category])];
            }
        }
        if ($evidenceCount > 0) {
            $categoryValues['weighted'][] = [25.0, $evidenceTotal / $evidenceCount];
        }

        $seniority = is_string($analysis['seniority'] ?? null) ? $analysis['seniority'] : null;
        $seniorityState = (string) (($analysis['seniority_state'] ?? null) ?: ($seniority === null ? 'absent' : 'detected'));
        if ($seniorityState === 'detected' && $seniority !== null && $seniority !== '') {
            $categoryValues['weighted'][] = [10.0, $this->cvContains($snapshot, $seniority) ? 1.0 : 0.0];
        }

        $role = is_array($analysis['extracted_role'] ?? null) ? $analysis['extracted_role'] : null;
        $domains = $this->items($analysis['domain_context'] ?? null, 'domain');
        if ($role !== null && ($role['state'] ?? 'absent') === 'detected') {
            $roleFound = $this->cvContains($snapshot, (string) ($role['value'] ?? ''));
            $domainFound = $domains !== [] && $this->cvContainsAny($snapshot, $domains);
            $categoryValues['weighted'][] = [5.0, ($roleFound || $domainFound) ? 1.0 : 0.0];
        } elseif ($domains !== []) {
            $categoryValues['weighted'][] = [5.0, $this->cvContainsAny($snapshot, $domains) ? 1.0 : 0.0];
        }

        $weights = $categoryValues['weighted'] ?? [];
        $weightTotal = array_sum(array_column($weights, 0));
        $weightedScore = $weightTotal <= 0.0
            ? 0.0
            : array_sum(array_map(static fn (array $entry): float => $entry[0] * $entry[1], $weights)) / $weightTotal * 100;

        usort($missing, [$this, 'sortClassification']);
        usort($weak, [$this, 'sortClassification']);
        usort($matched, [$this, 'sortClassification']);
        $missing = array_merge(
            array_values(array_filter($missing, static fn (array $item): bool => $item['importance'] === 'required')),
            array_values(array_filter($missing, static fn (array $item): bool => $item['importance'] === 'preferred')),
        );
        $weak = array_merge(
            array_values(array_filter($weak, static fn (array $item): bool => $item['importance'] === 'required')),
            array_values(array_filter($weak, static fn (array $item): bool => $item['importance'] === 'preferred')),
        );
        $recommendations = [];
        foreach (array_merge($missing, $weak) as $index => $item) {
            $recommendations[] = [
                'id' => 'recommendation-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'priority' => $item['importance'] === 'required' ? 'high' : 'medium',
                'target_cv_section' => $item['importance'] === 'required' ? 'projects_or_experience' : 'skills_or_summary',
                'related_signal_ids' => [$item['signal_id']],
                'rationale' => $item['evidence_level'] === 'weak'
                    ? 'The signal appears without strong project or experience evidence.'
                    : 'The current CV Version contains no supported evidence for this signal.',
                'action' => 'Add truthful, source-backed evidence if this experience is accurate; do not add an unsupported claim.',
            ];
        }

        return [
            'overall_score' => round(max(0.0, min(100.0, $weightedScore)), 2),
            'matched_skills' => $matched,
            'missing_skills' => $missing,
            'weak_evidence' => $weak,
            'recommendations' => $recommendations,
        ];
    }

    /** @return array<int,array{signal_id:string,label:string}> */
    private function items(mixed $value, string $kind = 'skill'): array
    {
        if (! is_array($value) || ! is_array($value['items'] ?? null) || ($value['state'] ?? 'detected') !== 'detected') {
            return [];
        }

        if ($kind === 'domain') {
            return array_values(array_filter($value['items'], static fn (mixed $item): bool => is_string($item) && $item !== ''));
        }

        return array_values(array_filter($value['items'], static fn (mixed $item): bool => is_array($item) && isset($item['signal_id'], $item['label'])));
    }

    /** @param array<int,string> $domains */
    private function cvContainsAny(array $snapshot, array $domains): bool
    {
        foreach ($domains as $domain) {
            if ($this->cvContains($snapshot, $domain)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{level:string,references:array<int,string>} */
    private function evidence(string $signalId, array $snapshot): array
    {
        $definition = $this->analyzer->vocabulary($signalId);
        $aliases = $definition['aliases'] ?? [$signalId];
        $result = ['strong' => [], 'weak' => []];
        $walk = function (mixed $value, string $path, string $section) use (&$walk, &$result, $aliases): void {
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    if (in_array((string) $key, ['id', 'url', 'website_url', 'linkedin_url', 'github_url', 'start_date', 'end_date', 'metadata'], true)) {
                        continue;
                    }
                    $childPath = $path === '' ? (string) $key : $path.'.'.$key;
                    $childSection = $section;
                    if (in_array((string) $key, ['projects', 'experience'], true)) {
                        $childSection = 'strong';
                    } elseif (in_array((string) $key, ['skills', 'summary'], true)) {
                        $childSection = 'weak';
                    }
                    $walk($child, $childPath, $childSection);
                }

                return;
            }
            if (! is_string($value) || $value === '') {
                return;
            }
            $lower = mb_strtolower($value, 'UTF-8');
            foreach ($aliases as $alias) {
                if ($this->containsPositiveAlias($lower, $alias)) {
                    if (in_array($section, ['strong', 'weak'], true)) {
                        $result[$section][] = $path;
                    }
                    break;
                }
            }
        };
        $walk($snapshot, '', 'unsupported');
        $strong = array_values(array_unique($result['strong']));
        $weak = array_values(array_unique($result['weak']));
        if ($strong !== []) {
            return ['level' => 'strong', 'references' => $strong];
        }
        if ($weak !== []) {
            return ['level' => 'weak', 'references' => $weak];
        }

        return ['level' => 'missing', 'references' => []];
    }

    private function containsPositiveAlias(string $text, string $alias): bool
    {
        $quoted = preg_quote($alias, '/');
        $positive = '/(?<![\\pL\\pN.])'.$quoted.'(?!(?:[\\pL\\pN]|\\.(?=[\\pL\\pN])))/iu';
        preg_match_all($positive, $text, $occurrences, PREG_OFFSET_CAPTURE);
        foreach ($occurrences[0] ?? [] as [$match, $offset]) {
            $before = substr($text, 0, (int) $offset);
            $after = substr($text, (int) $offset + strlen((string) $match));
            if (preg_match('/(?:\\bno\\b|\\bnot\\b|\\bwithout\\b|\\bkhông\\b|\\bkhong\\b)[^\\n.;]{0,32}$/iu', $before) !== 1
                && preg_match('/^[^\\n.;]{0,24}\\b(?:is\\s+not|not\\s+required|not\\s+needed|not\\s+mandatory|không\\s+cần|khong\\s+can)\\b/iu', $after) !== 1) {
                return true;
            }
        }

        return false;
    }

    private function cvContains(array $snapshot, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }
        $found = false;
        $walk = function (mixed $value, string $key) use (&$walk, &$found, $needle): void {
            if ($found) {
                return;
            }
            if (is_array($value)) {
                foreach ($value as $childKey => $child) {
                    if (in_array((string) $childKey, ['id', 'url', 'website_url', 'linkedin_url', 'github_url', 'start_date', 'end_date', 'metadata'], true)) {
                        continue;
                    }
                    $walk($child, (string) $childKey);
                }

                return;
            }
            if (! is_string($value) || $value === '') {
                return;
            }
            $quoted = preg_quote(mb_strtolower($needle, 'UTF-8'), '/');
            if (preg_match('/(?<![\\pL\\pN.])'.$quoted.'(?!(?:[\\pL\\pN]|\\.(?=[\\pL\\pN])))/iu', mb_strtolower($value, 'UTF-8')) === 1) {
                $found = true;
            }
        };
        $walk($snapshot, '');

        return $found;
    }

    /** @param array<string,mixed> $left @param array<string,mixed> $right */
    private function sortClassification(array $left, array $right): int
    {
        return $left['signal_id'] <=> $right['signal_id'];
    }
}
