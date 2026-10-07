<?php

declare(strict_types=1);

namespace App\Application\JobFit;

final class JobDescriptionAnalyzer
{
    public const SCHEMA_VERSION = '1.0.0';
    public const RULE_VERSION = '1.0.0';

    /** @var array<string, array{label:string,aliases:array<int,string>}> */
    private const VOCABULARY = [
        'javascript' => ['label' => 'JavaScript', 'aliases' => ['javascript', 'js']],
        'typescript' => ['label' => 'TypeScript', 'aliases' => ['typescript', 'ts']],
        'vue' => ['label' => 'Vue', 'aliases' => ['vue', 'vue.js', 'vuejs']],
        'react' => ['label' => 'React', 'aliases' => ['react', 'react.js', 'reactjs']],
        'angular' => ['label' => 'Angular', 'aliases' => ['angular']],
        'html' => ['label' => 'HTML', 'aliases' => ['html', 'html5']],
        'css' => ['label' => 'CSS', 'aliases' => ['css', 'css3']],
        'tailwind-css' => ['label' => 'Tailwind CSS', 'aliases' => ['tailwind', 'tailwind css']],
        'vite' => ['label' => 'Vite', 'aliases' => ['vite']],
        'pinia' => ['label' => 'Pinia', 'aliases' => ['pinia']],
        'rest-api' => ['label' => 'REST API', 'aliases' => ['rest api', 'restful api', 'restful']],
        'git' => ['label' => 'Git', 'aliases' => ['git', 'git workflow', 'version control']],
        'docker' => ['label' => 'Docker', 'aliases' => ['docker', 'containerization']],
        'php' => ['label' => 'PHP', 'aliases' => ['php']],
        'laravel' => ['label' => 'Laravel', 'aliases' => ['laravel']],
        'python' => ['label' => 'Python', 'aliases' => ['python']],
        'sql' => ['label' => 'SQL', 'aliases' => ['sql', 'postgresql', 'mysql']],
        'testing' => ['label' => 'Testing', 'aliases' => ['unit testing', 'automated testing', 'testing', 'vitest', 'jest', 'phpunit']],
    ];

    /** @return array<string, mixed> */
    public function analyze(string $rawText, ?string $role = null): array
    {
        $source = JobDescriptionValidator::normalizedSource($rawText);
        $lower = mb_strtolower($source, 'UTF-8');
        $roleValue = $this->detectRole($source, $role);
        $preferredSection = $this->sectionInfo($source, ['nice to have', 'nice-to-have', 'preferred', 'bonus', 'plus', 'ưu tiên', 'không bắt buộc', 'mong muốn'], '');
        // An explicitly preferred-only JD must not promote those signals into
        // required skills merely because no required heading exists. When no
        // section headings exist at all, retain the unsectioned-text fallback.
        $requiredSection = $this->sectionInfo(
            $source,
            ['requirements', 'required', 'must have', 'qualifications', 'yêu cầu', 'bắt buộc', 'kỹ năng cần có'],
            $preferredSection['present'] ? '' : $source,
        );
        $required = $this->detectSkills($lower, $requiredSection['text']);
        $preferred = $this->detectSkills($lower, $preferredSection['text']);
        $allSkills = $this->detectSkills($lower, $source);
        $responsibilitySection = $this->sectionInfo($source, ['responsibilities', 'what you will do', 'you will', 'trách nhiệm', 'mô tả công việc', 'bạn sẽ làm'], '');
        $responsibilities = $this->bullets($responsibilitySection['text']);
        $keywords = array_values(array_unique(array_merge($required, $preferred, $allSkills), SORT_REGULAR));
        usort($keywords, static fn (array $a, array $b): int => $a['signal_id'] <=> $b['signal_id']);
        $seniority = $this->detectSeniority($lower);
        $softSkills = $this->detectSoftSkills($lower);
        $domains = $this->detectDomains($lower);

        return [
            'role' => $roleValue === null
                ? ['state' => 'absent', 'value' => null]
                : ['state' => 'detected', 'value' => $roleValue],
            'required_skills' => $this->stateful($required, $this->unknownWhenUnmatched($requiredSection, $required)),
            'nice_to_have_skills' => $this->stateful($preferred, $this->unknownWhenUnmatched($preferredSection, $preferred)),
            'responsibilities' => $this->statefulStrings($responsibilities, $this->unknownWhenUnmatched($responsibilitySection, $responsibilities)),
            'keywords' => $this->stateful($keywords),
            'seniority' => $seniority,
            'soft_skills' => $this->statefulStrings($softSkills),
            'domain_context' => $this->statefulStrings($domains),
        ];
    }

