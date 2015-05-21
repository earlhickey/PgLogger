PgLogger
============

ZF2 Logging Module

Log to DB, Email, File, Syslog, FirePHP. Fully customizable with config file.

Call logger directly or with triggers.

Installation
------------

### Main Setup

#### By cloning project

1. Install the [PgLogger](https://github.com/earlhickey/PgLogger) ZF2 module
   by cloning it into `./vendor/`.
2. Clone this project into your `./vendor/` directory.

#### With composer

1. Add this project in your composer.json:

    ```json
    "require": {
        "earlhickey/pg-logger": "0.*"
    }
    ```

2. Now tell composer to download PgLogger by running the command:

    ```bash
    $ php composer.phar update
    ```

#### Post installation

1. Enabling it in your `application.config.php` file.

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'PgLogger',
        ),
        // ...
    );
    ```

2. Copy `./vendor/earlhickey/PgLogger/config/pg-logger.global.php.dist` to `./config/autoload/pg-logger.global.php` and change the values as desired.

3. Create directory `data/log` in the root of your ZF2 project.

### Usage

1. Call the logger service directly:
    ```php
    $this->getServiceLocator()->get('PgLogger\Service\Logger')->crit('test');
    ```
2. Use triggers:
    ```php
    $this->getEventManager()->trigger('log', $this, array('message' => $message, 'priority' => Logger::CRIT));
    ```
   * message
   * priority is optional (default: INFO)