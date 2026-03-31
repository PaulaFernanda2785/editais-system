<?php

declare(strict_types=1);

namespace App\Integrations;

use App\Services\LogService;

class PncpIntegration implements EditalSourceIntegrationInterface
{
    private LogService $logService;

    public function __construct(?LogService $logService = null)
    {
        $this->logService = $logService ?? new LogService();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetch(array $params = []): array
    {
        $limit = isset($params['limit']) ? (int) $params['limit'] : 50;
        if ($limit < 1) {
            $limit = 50;
        }
        if ($limit > 500) {
            $limit = 500;
        }

        $config = isset($params['config']) && is_array($params['config']) ? $params['config'] : [];
        $url = (string) ($params['url'] ?? $config['api_url'] ?? '');

        if (isset($config['mock_payload_json']) && is_string($config['mock_payload_json'])) {
            $decoded = json_decode($config['mock_payload_json'], true);
            if (is_array($decoded)) {
                $records = $this->extractRecords($decoded);
                if ($records !== []) {
                    return array_slice($records, 0, $limit);
                }
            }
        }

        if ($url !== '') {
            $remote = $this->fetchFromRemote($url, $limit);
            if ($remote !== []) {
                return $remote;
            }
        }

        return $this->buildFallbackRecords($limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchFromRemote(string $baseUrl, int $limit): array
    {
        $url = $this->buildUrl($baseUrl, $limit);
        $timeout = isset($_ENV['COLETA_HTTP_TIMEOUT']) ? (int) $_ENV['COLETA_HTTP_TIMEOUT'] : 25;
        if ($timeout < 3) {
            $timeout = 25;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: SaaS-Editais-Coleta/1.0\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if (!is_string($raw) || trim($raw) === '') {
            $this->logService->warning('coleta.pncp.http', 'Resposta vazia na coleta PNCP.', [
                'url' => $url,
            ]);
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $this->logService->warning('coleta.pncp.http', 'Payload invalido na coleta PNCP.', [
                'url' => $url,
                'amostra' => substr($raw, 0, 200),
            ]);
            return [];
        }

        $records = $this->extractRecords($decoded);
        if ($records === []) {
            $this->logService->warning('coleta.pncp.http', 'Sem registros mapeaveis no payload PNCP.', [
                'url' => $url,
            ]);
            return [];
        }

        return array_slice($records, 0, $limit);
    }

    private function buildUrl(string $baseUrl, int $limit): string
    {
        if (str_contains($baseUrl, '{limit}')) {
            return str_replace('{limit}', (string) $limit, $baseUrl);
        }

        if (!str_contains($baseUrl, '?')) {
            return rtrim($baseUrl, '/') . '?pagina=1&tamanhoPagina=' . $limit;
        }

        return $baseUrl . '&pagina=1&tamanhoPagina=' . $limit;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function extractRecords(array $payload): array
    {
        if (isset($payload[0]) && is_array($payload[0])) {
            return $payload;
        }

        foreach (['data', 'items', 'resultados', 'results'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_values(
                    array_filter(
                        $payload[$key],
                        static fn(mixed $item): bool => is_array($item)
                    )
                );
            }
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFallbackRecords(int $limit): array
    {
        $today = date('Y-m-d');
        $records = [];

        for ($i = 1; $i <= $limit; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $records[] = [
                'codigo_fonte' => 'PNCP-MOCK-' . $today . '-' . $seq,
                'numero_edital' => 'PNCP/' . date('Y') . '/' . $seq,
                'orgao_nome' => 'Orgao Publico Exemplo ' . $i,
                'orgao_poder' => 'EXECUTIVO',
                'esfera' => $i % 2 === 0 ? 'MUNICIPAL' : 'ESTADUAL',
                'uf' => $i % 2 === 0 ? 'CE' : 'SP',
                'municipio' => $i % 2 === 0 ? 'Fortaleza' : 'Sao Paulo',
                'modalidade' => $i % 3 === 0 ? 'PREGAO ELETRONICO' : 'CONCORRENCIA',
                'modo_disputa' => 'ABERTO',
                'objeto' => 'Registro de precos para contratacao de servicos de tecnologia da informacao lote ' . $i,
                'descricao_resumida' => 'Edital capturado em modo fallback para validacao operacional.',
                'valor_estimado' => 25000 + ($i * 1750),
                'data_publicacao' => $today,
                'data_abertura' => date('Y-m-d 09:00:00', strtotime('+' . ($i % 5) . ' day')),
                'data_encerramento' => date('Y-m-d 18:00:00', strtotime('+' . (($i % 5) + 7) . ' day')),
                'situacao' => 'PUBLICADO',
                'link_detalhe' => 'https://pncp.gov.br/app/editais/' . $seq,
                'link_edital' => 'https://pncp.gov.br/app/editais/' . $seq . '/arquivo',
            ];
        }

        $this->logService->warning('coleta.pncp.fallback', 'Coleta PNCP executada com dados fallback.', [
            'limit' => $limit,
        ]);

        return $records;
    }
}

