$hosts = 'C:\Windows\System32\drivers\etc\hosts'
$entries = @(
    '127.0.0.1 editais.local',
    '127.0.0.1 www.editais.local',
    '127.0.0.1 editais.paulafernandacl.com',
    '127.0.0.1 www.editais.paulafernandacl.com'
)

$current = Get-Content $hosts -ErrorAction Stop
$toAdd = @()
foreach ($entry in $entries) {
    if ($current -notcontains $entry) {
        $toAdd += $entry
    }
}

if ($toAdd.Count -gt 0) {
    Add-Content -Path $hosts -Value "`r`n# editais-system`r`n$($toAdd -join "`r`n")" -Encoding ASCII
    Write-Output 'HOSTS_UPDATED'
} else {
    Write-Output 'HOSTS_ALREADY_OK'
}
