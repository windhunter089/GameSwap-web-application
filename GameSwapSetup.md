# Getting Started with GameSwap

How to setup a local development environment for the **GameSwap** application.


## Install IDE
I am using **PHPStorm** from **JetBrains** as my PHP IDE of choice.
If you have not done so already, you can register for a JetBrains educational licesnse here: 
https://www.jetbrains.com/community/education/#students. You can then visit https://www.jetbrains.com/phpstorm/ to download the IDE.

##  Install WAMP Environment

Download latest Bitnami WAMP **v8.1.4-0** package here: [Install WAMP, Download WAMP (bitnami.com)](https://bitnami.com/stack/wamp/installer)

## Checkout GameSwap repo

If you haven't already, please check out the GameSwap repo with
`git clone https://github.gatech.edu/cs6400-2022-01-spring/cs6400-2022-01-Team064.git`. Checkout the `devel` branch and pull the latest code.

## WAMP + PHPStorm Integration
Now that you have the repo, WAMP, and PHPStorm installed, you can setup the application in PHPStorm.

 1. Declare **PHP Interpreter** in PHPStorm
	 1. Go to File->Settings->PHP
	 2. For **PHP language used** select **8.1**
	 3. For **CLI Interpreter** browse for your local Bitnami PHP executable, something like this `C:\Bitnami\wampstack-8.1.4-0\php\php.exe`
 2.  Open up the **cs6400-2022-01-Team064** repo via **File->Open**.
 3. Setup **deployment** configuration
	 1. Go to File->Settings->Build,Execution,Deployment->Deployment
	 2. Select plus sign (+). For "**New server name**" you can enter whatever you'd like. I put "**WAMP PHP**"
	 3. "Connection -> Type" should be "**Local or mounted folder**"
	 4. For "Connetion -> Folder", choose `C:\Bitnami\wampstack-8.1.4-0\apache2\htdocs`. 
	 5. For "Connection -> Web server url" I entered `http://localhost:8081/`, becasue that was my port of choice. Default is 80.
	 6. "Mappings->Deployment Path" I entered `/`
	 7. "Mappings->Web path" I entered `/GameSwap`.
	 8. I would recommend going to  File->Settings->Build,Execution,Deployment->Deployment->Options and setting "**Upload changed files automatically to the default server**" from "Never" to "**On explicit save action (Ctrl+S)**". 

## phpMyAdmin

 1. Login as ‘root’ to phpMyAdmin: http://127.0.0.1:80/phpmyadmin/ using the password you entered during the Bitnami installation phase.
	 1. At any time, you can configure the WAMP servers by opening "**WAMP packaged by Bitnami 8.14-0**" application on windows and going to "**Manager Servers**" tab.
	 2. Go to the SQL tab and run the sql in the GameSwap repo `team064_p2_schema.sql`. This should create the gatech user and the GameSwap database.

 

> Written with [StackEdit](https://stackedit.io/).
