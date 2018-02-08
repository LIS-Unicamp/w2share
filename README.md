W2Share Website: http://w2share.lis.ic.unicamp.br

# Installation Instructions #
## Installation on a local machine ##
### [Install composer on Ubuntu 16.04](https://bappa.info/2016/05/07/install-composer-in-ubuntu-16-04/) 
1. Run: 
$ sudo apt-get update
2. **If this was not done before**: Install wget for file download in ubuntu
$ sudo apt-get install wget
3. Download the **composer.phar** from **https://getcomposer.org/download via wget or built-in curl**
$ wget https://getcomposer.org/composer.phar
or
$ curl -O https://getcomposer.org/composer.phar
**-O is capital O not Zero(0)
4. Rename composer.phar to composer
$ mv composer.phar composer
5. Make composer executable
$ chmod +x composer
6. Now composer can be run locally through
$ ./composer
However, composer is available where you are, i.e., current directory where the composer file is.
7. Make composer available globally move it to /user/local/bin by running
$ sudo mv composer /usr/local/bin
8. Now composer will be available anywhere by simply
$ composer
### [Install composer on Mac](http://www.maiconschmitz.com.br/blog/2015/03/04/instalando-composer-no-mac-os-x.html) 

* Install **git**
* Install **netbeans PHP**

* Install **php-curl** and dependencies (This depends of the version of php that you are using)
* * sudo apt-get update
* * sudo apt-get install php-curl
* * sudo apt-get install php-xml

* If you are using php7.0 you can use:
* * sudo apt-get install php7.0-curl php7.0-mbstring php7.0-zip php7.0-xml

### Installation on a Web Server ###
* PHP
* Apache2
* MySQL
* Module PHP for Apache
* $ sudo apt-get install apache2 php libapache2-mod-php php-mysql

* If you are working on Ubuntu, it may be necessary to set the file 000-default.conf. Run this: $ sudo nano /etc/apache2/sites-enabled/000-default.conf
* And search for:
* * ServerAdmin webmaster@localhost
* * DocumentRoot /var/www/html
* * Change the last line to: **DocumentRoot /var/www/** 
* save your changes.
* To run the server, you can use: sudo systemctl restart apache2

### Install Virtuoso ###
* Notes for Installation on Ubuntu: [https://virtuoso.openlinksw.com/dataspace/doc/dav/wiki/Main/VOSUbuntuNotes]
* install virtuoso open source
* Install Virtuoso
** sudo apt-get install virtuoso-opensource
* SPARQL interface: [http://localhost:8890/sparql](Link URL)
* To create backups (dump data): [https://virtuoso.openlinksw.com/dataspace/doc/dav/wiki/Main/VirtRDFDatasetDump]
* The correct statement procedure: [https://bitbucket.org/lucasaugustomcc/phd-prototype/wiki/CREATE%20PROCEDURE%20dump_one_graph]
* Set permissions on SPARQL: [http://localhost:8890/conductor](Link URL) dba:dba
* * Open Interactive SQL (iSQL):

###For Load ttl files into virtuoso###

GRANT execute ON DB.DBA.SPARUL_LOAD TO "SPARQL"

###To insert triples into virtuoso###

* GRANT execute ON SPARQL_INSERT_DICT_CONTENT TO "SPARQL"
* GRANT execute ON SPARQL_INSERT_DICT_CONTENT TO SPARQL_UPDATE

###For update (delete and insert) run this:###

* GRANT execute ON DB.DBA.SPARQL_MODIFY_BY_DICT_CONTENTS TO "SPARQL"
* GRANT execute ON DB.DBA.SPARQL_MODIFY_BY_DICT_CONTENTS TO SPARQL_UPDATE
* GRANT SPARQL_UPDATE to "SPARQL"

###For delete run this###

* GRANT execute ON DB.DBA.SPARQL_DELETE_DICT_CONTENT TO "SPARQL"
* GRANT execute ON DB.DBA.SPARQL_DELETE_DICT_CONTENT TO SPARQL_UPDATE

###To allow SPARUL statements to be run, add this:###

* GRANT execute ON DB.DBA.SPARUL_RUN TO "SPARQL"
* GRANT execute ON DB.DBA.SPARUL_RUN TO SPARQL_UPDATE

###EDIT : To allow graph deletion, run the following:###

* GRANT execute on DB.DBA.SPARUL_CLEAR TO "SPARQL"
* GRANT execute on DB.DBA.RDF_QUAD TO "SPARQL"
* GRANT delete on DB.DBA.RDF_QUAD TO "SPARQL"
* GRANT execute ON SPARUL_CLEAR TO "SPARQL"
* GRANT execute ON SPARUL_CLEAR TO SPARQL_UPDATE
* GRANT DELETE ON RDF_QUAD TO "SPARQL"
* GRANT DELETE ON RDF_QUAD TO SPARQL_UPDATE

###Yet another grant (Virtuoso 7.0) NEED TO DO THIS for inserts to work!!!!###

* GRANT execute ON DB.DBA.RDF_OBJ_ADD_KEYWORD_FOR_GRAPH TO "SPARQL"
* GRANT execute ON DB.DBA.RDF_OBJ_ADD_KEYWORD_FOR_GRAPH TO SPARQL_UPDATE

* GRANT execute ON DB.DBA.L_O_LOOK TO "SPARQL"
* GRANT execute ON DDB.DBA.L_O_LOOK TO SPARQL_UPDATE

###After run the commands, the output sould be:
The statement execution did not return a result set

## Install Database ##
* install phpmyadmin
* configure parameters.yml with database name, user and password
* create database "prototype" in mysql
* create table using symfony command:
* * sudo rm -R app/cache/* && sudo rm -R app/logs/* (if you have problems with permission on directories cache and logs)
* * php app/console doctrine:schema:update --force

## Install Project ##
* cd /var/www (The project will be installed/cloned into /var/www)
* clone repository phd-prototype from bitbucket: https://github.com/lis-unicamp/w2share.git
* Inside /var/www/w2share/, to create the following directories:
* * mkdir web/uploads
* * mkdir web/uploads/documents
* * mkdir app/cache
* * mkdir app/sessions
* * mkdir vendor

## After install the project ##
* In the file **parameters.yml** (app/config/parameters.yml) assign a value to the attribute secret
* * secret: "This is a secret key"
* Execute composer: 
* **execute in menu** (from your IDE - e.x., Netbeans, Eclipse): Select phd-prototype -> Composer -> Install (no-dev)
or using a terminal (It's necessary to be in the path where the project is located. In our case, var/www/php-prototype):
* "/usr/bin/php" "/usr/local/bin/composer" "--ansi" "--no-interaction" "install" "--no-dev"

* Execute Symfony to create the assets
* * **execute in menu** (from your IDE - e.x., Netbeans, Eclipse): Select phd-prototype -> Symfony -> Run Command:
* * In the field Filter write install. Then, in Matching Task, select *assesst:install*, and click button Run.   
* * In Parameters write --symlink web. click button Run.

### Script Ruby : Taverna t2flow to Image ###
* sudo apt-get install ruby ruby-dev gcc make libxml2-dev
* sudo gem install taverna-t2flow
* * [https://github.com/myExperiment/workflow_parser-t2flow](Link URL)

## YesWorkflow ##

### Install Graphviz ###
$ sudo apt-get install graphviz

### Install Java JRE ###
$ sudo apt-get install default-jre

## Remote Server - Virtual Machine ##
* sudo apt-get install haveged
* sudo update-rc.d haveged defaults
* [https://www.digitalocean.com/community/tutorials/how-to-setup-additional-entropy-for-cloud-servers-using-haveged](Link URL)