[![Symfony_6](https://img.shields.io/badge/Symfony-6-blue.svg)](https://symfony.com/)
[![PHP 8.2](https://img.shields.io/badge/PHP-8.2-purple.svg)](https://www.php.net/)
[![API Platform 3](https://img.shields.io/badge/API%20Platform-3-turquoise.svg)](https://api-platform.com/)
[![Docker](https://img.shields.io/badge/Docker-blue.svg)](https://www.docker.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-blue.svg)](https://www.postgresql.org/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-blue.svg)](https://phpunit.de/)
[![phpstan](https://img.shields.io/badge/phpstan-blue.svg)](https://phpstan.org/)
[![Makefile](https://img.shields.io/badge/Makefile-blue.svg)](https://www.gnu.org/software/make/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-turquoise.svg)](https://tailwindcss.com/)

# Symfony Initiation Project
A Symfony project having the CRUD of 2 entities and API.  
This project leverages various Symfony components and extensions, including `Doctrine`, `Events`, `Flex`, `Form`, `Uuid`, `Translation`,  `Twig`, `Validator`.  
[See page previews](page_previews.md)

## Requirements

- [Docker](https://www.docker.com)
- Make (already installed on most Linux distributions and macOS)

## Install
- #### Clone the project

```
git clone git@github.com:alpernuage/symfony-initiation.git
```
- Then `cd symfony-initiation`

- Add SERVER_NAME value, located in `.env.file`, in `/etc/hosts` in order to match your docker daemon
machine IP (it should be 127.0.0.1) or use a tool like `dnsmasq` to map the docker daemon to a local tld
(e.g. `.local`).

- Then just run `make install` command and follow instructions.
Run `make help` to display available commands.


