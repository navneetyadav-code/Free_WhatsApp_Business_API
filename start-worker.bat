@echo off
setlocal

set "ROOT_DIR=%~dp0"
if "%ROOT_DIR:~-1%"=="\" set "ROOT_DIR=%ROOT_DIR:~0,-1%"
set "WORKER_DIR=%ROOT_DIR%\worker"
set "PID_FILE=%WORKER_DIR%\worker.pid"
set "OUT_LOG=%WORKER_DIR%\worker.out.log"
set "ERR_LOG=%WORKER_DIR%\worker.err.log"
set "ACTION=%~1"

if "%ACTION%"=="" goto :menu

if /I "%ACTION%"=="start" goto :start_worker
if /I "%ACTION%"=="stop" goto :stop_worker
if /I "%ACTION%"=="restart" goto :restart_worker
if /I "%ACTION%"=="status" goto :status_worker
if /I "%ACTION%"=="logs" goto :show_logs
if /I "%ACTION%"=="help" goto :show_help

echo Unknown option: %ACTION%
goto :show_help

:menu
cls
echo ==============================
echo   WhatsApp Worker Controller
echo ==============================
echo.
echo 1. Start worker
echo 2. Stop worker
echo 3. Restart worker
echo 4. Worker status
echo 5. Show log file paths
echo 6. Exit
echo.
set /p MENU_CHOICE=Choose an option: 

if "%MENU_CHOICE%"=="1" goto :start_and_hold
if "%MENU_CHOICE%"=="2" goto :stop_and_hold
if "%MENU_CHOICE%"=="3" goto :restart_and_hold
if "%MENU_CHOICE%"=="4" goto :status_and_hold
if "%MENU_CHOICE%"=="5" goto :logs_and_hold
if "%MENU_CHOICE%"=="6" exit /b 0

echo Invalid choice.
echo.
pause
goto :menu

:validate
if not exist "%WORKER_DIR%\server.mjs" (
    echo Worker entry file not found: "%WORKER_DIR%\server.mjs"
    exit /b 1
)

where node >nul 2>nul
if errorlevel 1 (
    echo Node.js is not installed or not available in PATH.
    exit /b 1
)
exit /b 0

:install_if_needed
if not exist "%WORKER_DIR%\node_modules" (
    echo Installing worker dependencies...
    pushd "%WORKER_DIR%"
    where npm >nul 2>nul
    if errorlevel 1 (
        popd
        echo npm is not installed or not available in PATH.
        exit /b 1
    )
    call npm install
    if errorlevel 1 (
        popd
        echo npm install failed.
        exit /b 1
    )
    popd
)
exit /b 0

:read_pid
set "WORKER_PID="
if exist "%PID_FILE%" (
    set /p WORKER_PID=<"%PID_FILE%"
)
exit /b 0

:pid_running
call :read_pid
if not defined WORKER_PID exit /b 1
powershell -NoProfile -Command "if (Get-Process -Id %WORKER_PID% -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }" >nul
if errorlevel 1 (
    exit /b 1
)
exit /b 0

:start_worker
call :validate
if errorlevel 1 exit /b 1

call :pid_running
if not errorlevel 1 (
    echo Worker is already running with PID %WORKER_PID%.
    echo Worker are started to work.
    exit /b 0
)

if exist "%PID_FILE%" del /q "%PID_FILE%" >nul 2>nul

call :install_if_needed
if errorlevel 1 exit /b 1

echo Starting WhatsApp worker...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$p = Start-Process -FilePath 'node' -ArgumentList 'server.mjs' -WorkingDirectory '%WORKER_DIR%' -WindowStyle Hidden -RedirectStandardOutput '%OUT_LOG%' -RedirectStandardError '%ERR_LOG%' -PassThru; Set-Content -Path '%PID_FILE%' -Value $p.Id"

if errorlevel 1 (
    echo Failed to start worker.
    exit /b 1
)

timeout /t 2 /nobreak >nul
call :pid_running
if errorlevel 1 (
    echo Worker failed to stay running. Check:
    echo   %OUT_LOG%
    echo   %ERR_LOG%
    exit /b 1
)

echo Worker are started to work.
echo To stop it, run: start-worker.bat stop
exit /b 0

:stop_worker
call :pid_running
if errorlevel 1 (
    if exist "%PID_FILE%" del /q "%PID_FILE%" >nul 2>nul
    echo Worker is not running.
    exit /b 0
)

taskkill /PID %WORKER_PID% /T /F >nul
if errorlevel 1 (
    echo Failed to stop worker PID %WORKER_PID%.
    exit /b 1
)

if exist "%PID_FILE%" del /q "%PID_FILE%" >nul 2>nul
echo Worker stopped.
exit /b 0

:restart_worker
call :stop_worker
call :start_worker
exit /b %ERRORLEVEL%

:status_worker
call :pid_running
if errorlevel 1 (
    echo Worker is not running.
    exit /b 0
)

echo Worker is running with PID %WORKER_PID%.
echo Logs:
echo   %OUT_LOG%
echo   %ERR_LOG%
exit /b 0

:show_logs
echo Output log:
echo   %OUT_LOG%
echo Error log:
echo   %ERR_LOG%
exit /b 0

:start_and_hold
call :start_worker
echo.
pause
goto :menu

:stop_and_hold
call :stop_worker
echo.
pause
goto :menu

:restart_and_hold
call :restart_worker
echo.
pause
goto :menu

:status_and_hold
call :status_worker
echo.
pause
goto :menu

:logs_and_hold
call :show_logs
echo.
pause
goto :menu

:show_help
echo Usage:
echo   start-worker.bat start
echo   start-worker.bat stop
echo   start-worker.bat restart
echo   start-worker.bat status
echo   start-worker.bat logs
exit /b 1
