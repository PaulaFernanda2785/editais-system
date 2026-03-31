Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

try {
    $repoRoot = git rev-parse --show-toplevel 2>$null
    if (-not $repoRoot) {
        throw "Repositorio Git nao detectado. Execute 'git init' antes."
    }

    Set-Location $repoRoot
    $hooksDir = Join-Path $repoRoot '.git/hooks'
    if (-not (Test-Path -LiteralPath $hooksDir)) {
        New-Item -ItemType Directory -Path $hooksDir -Force | Out-Null
    }

    $hookFile = Join-Path $hooksDir 'pre-commit'
    $hookBody = @'
#!/bin/sh
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "./scripts/pre-commit.ps1"
exit $?
'@

    Set-Content -LiteralPath $hookFile -Value $hookBody -Encoding ASCII
    Write-Host "[HOOKS] pre-commit instalado em $hookFile" -ForegroundColor Green
    exit 0
}
catch {
    Write-Host "[HOOKS] Falha ao instalar hooks: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
