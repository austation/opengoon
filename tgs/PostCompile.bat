@echo off

powershell -NoProfile -ExecutionPolicy Bypass -File PostCompile.ps1 -game_directory %1
