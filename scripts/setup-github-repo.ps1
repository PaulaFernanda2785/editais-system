param(
    [string]$RemoteUrl = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Step($message) {
    Write-Host "[GIT-SETUP] $message" -ForegroundColor Cyan
}

try {
    $projectRoot = Split-Path -Parent $PSScriptRoot
    Set-Location $projectRoot

    if (-not (Test-Path -LiteralPath (Join-Path $projectRoot '.git'))) {
        Step "Inicializando repositorio Git..."
        git init -b main | Out-Host
    }
    else {
        Step "Repositorio Git ja inicializado."
        git branch -M main | Out-Host
    }

    Step "Instalando hooks de pre-commit..."
    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\install-git-hooks.ps1"
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao instalar hooks."
    }

    if ($RemoteUrl -ne "") {
        $originExists = git remote | Select-String -Pattern '^origin$' -Quiet
        if ($originExists) {
            Step "Atualizando remote origin..."
            git remote set-url origin $RemoteUrl | Out-Host
        }
        else {
            Step "Adicionando remote origin..."
            git remote add origin $RemoteUrl | Out-Host
        }
    }
    else {
        Step "Remote nao informado. Use -RemoteUrl para conectar ao GitHub."
    }

    Step "Setup concluido."
    Write-Host "Proximos passos:" -ForegroundColor Green
    Write-Host "1) git add ." -ForegroundColor Green
    Write-Host "2) git commit -m ""chore: bootstrap git e seguranca de segredos""" -ForegroundColor Green
    if ($RemoteUrl -ne "") {
        Write-Host "3) git push -u origin main" -ForegroundColor Green
    }
    else {
        Write-Host "3) execute novamente com -RemoteUrl https://github.com/<usuario>/<repo>.git" -ForegroundColor Green
    }
}
catch {
    Write-Host "[GIT-SETUP] Erro: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
