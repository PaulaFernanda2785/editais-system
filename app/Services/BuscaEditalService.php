<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EditalRepository;

class BuscaEditalService
{
    private EditalRepository $editalRepository;

    public function __construct(?EditalRepository $editalRepository = null)
    {
        $this->editalRepository = $editalRepository ?? new EditalRepository();
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *     items: array<int, \App\Models\Edital>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     total_pages: int,
     *     sort: string,
     *     filters: array<string, mixed>
     * }
     */
    public function buscar(array $input): array
    {
        $filters = $this->normalizarFiltros($input);
        $page = $this->sanitizeInt($input['page'] ?? 1, 1, 100000, 1);
        $perPage = $this->sanitizeInt($input['per_page'] ?? 20, 5, 100, 20);
        $sort = $this->normalizarSort((string) ($input['sort'] ?? 'data_publicacao_desc'));

        $resultado = $this->editalRepository->search($filters, $page, $perPage, $sort);
        $resultado['sort'] = $sort;
        $resultado['filters'] = $filters;

        return $resultado;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalizarFiltros(array $input): array
    {
        $uf = strtoupper(trim((string) ($input['uf'] ?? '')));
        if (strlen($uf) > 2) {
            $uf = substr($uf, 0, 2);
        }

        return [
            'termo' => $this->truncate(trim((string) ($input['termo'] ?? '')), 140),
            'uf' => $uf,
            'orgao_nome' => $this->truncate(trim((string) ($input['orgao_nome'] ?? '')), 180),
            'modalidade' => $this->truncate(trim((string) ($input['modalidade'] ?? '')), 120),
            'fonte_id' => $this->sanitizeInt($input['fonte_id'] ?? 0, 0, 10000000, 0),
            'data_publicacao_de' => $this->normalizarData((string) ($input['data_publicacao_de'] ?? '')),
            'data_publicacao_ate' => $this->normalizarData((string) ($input['data_publicacao_ate'] ?? '')),
            'valor_min' => $this->normalizarDecimal($input['valor_min'] ?? null),
            'valor_max' => $this->normalizarDecimal($input['valor_max'] ?? null),
        ];
    }

    private function normalizarSort(string $sort): string
    {
        $permitidos = [
            'data_publicacao_desc',
            'data_publicacao_asc',
            'data_abertura_desc',
            'valor_desc',
            'valor_asc',
            'relevancia_desc',
        ];

        return in_array($sort, $permitidos, true) ? $sort : 'data_publicacao_desc';
    }

    private function normalizarData(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date === false) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function normalizarDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = str_replace(['.', ','], ['', '.'], (string) $value);
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function sanitizeInt(mixed $value, int $min, int $max, int $default): int
    {
        if (!is_numeric($value)) {
            return $default;
        }

        $int = (int) $value;
        if ($int < $min) {
            return $min;
        }

        if ($int > $max) {
            return $max;
        }

        return $int;
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit < 1 || strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }
}