    /** @return array{label:string,aliases:array<int,string>}|null */
    public function vocabulary(string $signalId): ?array
    {
        return self::VOCABULARY[$signalId] ?? null;
    }

    /** @return array<int,array{signal_id:string,label:string}> */
    private function detectSkills(string $lower, string $scope): array
    {
        $signals = [];
        $scopeLower = mb_strtolower($scope, 'UTF-8');
        foreach (self::VOCABULARY as $id => $definition) {
            foreach ($definition['aliases'] as $alias) {
                if (preg_match('/(?<![\pL\pN.])'.preg_quote($alias, '/').'(?!(?:[\pL\pN]|\.(?=[\pL\pN])))/iu', $scopeLower) === 1 && ! $this->isNegated($scopeLower, $alias)) {
                    $signals[] = ['signal_id' => $id, 'label' => $definition['label']];
                    break;
                }
            }
        }
        usort($signals, static fn (array $a, array $b): int => $a['signal_id'] <=> $b['signal_id']);

        return $signals;
    }

    private function isNegated(string $scopeLower, string $alias): bool
    {
        $quotedAlias = preg_quote($alias, '/');
        $positive = '/(?<![\pL\pN.])'.$quotedAlias.'(?!(?:[\pL\pN]|\.(?=[\pL\pN])))/iu';
        $negatedBefore = '/(?:\bno\b|\bnot\b|\bwithout\b|\bkhông\b|\bkhong\b)[^\n.;]{0,32}$/iu';
        $negatedAfter = '/^[^\n.;]{0,24}\b(?:is\s+not|not\s+required|not\s+needed|not\s+mandatory|không\s+cần|khong\s+can)\b/iu';
        preg_match_all($positive, $scopeLower, $occurrences, PREG_OFFSET_CAPTURE);
        if ($occurrences[0] === []) {
            return false;
        }
        foreach ($occurrences[0] as [$match, $offset]) {
            $before = substr($scopeLower, 0, (int) $offset);
            $after = substr($scopeLower, (int) $offset + strlen((string) $match));
            if (preg_match($negatedBefore, $before) !== 1 && preg_match($negatedAfter, $after) !== 1) {
                return false;
            }
        }

        return true;
    }

    private function detectRole(string $source, ?string $metadataRole): ?string
    {
        if ($metadataRole !== null) {
            return $metadataRole;
        }
        if (preg_match('/(?:looking for|seeking|position|role|vị trí)\s*[:\-]?\s*([^\n.]{2,120})/iu', $source, $matches) === 1) {
            $value = JobDescriptionValidator::trimUnicode($matches[1]);

            return $value !== '' ? $value : null;
        }
        foreach (['frontend engineer', 'backend engineer', 'software engineer', 'full stack developer', 'data analyst', 'product designer'] as $candidate) {
            if (stripos($source, $candidate) !== false) {
                return ucwords($candidate);
            }
        }

        return null;
    }

