<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DomainArchitectureTest extends TestCase
{
    /**
     * Asserts that the Domain Layer (app/Domain) has ZERO dependencies
     * on Laravel (Illuminate), Filament, or Infrastructure.
     */
    public function test_domain_layer_does_not_depend_on_framework_or_infrastructure(): void
    {
        $domainPath = dirname(__DIR__, 2).'/app/Domain';
        $forbiddenNamespaces = [
            'Illuminate\\',
            'Filament\\',
            'App\\Infrastructure\\',
            'App\\Models\\',
            'App\\Filament\\',
            'Livewire\\',
        ];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($domainPath));

        $violations = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, 'use ')) {
                    foreach ($forbiddenNamespaces as $forbidden) {
                        if (str_contains($trimmed, $forbidden)) {
                            $violations[] = sprintf(
                                '%s (Line %d): Forbidden import "%s"',
                                $file->getFilename(),
                                $lineNum + 1,
                                $forbidden
                            );
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Architecture Violation: Domain Layer contains forbidden framework dependencies:\n".implode("\n", $violations)
        );
    }
}
