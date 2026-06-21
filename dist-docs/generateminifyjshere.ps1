# INTER-Mediator Deployment File Set Builder
# Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
# This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
#
# INTER-Mediator is supplied under MIT License.
# Please see the full license for details:
# https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
#
# PowerShell port of generateminifyjshere.sh so the minified JS can be built on Windows.

$ErrorActionPreference = 'Stop'

$version = '14'
# UTF-8 without BOM, so the generated JS keeps the same bytes as the shell version.
$script:utf8NoBom = New-Object System.Text.UTF8Encoding($false)

# $SourcePath: source file to merge. Appends its content to $script:tempJs,
# stopping at the "@@IM@@IgnoringRestOfFile" marker and dropping any
# "@@IM@@IgnoringNextLine" marker together with the line right after it.
function Add-FileUntilMark {
    param([Parameter(Mandatory = $true)][string]$SourcePath)

    if (-not (Test-Path -LiteralPath $SourcePath)) {
        Write-Host "  Skipped (not found): $SourcePath"
        return
    }

    $sb = New-Object System.Text.StringBuilder
    $skipNext = $false
    foreach ($line in [System.IO.File]::ReadAllLines($SourcePath)) {
        if ($line.Contains('@@IM@@IgnoringRestOfFile')) { break }
        if ($skipNext) { $skipNext = $false; continue }
        if ($line.Contains('@@IM@@IgnoringNextLine')) { $skipNext = $true; continue }
        [void]$sb.Append($line)
        [void]$sb.Append("`n")
    }
    [System.IO.File]::AppendAllText($script:tempJs, $sb.ToString(), $script:utf8NoBom)
}

$distDocDir = $PSScriptRoot
$imRoot = Split-Path -Parent $distDocDir
if ((Split-Path -Leaf $imRoot) -ne 'inter-mediator') {
    Write-Host "This command works on just composer installed inter-mediator."
    exit 1
}
$imRootOver = Split-Path -Parent $imRoot
$vendorDir = Split-Path -Parent $imRootOver
if ((Split-Path -Leaf $vendorDir) -ne 'vendor') {
    Write-Host "This command works on just composer installed inter-mediator."
    exit 1
}

$MINIFYJS = 'minify'
$minifyjsDir = Join-Path $vendorDir "matthiasmullie/$MINIFYJS"
$minifyjsBin = Join-Path $vendorDir "bin/${MINIFYJS}js"
if ((Test-Path -LiteralPath $minifyjsDir) -and (Test-Path -LiteralPath $minifyjsBin)) {
    Write-Host " Path of minifyer (installed by composer): $minifyjsDir"
} else {
    Write-Host "*** Minifyer isn't exist. ***"
    exit 1
}

#### Merge js files
Write-Host "PROCESSING: Merging JS files"
$script:tempJs = Join-Path $imRoot 'src/js/temp.js'
[System.IO.File]::WriteAllText($script:tempJs, "/*! INTER-Mediator Ver.$version https://inter-mediator.com/ */`n", $script:utf8NoBom)

Add-FileUntilMark (Join-Path $imRoot 'node_modules/socket.io-client/dist/socket.io.js')
[System.IO.File]::AppendAllText($script:tempJs, "`n", $script:utf8NoBom)
Add-FileUntilMark (Join-Path $imRoot 'node_modules/jssha/dist/sha.js')
Add-FileUntilMark (Join-Path $imRoot 'node_modules/inter-mediator-formatter/index.js')
Add-FileUntilMark (Join-Path $imRoot 'node_modules/inter-mediator-nodegraph/index.js')
Add-FileUntilMark (Join-Path $imRoot 'node_modules/inter-mediator-queue/index.js')
Add-FileUntilMark (Join-Path $imRoot 'node_modules/inter-mediator-expressionparser/index.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Page.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Auth.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-AuthUI.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-ContextPool.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Context.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-LocalContext.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Lib.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Element.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Calc.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/Adapter_DBServer.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Navi.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-UI.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Log.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-Events.js')
Add-FileUntilMark (Join-Path $imRoot 'src/js/INTER-Mediator-DoOnStart.js')

#### Compress INTER-Mediator.js
$minOut = Join-Path $imRoot 'src/js/INTER-Mediator.min.js'
if (Test-Path -LiteralPath $minifyjsDir) {
    Write-Host "MINIFYING."
    $minErr = [System.IO.Path]::GetTempFileName()
    # Invoke the composer-installed minifier through PHP and capture its stdout
    # straight into the .min.js file (the bin file is a PHP proxy without a
    # ".bat", so it cannot be launched directly on Windows).
    $minProc = Start-Process -FilePath 'php' `
        -ArgumentList ('"{0}" "{1}"' -f $minifyjsBin, $script:tempJs) `
        -NoNewWindow -Wait -PassThru `
        -RedirectStandardOutput $minOut `
        -RedirectStandardError $minErr
    if ($minProc.ExitCode -ne 0) {
        Write-Host "*** Minify failed (exit code $($minProc.ExitCode)). ***"
        if (Test-Path -LiteralPath $minErr) { Get-Content -LiteralPath $minErr | Write-Host }
        Remove-Item -LiteralPath $minErr -Force -ErrorAction SilentlyContinue
        exit 1
    }
    Remove-Item -LiteralPath $minErr -Force -ErrorAction SilentlyContinue
    [System.IO.File]::AppendAllText($minOut, "`n", $script:utf8NoBom)
}
Remove-Item -LiteralPath $script:tempJs -Force
