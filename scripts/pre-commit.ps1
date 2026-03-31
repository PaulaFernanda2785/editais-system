Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Fail {
    param([string]$Message)
    Write-Host "[PRE-COMMIT] $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "[PRE-COMMIT] $Message" -ForegroundColor Green
}

function Get-PhpExecutable {
    $candidates = @(
        'D:\wamp64\bin\php\php8.4.15\php.exe',
        'D:\wamp64\bin\php\php8.3.28\php.exe',
        'D:\wamp64\bin\php\php8.2.29\php.exe'
    )

    foreach ($candidate in $candidates) {
        if (Test-Path -LiteralPath $candidate) {
            return $candidate
        }
    }

    $dynamic = Get-ChildItem -Path 'D:\wamp64\bin\php' -Directory -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending |
        ForEach-Object {
            $exe = Join-Path $_.FullName 'php.exe'
            if (Test-Path -LiteralPath $exe) { $exe }
        } |
        Select-Object -First 1

    return $dynamic
}

try {
    $repoRoot = git rev-parse --show-toplevel 2>$null
    if (-not $repoRoot) {
        Write-Info "Repositorio Git nao detectado. Hook ignorado."
        exit 0
    }

    Set-Location $repoRoot

    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\scan-secrets.ps1"
    if ($LASTEXITCODE -ne 0) {
        Write-Fail "Scan de segredos bloqueou o commit."
        exit 1
    }

    $php = Get-PhpExecutable
    if (-not $php) {
        Write-Info "PHP nao encontrado para lint. Commit segue apenas com scan de segredos."
        exit 0
    }

    $phpFiles = git diff --cached --name-only --diff-filter=ACMR |
        Where-Object { $_ -match '\.php$' -and (Test-Path -LiteralPath $_) }

    if (-not $phpFiles) {
        Write-Info "Nenhum arquivo PHP staged. Pre-commit OK."
        exit 0
    }

    foreach ($file in $phpFiles) {
        & $php -l $file | Out-Host
        if ($LASTEXITCODE -ne 0) {
            Write-Fail "Erro de sintaxe em $file"
            exit 1
        }
    }

    Write-Info "Pre-commit concluido com sucesso."
    exit 0
}
catch {
    Write-Fail "Falha no pre-commit: $($_.Exception.Message)"
    exit 1
}
