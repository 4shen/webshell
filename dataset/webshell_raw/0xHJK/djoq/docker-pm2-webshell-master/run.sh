#!/bin/bash
cd /app && pm2 set pm2-webshell:username foo && pm2 set pm2-webshell:password bar && pm2 set pm2-webshell:port 7890 && pm2 logs
