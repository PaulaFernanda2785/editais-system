param(
    [Parameter(Mandatory = $true)]
    [string] $DbHost,

    [Parameter(Mandatory = $true)]
    [string] $Database,

    [Parameter(Mandatory = $true)]
    [string] $User,

    [string] $Port = "3306",
    [string] $Password = "",
    [string] $MysqlPath = "mysql",
    [string] $MigrationPath = "database/migrations/20260331_004_envio_controlado_propostas.sql"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-MysqlPath {
    param([string] $Candidate)

    if (Test-Path $Candidate) {
        return (Resolve-Path $Candidate).Path
    }

    $cmd = Get-Command $Candidate -ErrorAction SilentlyContinue
    if ($null -ne $cmd) {
        return $cmd.Source
    }

    throw "Nao foi possivel localizar o executavel do mysql. Informe -MysqlPath com o caminho completo."
}

if (-not (Test-Path $MigrationPath)) {
    throw "Arquivo de migration nao encontrado: $MigrationPath"
}

$mysqlExe = Resolve-MysqlPath -Candidate $MysqlPath
$migrationSql = Get-Content -Raw $MigrationPath

Write-Host ("Aplicando migration 004 em {0}:{1}/{2} ..." -f $DbHost, $Port, $Database) -ForegroundColor Cyan

$env:MYSQL_PWD = $Password
try {
    $migrationSql | & $mysqlExe -h $DbHost -P $Port -u $User $Database
}
finally {
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
}

Write-Host "Migration aplicada. Validando estruturas..." -ForegroundColor Cyan

$checkQuery = @"
SHOW TABLES LIKE 'proposta_aprovacoes';
SHOW TABLES LIKE 'proposta_submissoes';
DESCRIBE proposta_aprovacoes;
DESCRIBE proposta_submissoes;
"@

$env:MYSQL_PWD = $Password
try {
    $checkQuery | & $mysqlExe -h $DbHost -P $Port -u $User $Database
}
finally {
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
}

Write-Host "Concluido com sucesso." -ForegroundColor Green