    /** @return array{state:string,value:string|null} */
    private function detectSeniority(string $lower): array
    {
        $matches = [
            'intern' => ['intern', 'internship', 'thực tập'], 'junior' => ['junior', 'fresher', 'entry level', 'mới tốt nghiệp'],
            'mid' => ['mid-level', 'mid level', 'intermediate'], 'senior' => ['senior'], 'lead' => ['lead', 'principal', 'trưởng nhóm'],
        ];
        $detected = [];
        foreach ($matches as $level => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/(?<![\pL\pN])'.preg_quote($alias, '/').'(?!(?:[\pL\pN]))/iu', $lower) === 1 && ! $this->isNegated($lower, $alias)) {
                    $detected[$level] = true;
                }
            }
        }
        if (count($detected) === 1) {
            return ['state' => 'detected', 'value' => (string) array_key_first($detected)];
        }
        if (count($detected) > 1) {
            return ['state' => 'unknown', 'value' => null];
        }

        return ['state' => 'absent', 'value' => null];
    }

    /** @return array<int,string> */
    private function detectSoftSkills(string $lower): array
    {
        $terms = [
            'communication' => ['communication', 'communication skills'],
            'problem solving' => ['problem solving'],
            'collaboration' => ['collaboration'],
            'teamwork' => ['teamwork', 'làm việc nhóm'],
            'giao tiếp' => ['giao tiếp'],
        ];
        $found = [];
        foreach ($terms as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($lower, $alias)) {
                    $found[] = $canonical;
                    break;
                }
            }
        }
        sort($found);

        return array_values(array_unique($found));
    }

    /** @return array<int,string> */
    private function detectDomains(string $lower): array
    {
        $terms = ['fintech', 'healthcare', 'e-commerce', 'ecommerce', 'education', 'saas', 'web application', 'ứng dụng web'];
        $found = [];
        foreach ($terms as $term) {
            if (str_contains($lower, $term)) {
                $found[] = $term;
            }
        }
        sort($found);

        return array_values(array_unique($found));
    }

    /** @return array<int,string> */
    private function bullets(string $scope): array
    {
        $result = [];
        foreach (preg_split('/\R/u', $scope) ?: [] as $line) {
            $line = JobDescriptionValidator::trimUnicode((string) preg_replace('/^\s*(?:[-*•]|\d+[.)])\s*/u', '', $line));
            if ($line !== '' && mb_strlen($line, 'UTF-8') >= 3) {
                $result[] = $line;
            }
        }

        return array_values(array_slice(array_unique($result), 0, 30));
    }

    /** @return array{text:string,present:bool} */
    private function sectionInfo(string $source, array $headings, string $fallback): array
    {
        $lines = preg_split('/\R/u', $source) ?: [];
        $start = null;
        $inline = '';
        foreach ($lines as $index => $line) {
            $normalized = mb_strtolower(JobDescriptionValidator::trimUnicode((string) $line), 'UTF-8');
            foreach ($headings as $heading) {
                if (! str_starts_with($normalized, $heading)) {
                    continue;
                }
                $rest = mb_substr($normalized, mb_strlen($heading, 'UTF-8'), null, 'UTF-8');
                if ($normalized === $heading || preg_match('/^\s*(?::|-)\s*(.*)$/u', $rest, $suffix) === 1) {
                    $start = $index + 1;
                    $inline = JobDescriptionValidator::trimUnicode((string) ($suffix[1] ?? ''));
                    break 2;
                }
            }
        }
        if ($start === null) {
            return ['text' => $fallback, 'present' => false];
        }
        $end = count($lines);
        for ($index = $start; $index < count($lines); $index++) {
            $line = mb_strtolower(JobDescriptionValidator::trimUnicode((string) $lines[$index]), 'UTF-8');
            if ($line !== '' && preg_match('/^[#\[]?[\p{L}][\p{L} &-]{2,35}[:\]]?$/iu', $line) === 1 && $index > $start) {
                $end = $index;
                break;
            }
        }

        $parts = $inline === '' ? [] : [$inline];
        $parts[] = implode("\n", array_slice($lines, $start, $end - $start));

        return ['text' => implode("\n", $parts), 'present' => true];
    }

    private function section(string $source, array $headings, string $fallback): string
    {
        return $this->sectionInfo($source, $headings, $fallback)['text'];
    }

    /** @param array{text:string,present:bool} $section */
    private function unknownWhenUnmatched(array $section, array $items): bool
    {
        if (! $section['present'] || trim($section['text']) === '' || $items !== []) {
            return false;
        }

        // Explicitly negated requirements are a known absence, not an
        // unparseable section. Any other substantive text is unknown,
        // including unsupported requirements such as "Kubernetes needed".
        $lines = array_values(array_filter(preg_split('/\R/u', $section['text']) ?: [], static fn (string $line): bool => trim($line) !== ''));
        foreach ($lines as $line) {
            if (preg_match('/\b(?:no|without|does\s+not\s+require|not\s+(?:required|needed|mandatory)|không\s+cần|khong\s+can)\b/iu', $line) === 1) {
                continue;
            }

            return true;
        }

        return false;
    }

    /** @param array<int,mixed> $items */
    private function stateful(array $items, bool $unknown = false): array
    {
        return ['state' => $items !== [] ? 'detected' : ($unknown ? 'unknown' : 'absent'), 'items' => array_values($items)];
    }

    /** @param array<int,string> $items */
    private function statefulStrings(array $items, bool $unknown = false): array
    {
        return ['state' => $items !== [] ? 'detected' : ($unknown ? 'unknown' : 'absent'), 'items' => array_values($items)];
    }
}
