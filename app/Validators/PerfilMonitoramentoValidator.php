<?php

declare(strict_types=1);

namespace App\Validators;

class PerfilMonitoramentoValidator
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
        $frequencia = strtoupper(trim((string) ($payload['frequencia_alerta'] ?? 'DIARIO')));

        $ufsTexto = (string) ($payload['ufs'] ?? '');
        $modalidadesTexto = (string) ($payload['modalidades'] ?? '');
        $orgaosTexto = (string) ($payload['orgaos'] ?? '');

        $ufs = $this->parseList($ufsTexto);
        $modalidades = $this->parseList($modalidadesTexto);
        $orgaos = $this->parseList($orgaosTexto);

        if ($nome === '') {
            $errors['nome'] = 'Informe o nome do perfil.';
        } elseif (strlen($nome) > 120) {
            $errors['nome'] = 'Nome do perfil excede 120 caracteres.';
        }

        $frequenciasPermitidas = ['IMEDIATO', 'DIARIO', 'SEMANAL'];
        if (!in_array($frequencia, $frequenciasPermitidas, true)) {
            $errors['frequencia_alerta'] = 'Frequencia de alerta invalida.';
        }

        foreach ($ufs as $uf) {
            if (!preg_match('/^[A-Z]{2}$/', $uf)) {
                $errors['ufs'] = 'UFs devem conter apenas siglas com 2 letras, separadas por virgula.';
                break;
            }
        }

        if (count($modalidades) > 40) {
            $errors['modalidades'] = 'Limite maximo de modalidades por perfil: 40.';
        }

        if (count($orgaos) > 60) {
            $errors['orgaos'] = 'Limite maximo de orgaos por perfil: 60.';
        }

        $faixaMin = $this->normalizeDecimal($payload['faixa_valor_min'] ?? null);
        $faixaMax = $this->normalizeDecimal($payload['faixa_valor_max'] ?? null);

        if (($payload['faixa_valor_min'] ?? '') !== '' && $faixaMin === null) {
            $errors['faixa_valor_min'] = 'Faixa minima invalida.';
        }

        if (($payload['faixa_valor_max'] ?? '') !== '' && $faixaMax === null) {
            $errors['faixa_valor_max'] = 'Faixa maxima invalida.';
        }

        if ($faixaMin !== null && $faixaMin < 0) {
            $errors['faixa_valor_min'] = 'Faixa minima nao pode ser negativa.';
        }

        if ($faixaMax !== null && $faixaMax < 0) {
            $errors['faixa_valor_max'] = 'Faixa maxima nao pode ser negativa.';
        }

        if ($faixaMin !== null && $faixaMax !== null && $faixaMin > $faixaMax) {
            $errors['faixa_valor_min'] = 'Faixa minima deve ser menor ou igual a faixa maxima.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => [
                'nome' => $nome,
                'ufs_json' => array_map(static fn(string $uf): string => strtoupper($uf), $ufs),
                'modalidades_json' => $modalidades,
                'orgaos_json' => $orgaos,
                'faixa_valor_min' => $faixaMin,
                'faixa_valor_max' => $faixaMax,
                'frequencia_alerta' => $frequencia,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,;]+/', $value);
        if (!is_array($parts)) {
            return [];
        }

        $clean = [];
        foreach ($parts as $part) {
            $item = trim($part);
            if ($item === '') {
                continue;
            }
            $clean[] = $item;
        }

        return array_values(array_unique($clean));
    }

    private function normalizeDecimal(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $text);
        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
