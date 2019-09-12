#!/usr/bin/env bash

set -e

MAX_MEMORY=$(cat /sys/fs/cgroup/memory/memory.limit_in_bytes |while read B dummy;do echo $((B/1024/1024))M;done)
echo "memory_limit=$MAX_MEMORY" > /usr/local/etc/php/conf.d/max-memory.ini

docker-php-entrypoint "$@"