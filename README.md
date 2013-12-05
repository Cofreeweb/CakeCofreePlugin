CofreePlugin
==========


## Creación de proyectos

Para la creación de proyectos:
  
  1. Instalar la aplicación con https://github.com/Cofreeweb/CakeInitProject
  2. Situados en /path/to/project hacemos bin/cake cofree.git install

## Instalación de proyectos

Para la instalación de proyectos:

 1. git clone url_git path/to/project
 2. cd path/to/project
 3. git submodule init
 4. git submodule update
 5. cp app/Config/core.php.default app/Config/core.php
 6. cp app/Config/database.php.default app/Config/database.php
 7. cp app/Config/email.php.default app/Config/email.php
 8. bin/cake cofree.git change_mod
 
 
## Actualización de proyectos

 1. git pull (para el proyecto en sí)
 2. bin/cake cofree.git update_plugins
 
## Commit del proyecto

 1. bin/cake cofree.git commit
 
## Commit de un plugin

1. bin/cake cofree.git pl_commit NombrePlugin