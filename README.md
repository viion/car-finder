# Car finder

Find me a car

## Requirements

- Install docker-compose: https://docs.docker.com/compose/install/

## Getting Setup

- `mkdir project`
- `cd project`

Build and run the docker

- `docker-compose build`
- `docker-compose up -d`

Add to your hosts file: `127.0.0.1 car.local`

## XDebug

- IDE Key: PHPSTORM
- Update `docker-compose.yml` -> `extra_hosts: "docker-host.localhost:127.0.0.1"` to your ip from `ifconfig en0`
- Port: `5902`
- Host: `docker-host.localhost`
- Use Docker container for PHP Cli Interpreter.
