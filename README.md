# Symfony fast track book project

### notes:
* Creating new Symfony project using CLI tool. Just execute `symfony new <new_project_dir> --webapp`
* Installed `composer require --dev symfony/profiler-pack`
* `.env` is committed but locally you can use `.env.local` 

* Possibility to configure xdebug to show file in IDE `https://symfony.com/doc/current/the-fast-track/en/5-debug.html#configuring-your-ide`

* Marks as `###< doctrine/doctrine-bundle ###` were added by the recipes/packages. Then is easily to remove also when
the package will be removed.

* Using Docker&DockerCompose remember to use the same network and hosts names for connections between them.

* Cannot autowire entity (because the entites are excluded from services file): https://symfony.com/doc/current/the-fast-track/en/10-twig.html#creating-the-page-for-a-conference

* format_date() missing function wasn't resolved by isntalled `symfony composer req "twig/intl-extra:^3"` but `composer require twig/extra-bundle`

* Using `path()` to generate links instead of hard coding path `/conference/`