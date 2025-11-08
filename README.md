# Laravel Module飞书模块

## 介绍

将飞书云文档作为在线编辑器, 内容可以同步分发到各个平台. 目前只同步到github(本人暂无其他需求).

使用无头浏览器可以将云文档中的画板等保存为图片. 

## Install
```bash
composer require nwidart/laravel-modules
composer requirejoshbrw/laravel-module-installer

composer require youlooklikedelicious/feishu-module
```

修改composer.json
```json
{
    "extra": {
        "laravel": {
            "dont-discover": []
        },
        "merge-plugin": {
            "include": [
                "Modules/*/composer.json"
            ]
        }
    }
}
```

## Requirement

1. PHP 8.2 或更高级版本
2. Laravel 11 或更高级版本
3. [Laravelmodules](https://laravelmodules.com/)
4. [飞书客户端小组件](https://github.com/youLookLikeDelicious/feishu-sync-github-cli)
