Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Fail {
    param([string]$Message)
    Write-Host "[SECRET-SCAN] $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "[SECRET-SCAN] $Message" -ForegroundColor Cyan
}

function Get-StagedFiles {
    $files = git -c core.quotepath=false diff --cached --name-only --diff-filter=ACMR 2>$null
    if (-not $files) {
        return @()
    }

    return $files |
        ForEach-Object { $_.Trim('"').Trim() } |
        Where-Object { $_ -and $_.Trim() -ne '' }
}

function Is-BinaryFile {
    param([string]$Path)

    $binaryExtensions = @(
        '.png', '.jpg', '.jpeg', '.gif', '.pdf', '.zip', '.gz',
        '.7z', '.exe', '.dll', '.so', '.jar', '.bin', '.ico'
    )

    $ext = [System.IO.Path]::GetExtension($Path).ToLowerInvariant()
    return $binaryExtensions -contains $ext
}

function Contains-SensitivePattern {
    param([string]$Content)

    $patterns = @(
        '(?im)^\s*(DB_PASSWORD|MYSQL_PASSWORD|MARIADB_PASSWORD)\s*=\s*.+$',
        '(?im)^\s*(MERCADO_PAGO_ACCESS_TOKEN|MERCADO_PAGO_PUBLIC_KEY|MERCADO_PAGO_WEBHOOK_SECRET)\s*=\s*.+$',
        '(?im)^\s*(API_KEY|API_SECRET|SECRET_KEY|ACCESS_TOKEN)\s*=\s*.+$',
        '(?i)-----BEGIN (RSA|OPENSSH|EC|DSA) PRIVATE KEY-----',
        '(?i)AKIA[0-9A-Z]{16}',
        '(?i)ghp_[A-Za-z0-9]{36,}'
    )

    foreach ($pattern in $patterns) {
        if ([regex]::IsMatch($Content, $pattern)) {
            return $true
        }
    }

    return $false
}

try {
    $repoRoot = git rev-parse --show-toplevel 2>$null
    if (-not $repoRoot) {
        Write-Info "Repositorio Git nao detectado. Scan ignorado."
        exit 0
    }

    Set-Location $repoRoot
    $staged = @(Get-StagedFiles)

    if ($staged.Count -eq 0) {
        Write-Info "Nenhum arquivo staged. Scan concluido."
        exit 0
    }

    $blockedNames = @(
        '.env',
        '.env.local',
        '.env.production',
        '.env.staging',
        'cookies.txt',
        'cookies_monitoramento.txt'
    )

    foreach ($file in $staged) {
        $normalized = $file.Replace('\', '/')
        if ($blockedNames -contains $normalized.ToLowerInvariant()) {
            Write-Fail "Arquivo sensivel staged: $file"
            Write-Fail "Use apenas .env.example no repositorio."
            exit 1
        }

        if ($normalized -match '(?i)\.(pem|key|p12|crt)$') {
            Write-Fail "Arquivo de certificado/chave staged: $file"
            exit 1
        }
    }

    $violations = @()
    foreach ($file in $staged) {
        try {
            $exists = Test-Path -LiteralPath $file
        }
        catch {
            Write-Info "Arquivo ignorado por caminho invalido no scanner: $file"
            continue
        }

        if (-not $exists) {
            continue
        }

        if (Is-BinaryFile -Path $file) {
            continue
        }

        if ($file -ieq '.env.example') {
            continue
        }

        $content = Get-Content -LiteralPath $file -Raw -ErrorAction SilentlyContinue
        if (-not $content) {
            continue
        }

        if (Contains-SensitivePattern -Content $content) {
            $violations += $file
        }
    }

    if ($violations.Count -gt 0) {
        Write-Fail "Possivel segredo detectado nos arquivos:"
        foreach ($v in $violations) {
            Write-Fail " - $v"
        }
        Write-Fail "Commit bloqueado. Remova/redija os dados sensiveis."
        exit 1
    }

    Write-Info "Scan de segredos finalizado sem bloqueios."
    exit 0
}
catch {
    Write-Fail "Falha no scan de segredos: $($_.Exception.Message)"
    exit 1
}
