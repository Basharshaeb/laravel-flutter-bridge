<?php

namespace LaravelFlutter\Generator\Contracts;

interface AnalyzerInterface
{
    /**
     * Analyze the given subject and return structured data.
     *
     * @param mixed $subject The subject to analyze
     * @return array The analyzed data
     */
    public function analyze($subject): array;

    /**
     * Check if the analyzer can handle the given subject.
     *
     * @param mixed $subject The subject to check
     * @return bool True if the analyzer can handle the subject
     */
    public function canAnalyze($subject): bool;
}
