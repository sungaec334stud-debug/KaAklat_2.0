<?php
declare(strict_types=1);
// PowerShell supplies only the password through a private pipe, never argv.
function operator_password(): string
{
    if (PHP_SAPI!=='cli' || PHP_OS_FAMILY!=='Windows') { throw new RuntimeException('Use the documented Windows local operator workflow.'); }
    fwrite(STDERR,"Enter password privately (12–72 bytes), then press Enter: ");
    $script='[Console]::OutputEncoding=[System.Text.UTF8Encoding]::new($false); if ([Console]::IsInputRedirected) { throw "An interactive terminal is required." }; $p=New-Object Security.SecureString; while($true) { $k=[Console]::ReadKey($true); if($k.Key -eq "Enter") { break }; if($k.Key -eq "Backspace") { if($p.Length -gt 0) { $p.RemoveAt($p.Length-1) }; continue }; if(-not [char]::IsControl($k.KeyChar)) { $p.AppendChar($k.KeyChar) } }; $v=[System.Net.NetworkCredential]::new("",$p); [Console]::Out.Write($v.Password); Remove-Variable v,p';
    $process=proc_open(['powershell.exe','-NoProfile','-Command',$script],[0=>STDIN,1=>['pipe','w'],2=>STDERR],$pipes);
    if (!is_resource($process)) { throw new RuntimeException('Private password prompt unavailable.'); }
    $password=stream_get_contents($pipes[1]); fclose($pipes[1]);
    if (proc_close($process)!==0 || !is_string($password)) { throw new RuntimeException('Private password prompt failed.'); }
    return $password;
}
