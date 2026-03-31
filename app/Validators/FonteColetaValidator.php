<?php

declare(strict_types=1);

namespace App\Validators;

class FonteColetaValidator
{
    /**
     * @return array{
     *     valid: bool,
     *     errors: array<string, string>,
     *     data: array<string, mixed>
     * }
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $nome = trim((string) ($payload['nome'] ?? ''));
        $codigo = strtoupper(trim((string) ($payload['codigo'] ?? '')));
        $tipo = strtoupper(trim((string) ($payload['tipo'] ?? 'API')));
        $urlBase = trim((string) ($payload['url_base'] ?? ''));
        $periodicidadeRaw = trim((string) ($payload['periodicidade_minutos'] ?? '60'));
        $configuracaoRaw = trim((string) ($payload['configuracao_json'] ?? ''));

        if ($nome === '') {
            $errors['nome'] = 'Informe o nome da fonte.';
        } elseif (strlen($nome) > 120) {
            $errors['nome'] = 'Nome da fonte excede 120 caracteres.';
        }

        if ($codigo === '') {
            $errors['codigo'] = 'Informe o codigo da fonte.';
        } elseif (!preg_match('/^[A-Z0-9_\-]{3,50}$/', $codigo)) {
            $errors['codigo'] = 'Codigo deve conter 3 a 50 caracteres alfanumericos, underscore ou hifen.';
        }

        if (!in_array($tipo, ['API', 'SCRAPING', 'MANUAL'], true)) {
            $errors['tipo'] = 'Tipo de fonte invalido.';
        }

        if ($urlBase !== '' && !filter_var($urlBase, FILTER_VALIDATE_URL)) {
            $errors['url_base'] = 'URL base invalida.';
        }

        if ($periodicidadeRaw === '' || !preg_match('/^\d+$/', $periodicidadeRaw)) {
            $errors['periodicidade_minutos'] = 'Periodicidade deve ser numero inteiro.';
            $periodicidade = 60;
        } else {
            $periodicidade = (int) $periodicidadeRaw;
            if ($periodicidade < 5 || $periodicidade > 10080) {
                $errors['periodicidade_minutos'] = 'Periodicidade deve estar entre 5 e 10080 minutos.';
            }
        }

        $configuracao = null;
        if ($configuracaoRaw !== '') {
            $decoded = json_decode($configuracaoRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                $errors['configuracao_json'] = 'Configuracao JSON invalida.';
            } else {
                $configuracao = $decoded;
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => [
                'nome' => $nome,
                'codigo' => $codigo,
                'tipo' => $tipo,
                'url_base' => $urlBase !== '' ? $urlBase : null,
                'periodicidade_minutos' => $periodicidade ?? 60,
                'configuracao_json' => $configuracao,
            ],
        ];
    }
}
