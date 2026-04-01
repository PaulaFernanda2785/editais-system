param(
    [ValidateSet("register", "unregister", "run", "status")]
    [string] $Action = "register",
    [string] $TaskName = "Editais-Alertas-Proativos",
    [int] $IntervalMinutes = 15,
    [int] $EmpresaId = 0,
    [switch] $SemEmail,
    [string] $PhpPath = "D:\\wamp64\\bin\\php\\php8.5.0\\php.exe"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-ProjectRoot {
    $scriptDir = Split-Path -Path $PSCommandPath -Parent
    return (Resolve-Path (Join-Path $scriptDir "..")).Path
}

function Build-RunnerCommand {
    param(
        [string] $ProjectRootPath,
        [int] $EmpresaIdValue,
        [switch] $SemEmailValue,
        [string] $PhpPathValue
    )

    $cliScript = Join-Path $ProjectRootPath "scripts\\alertas-proativos-cli.php"

    $parts = @()
    $parts += "`"$PhpPathValue`""
    $parts += "`"$cliScript`""

    if ($EmpresaIdValue -gt 0) {
        $parts += "--empresa=$EmpresaIdValue"
    }
    if ($SemEmailValue.IsPresent) {
        $parts += "--sem-email"
    }

    return ($parts -join " ")
}

function Register-Task {
    param(
        [string] $Task,
        [int] $Interval,
        [string] $RunnerCommand
    )

    if ($Interval -lt 5) {
        throw "IntervalMinutes deve ser no minimo 5."
    }

    schtasks /Create /TN $Task /SC MINUTE /MO $Interval /TR "`"$RunnerCommand`"" /F | Out-Null
}

function Unregister-Task {
    param([string] $Task)
    schtasks /Delete /TN $Task /F | Out-Null
}

function Run-TaskNow {
    param([string] $Task)
    schtasks /Run /TN $Task | Out-Null
}

function Show-TaskStatus {
    param([string] $Task)
    schtasks /Query /TN $Task /V /FO LIST
}

$projectRoot = Resolve-ProjectRoot
$runnerCommand = Build-RunnerCommand `
    -ProjectRootPath $projectRoot `
    -EmpresaIdValue $EmpresaId `
    -SemEmailValue:$SemEmail `
    -PhpPathValue $PhpPath

switch ($Action) {
    "register" {
        Register-Task -Task $TaskName -Interval $IntervalMinutes -RunnerCommand $runnerCommand
        Write-Host "Tarefa registrada: $TaskName" -ForegroundColor Green
        Show-TaskStatus -Task $TaskName
        break
    }
    "unregister" {
        Unregister-Task -Task $TaskName
        Write-Host "Tarefa removida: $TaskName" -ForegroundColor Yellow
        break
    }
    "run" {
        Run-TaskNow -Task $TaskName
        Write-Host "Execucao disparada para a tarefa: $TaskName" -ForegroundColor Cyan
        break
    }
    "status" {
        Show-TaskStatus -Task $TaskName
        break
    }
}
