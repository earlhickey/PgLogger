PgLogger
============

ZF2 Logging Module

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
        "earlhickey/pg-logger": "1.*"
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



### Usage

1. $this->getServiceLocator()->get('PgLogger\Service\Logger')->crit('test');
2. $this->getEventManager()->trigger('log', $this, array('message' => array('file' => $filename, "Testing Logging", 1, "meh"), 'priority' => Logger::CRIT));
