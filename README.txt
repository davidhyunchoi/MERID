Instructions for getting started on MERID (and by extension Zend Framework 2 and MongoDB)
=========================================================================================

Setting up your computer
========================

-Ubuntu
    - install LAMP stack 
        - sudo apt-get install tasksel
        - sudo tasksel install lamp-server
    - install MongoDB
        - please follow instructions at http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/
        - additionally, I'd recommend downloading a gui for MongoDB
            - RockMongo is one, http://rockmongo.com/wiki/installation?lang=en_us
            - I just install it in it's own directory under /var/www/ and give it it's own vHost
    - enable necessary modules
        - sudo a2enmod rewrite
        - sudo a2enmod ssl
    - create your virtual host
        - edit /etc/hosts
            - add line "127.0.0.X	merid"
            - don't use the quotes, replace the X with a number
        - create the site config
            - create file /etc/apache2/sites-available/merid
            - contents should be as follows
                ServerName 127.0.0.X
                <VirtualHost *:80>
                        ServerAlias merid
                        DocumentRoot /var/www/merid/public
                        SetEnv APPLICATION_ENV "development"
                        ErrorLog /var/www/MERID/error.log

                        <Directory /var/www/merid/public>
                                DirectoryIndex index.php
                                AllowOverride All
                                Order allow,deny
                                Allow from all
                        </Directory>
                </VirtualHost>
            - replace the X in server name with the matching number from your hosts file
        - sudo a2ensite merid
        - sudo service apache2 restart
    - clone this repository
        - cd /var/www
        - git clone https://github.com/clandorf/merid.git
    - run composer (installs dependencies)
        - cd merid
        - php composer.phar self -update
        - php composer.phar install
    - go crazy
    - also, I always just give everything write access; at the very least do it for /var/www/merid/data
        - cd /var/www
        - sudo chmod -R 777 merid 

- Windows 
    - coming soon 

- Mac 
    - meh 

What's going on in this massive directory structure
===================================================

- ProjectDocuments 
    - contains MERID related documents
- config
    - autoload
        - contains configuration override files for various modules, local configurations, and globabl configurations
    - application.config.php
        - main configuration file for Zend Framework 2 application
- data
    - stores cached files, make sure the application has write access
- module, the meat of the application, each directory directly below it is an individual module
    - Individual Module (for us right now, only Application), if you're interested in why it's structured this way, look here, http://framework.zend.com/manual/2.1/en/modules/zend.mvc.intro.html
        - config
            - module.config.php
                - returns an array that serves as the configuration that manages all that magic that happens within a module, namely routing and linking views to controllers.
                - routes - does all the routing magic; takes /controller/action/key and routes it to the proper function within the controller provided passing the key given
                - controllers - lists controllers within the module
                - viewmanager - routes controller functions to the view folder
        - language - zend stuff for translating content
        - src/Application
            -Controller
                - receives, modifies, and returns data 
                - in the future, we should try and use RESTful controllers; for now we'll try and be as close to a RESTful implementation as we can
            - Entity
                - a data model
            - Mapper
                - uses repositories to create entities or arrays with data from multiple tables
            - Repository
                - directly interacts with the database and returns entities
        - view
            - application
                - controllername
                    - phtml files
                    - each phtml file should contain what you'd like to place in the body of that page
                    - the file's name should match a function within it's controller
            - error
                - contains default error pages
            - layout
                - contains layout files
                - we should just use one default layout; if necessary we can create multiple
                - layout.phtml should contain references to all js and css files
        - Module.php - controls parts of the module
- public (most of the directories within this folder are pretty self explanatory)
    - ScrapWork contains some random work people were working on; to navigate to it use the url merid/ScrapWork/YourFile
    - css 
    - fonts
    - img
    - js
- vendor
    - contains libraries the application is dependent on; placed there after running composer.phar
- misc. 
    - composer.json is the package manager for the application
    - error.log is the error log...