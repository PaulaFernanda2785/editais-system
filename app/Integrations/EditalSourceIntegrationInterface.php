<?php

declare(strict_types=1);

namespace App\Integrations;

interface EditalSourceIntegrationInterface
{
    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetch(array $params = []): array;
}

