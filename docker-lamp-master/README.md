# docker-lamp

Docker example with Apache, MySql 8.0, PhpMyAdmin and Php

- You can use MariaDB 10.1 if you checkout to the tag `mariadb-10.1` - contribution made by [luca-vercelli](https://github.com/luca-vercelli)
- You can use MySql 5.7 if you checkout to the tag `mysql5.7`

I use docker-compose as an orchestrator. To run these containers, run:

```
docker-compose up -d
```

in a terminal. To shut down, run: 

```
docker-compose down
```

Open phpmyadmin at [http://localhost:8080](http://localhost:8080)
Open web browser to look at a simple php example at [http://localhost](http://localhost)

Enjoy !

## For the course on databases
This is a fork of Joel Cavat's repository. This fork contains some changes to reflect some of the examples we would see in the course, but also provides you a starting point for your project. Notice that the name of the host on which MySQL resides is `ms8db`. This may need to be changed to `localhost` once you deploy your code on the department's machines. I strongly encourage groups to use Git to collaborate. If you are not familiar with Git, you can download this project as a ZIP folder. If you want to use this Docker composition, you will need to install Docker.

* Install docker
* Download, or fork and clone this repository
* Open a command promps from the directory containing this project
* Execute the commands as shown above

By default, the database, username, and password are: `groupXX`, `groupXX`, and `secret`. Do not forget to change those in `docker-compose.yml`. 

Good luck !
