Open e-Class Docker image

Base image is `php:8.2-apache`

# Run
* All commands should be run in the root Open e-Class folder
* Build: `docker compose -f docker-compose.build.yaml build`
* Run stack: `docker compose up -d`
* Run stack with SimpleIdentity (SSO & LDAP): `docker compose -f docker-compose.yaml -f docker-compose.test.yaml up -d`

# Environment Variables
* The variables below can be set in the `docker-compose.yaml` file
  - `MYSQL_LOCATION`: The DB location, used as default value when setting up a new installation. Should be left to the default value of `db` which is the service name of the `db` MariaDB container
  - `MYSQL_ROOT_USER`: The default of `root` is fine
  - `MYSQL_ROOT_PASSWORD`: The MariaDB root password to use to connect. Should be left to the default value of `secret`. Only containers in the Docker Compose stack have access to the MariaDB container, no SQL port is exposed to the outside world.
  - `MYSQL_DB`: The MariaDB database to use. The MariaDB container in the Docker compose stack sets a name of `eclass` (which is the default that we use)
  - `ADMIN_USERNAME`: The admin username to use. Default is `admin`
  - `ADMIN_PASSWORD`: You can set the admin password with an environment variable. By default the variable is not set and the admin can set the password on first installation (or accept a randomly set password)
  - `PHP_MAX_UPLOAD`: The maximum file that can be uploaded. The default value is `256M`

# Size
* Docker image
  - e-Class: `1 GB`
  - MariaDB: `400 MB`
* Memory usage
  - e-Class: `50 MB`
  - MariaDB: `150 MB`