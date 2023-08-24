# symfony-initiation
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


