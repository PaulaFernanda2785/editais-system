<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\TenantMiddleware;
use App\Middlewares\AssinaturaMiddleware;
use App\Middlewares\AdminMiddleware;

$router->get('/', function (Request $request, Response $response): void {
    if ($request->session('auth.user_id') !== null) {
        $response->redirect('/dashboard');
        return;
    }

    $response->redirect('/login');
});

$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');

$router->get('/dashboard', 'DashboardController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/assinatura/status', 'AssinaturaController@status', [
    AuthMiddleware::class,
    TenantMiddleware::class,
]);
$router->post('/assinatura/ativar-teste', 'AssinaturaController@ativarTeste', [
    AuthMiddleware::class,
    TenantMiddleware::class,
]);

$router->get('/monitoramento', 'PerfilMonitoramentoController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/monitoramento/novo', 'PerfilMonitoramentoController@create', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento', 'PerfilMonitoramentoController@store', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/monitoramento/{id}/editar', 'PerfilMonitoramentoController@edit', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}', 'PerfilMonitoramentoController@update', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/toggle', 'PerfilMonitoramentoController@toggle', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/delete', 'PerfilMonitoramentoController@delete', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/palavras', 'PalavraChaveController@store', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/palavras/{palavraId}', 'PalavraChaveController@update', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/palavras/{palavraId}/toggle', 'PalavraChaveController@toggle', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/monitoramento/{id}/palavras/{palavraId}/delete', 'PalavraChaveController@delete', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/editais', 'EditalController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/editais/{id}', 'EditalController@show', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/oportunidades', 'CorrespondenciaController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/oportunidades/{id}', 'CorrespondenciaController@show', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/oportunidades/{id}/decidir', 'CorrespondenciaController@decidir', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/oportunidades/processar', 'CorrespondenciaController@processar', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/favoritos', 'FavoritoController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/favoritos/{id}/proposta/gerar', 'PropostaController@gerarPorFavorito', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/favoritos/relatorio/conversao', 'FavoritoController@relatorioConversao', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/favoritos/{id}', 'FavoritoController@show', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/favoritos/{id}/status', 'FavoritoController@updateStatus', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/favoritos/{id}/tarefas', 'FavoritoController@storeTarefa', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/favoritos/{id}/tarefas/{tarefaId}/status', 'FavoritoController@updateTarefaStatus', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/favoritos/{id}/tarefas/{tarefaId}/delete', 'FavoritoController@deleteTarefa', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/propostas', 'PropostaController@index', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->get('/propostas/{id}', 'PropostaController@show', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);
$router->post('/propostas/{id}', 'PropostaController@update', [
    AuthMiddleware::class,
    TenantMiddleware::class,
    AssinaturaMiddleware::class,
]);

$router->get('/fontes', 'FonteController@index', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->get('/fontes/novo', 'FonteController@create', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/fontes', 'FonteController@store', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->get('/fontes/{id}', 'FonteController@show', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->get('/fontes/{id}/editar', 'FonteController@edit', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/fontes/{id}', 'FonteController@update', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/fontes/{id}/toggle', 'FonteController@toggle', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);

$router->get('/admin/coletas', 'ColetaController@index', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->get('/admin/coletas/{id}', 'ColetaController@show', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/admin/coletas/pncp', 'ColetaController@executarPncp', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/admin/coletas/comprasgov', 'ColetaController@executarComprasGov', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);
$router->post('/admin/coletas/licitacoese', 'ColetaController@executarLicitacoesE', [
    AuthMiddleware::class,
    AdminMiddleware::class,
]);

$router->get('/logout', 'AuthController@showLogout', [AuthMiddleware::class]);
$router->post('/logout', 'AuthController@logout', [AuthMiddleware::class]);
