@echo off
title SSH Tunnel BudgetKita (65432 -^> server 5432)
color 0A
echo ============================================================
echo    BudgetKita  -  SSH Tunnel ke Database cPanel
echo ============================================================
echo.
echo    Lokal    : 127.0.0.1:65432
echo    Server   : cesena.id.rapidplex.com:5432  (via SSH :64000)
echo    User     : budgetki   (otentikasi SSH key, tanpa password)
echo.
echo    ^> Biarkan jendela ini TERBUKA selama develop.
echo    ^> Tutup jendela / tekan Ctrl+C untuk mematikan tunnel.
echo    ^> Tidak ada tulisan output = tunnel sedang aktif (normal).
echo ============================================================
echo.
echo [1/1] Menyalakan tunnel SSH ...
echo.
ssh -i "%USERPROFILE%\.ssh\id_rsa" -o BatchMode=yes -o ServerAliveInterval=30 -o ServerAliveCountMax=3 -N -L 65432:127.0.0.1:5432 -p 64000 budgetki@cesena.id.rapidplex.com
echo.
echo ============================================================
echo    Tunnel terhenti (terputus atau dihentikan).
echo    Jalankan ulang file ini untuk menyambung kembali.
echo ============================================================
pause
