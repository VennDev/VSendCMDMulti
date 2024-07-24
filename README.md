# VSendCMDMulti
- Virion PocketMine-PMMP 5 allows you to send commands through servers in other IPs and ports that are using this Virion!!

# Virion Required
- [LibVapmPMMP](https://github.com/VennDev/LibVapmPMMP)

# Config when init this virion
```yml
---
settings-host:
  ip: 127.0.0.1
  port: 3003
  password: your-password
...
```

# Method
- Init when use virion in the plugin
```php
...
use venndev\vsendcmdmulti\VSendCMDMulti;

protected function onEnable(): void
{
    VSendCMDMulti::init($this);
}
```
- Send a request to execute a command to another server!
```php
VSendCMDMulti::sendCommand(string $command, string $ip, int $port, string $password);
```
