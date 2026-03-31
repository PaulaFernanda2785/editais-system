<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Edital;
use App\Repositories\EditalRepository;

class DeduplicacaoService
{
    private EditalRepository $editalRepository;

    public function __construct(?EditalRepository $editalRepository = null)
    {
        $this->editalRepository = $editalRepository ?? new EditalRepository();
    }

    /**
     * @param array<string, mixed> $registro
     */
    public function gerarHash(array $registro): string
    {
        $partes = [
            $this->normalizarTexto($registro['codigo_fonte'] ?? null),
            $this->normalizarTexto($registro['numero_edital'] ?? null),
            $this->normalizarTexto($registro['orgao_nome'] ?? null),
            $this->normalizarTexto($registro['objeto'] ?? null),
            $this->normalizarTexto($registro['data_abertura'] ?? null),
        ];

        return md5(implode('|', $partes));
    }

    /**
     * @param array<string, mixed> $registro
     * @return array{acao: string, edital: Edital|null}
     */
    public function decidirAcao(array $registro): array
    {
        $hashUnico = (string) ($registro['hash_unico'] ?? '');
        $existente = $hashUnico !== '' ? $this->editalRepository->findByHash($hashUnico) : null;

        if ($existente === null) {
            return [
                'acao' => 'INSERIR',
                'edital' => null,
            ];
        }

        if ($this->possuiMudancas($existente, $registro)) {
            return [
                'acao' => 'ATUALIZAR',
                'edital' => $existente,
            ];
        }

        return [
            'acao' => 'DUPLICADO',
            'edital' => $existente,
        ];
    }

    /**
     * @param array<string, mixed> $novo
     */
    private function possuiMudancas(Edital $existente, array $novo): bool
    {
        $checks = [
            'codigo_fonte' => $existente->codigoFonte,
            'numero_edital' => $existente->numeroEdital,
            'orgao_nome' => $existente->orgaoNome,
            'orgao_poder' => $existente->orgaoPoder,
            'esfera' => $existente->esfera,
            'uf' => $existente->uf,
            'municipio' => $existente->municipio,
            'modalidade' => $existente->modalidade,
            'modo_disputa' => $existente->modoDisputa,
            'objeto' => $existente->objeto,
            'descricao_resumida' => $existente->descricaoResumida,
            'valor_estimado' => $existente->valorEstimado,
            'data_publicacao' => $existente->dataPublicacao,
            'data_abertura' => $existente->dataAbertura,
            'data_encerramento' => $existente->dataEncerramento,
            'situacao' => $existente->situacao,
            'link_detalhe' => $existente->linkDetalhe,
            'link_edital' => $existente->linkEdital,
        ];

        foreach ($checks as $key => $antigo) {
            $novoValor = $novo[$key] ?? null;
            if ($this->normalizarEscalar($antigo) !== $this->normalizarEscalar($novoValor)) {
                return true;
            }
        }

        return false;
    }

    private function normalizarEscalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) || is_int($value)) {
            return number_format((float) $value, 4, '.', '');
        }

        return trim((string) $value);
    }

    private function normalizarTexto(mixed $value): string
    {
        $text = strtolower(trim((string) ($value ?? '')));
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\s+/', ' ', $text);
        return is_string($text) ? $text : '';
    }
}

