# Symfony fast track book project

### Notes:
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

* BYTEA database field type in Postresql.

* Execute migrations with newer version on command `php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20220418170145' --up`

* ### Event Dispatcher Component
  * Possible to create listeners (called subscribers) to specific events throws by framework or some components.
  * Using maker bundle the suggested list can looks like:
    ```bash
    Suggested Events:
      * Symfony\Component\Security\Http\Event\CheckPassportEvent (Symfony\Component\Security\Http\Event\CheckPassportEvent)
      * Symfony\Component\Security\Http\Event\LoginSuccessEvent (Symfony\Component\Security\Http\Event\LoginSuccessEvent)
      * Symfony\Component\Security\Http\Event\LogoutEvent (Symfony\Component\Security\Http\Event\LogoutEvent)
      ... and more ...
    ```
  * Examples of debugging it in console: ```php bin/console debug:event-dispatcher```
  * Or specific event ```php bin/console debug:event-dispatcher kernel.exception```
    
* ### Dependency Injection Container
  * Entities are Data Object and its not be right to inject them as services
  * Previously auto generated event subscriber was instantiated by framework because of the interface
    which class implements. Interface tells framework how to create service.
  * Symfony Services Cheat Sheet: `https://github.com/andreia/symfony-cheat-sheets/blob/master/Symfony4/services_en_42.pdf`

* ### Testing
  * Writing `dataProviders` functions. You haven't return a simple array. It's a `iterable` type hint. It's mean you 
  can simple write more readable generator using yield keyword instead on multiple nested array. For ex.
  ```php
  public function someDataProvider(): iterable``
  {
    // buid some data
    yield 'key' => 'data1'
  
    yield 'key' => 'data2'
  }
  ```
  * In Symfony components like http you can find classes to mock it easily. For ex. `MockHttpClient`