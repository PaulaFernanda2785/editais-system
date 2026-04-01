param(
    [int] $EmpresaId = 0,
    [int] $Limite = 0,
    [switch] $SemEmail,
    [string] $PhpPath = "php",
    [string] $ScriptPath = "scripts/processar-alertas-proativos.php"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-PhpPath {
    param([string] $Candidate)

    if (Test-Path $Candidate) {
        return (Resolve-Path $Candidate).Path
    }

    $cmd = Get-Command $Candidate -ErrorAction SilentlyContinue
    if ($null -ne $cmd) {
        return $cmd.Source
    }

    $roots = @("D:\\wamp64\\bin\\php", "C:\\wamp64\\bin\\php")
    foreach ($root in $roots) {
        if (-not (Test-Path $root)) {
            continue
        }

        $versions = Get-ChildItem -Path $root -Directory
        $patterns = @("php8.2*", "php8.3*", "php8.4*", "php8.1*", "php8.5*", "*")
        foreach ($pattern in $patterns) {
            $ordered = $versions | Where-Object { $_.Name -like $pattern } | Sort-Object Name -Descending
            foreach ($version in $ordered) {
                $phpExe = Join-Path $version.FullName "php.exe"
                if (Test-Path $phpExe) {
                    return $phpExe
                }
            }
        }
    }

    throw "Nao foi possivel localizar o executavel do PHP. Informe -PhpPath com o caminho completo."
}

if (-not (Test-Path $ScriptPath)) {
    throw "Script nao encontrado: $ScriptPath"
}

$phpExe = Resolve-PhpPath -Candidate $PhpPath
$scriptAbsolute = (Resolve-Path $ScriptPath).Path

$args = @($scriptAbsolute)
if ($EmpresaId -gt 0) {
    $args += "--empresa=$EmpresaId"
}
if ($Limite -gt 0) {
    $args += "--limite=$Limite"
}
if ($SemEmail.IsPresent) {
    $args += "--sem-email"
}

Write-Host "Executando processamento de alertas proativos..." -ForegroundColor Cyan
& $phpExe @args

if ($LASTEXITCODE -ne 0) {
    throw "O processamento de alertas finalizou com erro (codigo $LASTEXITCODE)."
}

Write-Host "Processamento concluido com sucesso." -ForegroundColor Green
