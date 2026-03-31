<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Edital;
use App\Models\PalavraChave;
use App\Models\PerfilMonitoramento;

class ScoreService
{
    /**
     * @param array<int, PalavraChave> $palavras
     * @return array<string, mixed>
     */
    public function calcular(Edital $edital, PerfilMonitoramento $perfil, array $palavras): array
    {
        if (!$this->editalCompativelComPerfil($edital, $perfil)) {
            return [
                'score' => 0.0,
                'nivel_relevancia' => 'BAIXA',
                'motivo' => [
                    'filtros' => 'nao_compativel',
                    'palavras_encontradas' => [],
                    'detalhes' => [],
                ],
            ];
        }

        $textoBase = $this->normalizarTexto(
            implode(' ', [
                $edital->numeroEdital ?? '',
                $edital->orgaoNome,
                $edital->objeto,
                $edital->descricaoResumida ?? '',
                $edital->modalidade ?? '',
                $edital->municipio ?? '',
            ])
        );

        $score = 0.0;
        $palavrasEncontradas = [];
        $detalhes = [];

        foreach ($palavras as $palavra) {
            if (!$palavra->ativo) {
                continue;
            }

            $termo = $this->normalizarTexto($palavra->termo);
            if ($termo === '') {
                continue;
            }

            $ocorrencias = $this->contarOcorrencias($textoBase, $termo);
            if ($ocorrencias < 1) {
                continue;
            }

            $peso = max(1, $palavra->peso);
            $fatorOcorrencia = min($ocorrencias, 4);
            $incremento = $peso * 7 * $fatorOcorrencia;
            $score += $incremento;

            $palavrasEncontradas[] = $palavra->termo;
            $detalhes[] = [
                'termo' => $palavra->termo,
                'peso' => $peso,
                'ocorrencias' => $ocorrencias,
                'incremento' => $incremento,
            ];
        }

        if ($score <= 0) {
            return [
                'score' => 0.0,
                'nivel_relevancia' => 'BAIXA',
                'motivo' => [
                    'filtros' => 'compativel',
                    'palavras_encontradas' => [],
                    'detalhes' => [],
                ],
            ];
        }

        if ($edital->valorEstimado !== null && $edital->valorEstimado > 0) {
            $score += min(12, max(0, $edital->valorEstimado / 250000));
        }

        $score = min(100.0, round($score, 2));
        $nivel = $this->nivelPorScore($score);

        return [
            'score' => $score,
            'nivel_relevancia' => $nivel,
            'motivo' => [
                'filtros' => 'compativel',
                'palavras_encontradas' => array_values(array_unique($palavrasEncontradas)),
                'detalhes' => $detalhes,
            ],
        ];
    }

    private function editalCompativelComPerfil(Edital $edital, PerfilMonitoramento $perfil): bool
    {
        if (!$perfil->ativo) {
            return false;
        }

        if ($perfil->ufs !== [] && !$this->valorNaLista($edital->uf, $perfil->ufs, true)) {
            return false;
        }

        if ($perfil->modalidades !== [] && !$this->textoCompativelComLista($edital->modalidade, $perfil->modalidades)) {
            return false;
        }

        if ($perfil->orgaos !== [] && !$this->orgaoCompativel($edital->orgaoNome, $perfil->orgaos)) {
            return false;
        }

        if ($perfil->faixaValorMin !== null || $perfil->faixaValorMax !== null) {
            if ($edital->valorEstimado === null) {
                return false;
            }

            if ($perfil->faixaValorMin !== null && $edital->valorEstimado < $perfil->faixaValorMin) {
                return false;
            }

            if ($perfil->faixaValorMax !== null && $edital->valorEstimado > $perfil->faixaValorMax) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $lista
     */
    private function valorNaLista(?string $valor, array $lista, bool $caseInsensitive): bool
    {
        if ($valor === null) {
            return false;
        }

        $valor = trim($valor);
        if ($valor === '') {
            return false;
        }

        foreach ($lista as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            if ($caseInsensitive) {
                if (mb_strtoupper($item) === mb_strtoupper($valor)) {
                    return true;
                }
                continue;
            }

            if ($item === $valor) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $lista
     */
    private function textoCompativelComLista(?string $valor, array $lista): bool
    {
        if ($valor === null) {
            return false;
        }

        $valorNormalizado = $this->normalizarTexto($valor);
        if ($valorNormalizado === '') {
            return false;
        }

        foreach ($lista as $item) {
            $itemNormalizado = $this->normalizarTexto($item);
            if ($itemNormalizado === '') {
                continue;
            }

            if (str_contains($valorNormalizado, $itemNormalizado) || str_contains($itemNormalizado, $valorNormalizado)) {
                return true;
            }

            if ($this->temIntersecaoRelevante($valorNormalizado, $itemNormalizado)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $orgaos
     */
    private function orgaoCompativel(string $orgaoEdital, array $orgaos): bool
    {
        $alvo = $this->normalizarTexto($orgaoEdital);
        if ($alvo === '') {
            return false;
        }

        foreach ($orgaos as $orgao) {
            $filtro = $this->normalizarTexto($orgao);
            if ($filtro === '') {
                continue;
            }

            if (str_contains($alvo, $filtro) || str_contains($filtro, $alvo)) {
                return true;
            }

            if ($this->temIntersecaoRelevante($alvo, $filtro)) {
                return true;
            }
        }

        return false;
    }

    private function nivelPorScore(float $score): string
    {
        if ($score >= 80) {
            return 'PRIORITARIA';
        }

        if ($score >= 55) {
            return 'ALTA';
        }

        if ($score >= 30) {
            return 'MEDIA';
        }

        return 'BAIXA';
    }

    private function contarOcorrencias(string $textoBase, string $termo): int
    {
        if ($textoBase === '' || $termo === '') {
            return 0;
        }

        return substr_count($textoBase, $termo);
    }

    private function normalizarTexto(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }

        $valorAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if (is_string($valorAscii) && $valorAscii !== '') {
            $valor = $valorAscii;
        }

        $valor = mb_strtolower($valor);
        $valor = preg_replace('/[^a-z0-9\s]/', ' ', $valor);
        $valor = preg_replace('/\s+/', ' ', (string) $valor);
        if (!is_string($valor)) {
            return '';
        }

        return trim($valor);
    }

    private function temIntersecaoRelevante(string $textoA, string $textoB): bool
    {
        $tokensA = $this->tokensRelevantes($textoA);
        $tokensB = $this->tokensRelevantes($textoB);

        if ($tokensA === [] || $tokensB === []) {
            return false;
        }

        foreach ($tokensA as $tokenA) {
            if (in_array($tokenA, $tokensB, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function tokensRelevantes(string $valor): array
    {
        $partes = explode(' ', $valor);
        $tokens = [];

        foreach ($partes as $token) {
            $token = trim($token);
            if (strlen($token) < 5) {
                continue;
            }

            $tokens[] = $token;

            if (str_ends_with($token, 's') && strlen($token) >= 6) {
                $tokens[] = substr($token, 0, -1);
            }
        }

        return array_values(array_unique($tokens));
    }
}
