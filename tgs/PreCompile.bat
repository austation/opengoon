@echo off

powershell -NoProfile -ExecutionPolicy Bypass -File PreCompile.ps1 -game_directory %1 -commit %2
