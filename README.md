<p align="center">
    
[![Actions Status](https://github.com/drorganvidez/evidentia/workflows/CI/badge.svg)](https://github.com/drorganvidez/evidentia/actions) 
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
</p>

# Aviso
Se recomienda tener desactivado el Cortafuegos/Firewall para evitar problemas en la instalación. El firewall de Windows no suele dar problemas, cosa que no ocurre en otros como Kaspersky, Panda, Norton, etc...

# 1. Software necesario
Es necesario tener el siguiente software instalado en la máquina donde se quiera desplegar Evidentia:
* [Descargar VirtualBox](https://www.virtualbox.org/wiki/Downloads)
* [Descargar Vagrant](https://www.vagrantup.com/downloads.html)
* [Descargar Git (para Windows)](https://git-scm.com/download/win)
* [Descargar Git (para Mac)](https://sourceforge.net/projects/git-osx-installer/files/git-2.6.3-intel-universal-mavericks.dmg/download?use_mirror=autoselect)  

_Nota: es necesario reiniciar después de instalar Vagrant._

# 2. Clonar repositorio de Evidentia
`git clone https://github.com/drorganvidez/evidentia.git evidentia`

# 3. Instalación
## 3.1 Instalación en Windows
Dentro de la carpeta `evidentia` que acabamos de clonar, encontraremos un archivo llamado `install.bat`  

Hacemos doble click en dicho archivo.

Seleccionaremos la opción `3) virtualbox`.  

### 3.1. Host virtual
Para habilitar un acceso más sencillo a través del navegador, se recomienda crear un host virtual.  
Esto es posible haciendo click derecho en `host.bat`, **ejecutar como administrador**.

_**Tiempo promedio de instalación: 30 minutos**_

## 3.2 Instalación en Mac y Linux
Dentro de la carpeta `evidentia` que acabamos de clonar, encontraremos un archivo llamado `install.sh`  

Primero, mediante consola, nos situaremos en la carpeta `evidentia`  

Luego, daremos permisos de ejecución al archivo con `chmod +x install.sh`  

Por último, ejecutaremos el archivo con `sh install.sh`  

Seleccionaremos la opción `3) virtualbox`.  

_**Tiempo promedio de instalación: 30 minutos**_

# 4. Comprobar que todo ha ido bien
Desde el navegador, acceder a la dirección `http://evidentia.test`  

## 4.1  Usuarios de prueba
Por defecto, las cuentas que se crean en el _Curso 2020/21_ son:
```
ESTUDIANTES
Usuario: alumno1
Pass: alumno1

Usuario: alumno2
Pass: alumno2

SECRETARIOS
Usuario: secretario1
Pass: secretario1

Usuario: secretario2
Pass: secretario2

COORDINADORES
Usuario: coordinador1
Pass: coordinador1

Usuario: coordinador2
Pass: coordinador2

COORDINADORES DE REGISTRO
Usuario: coordinadorregistro1
Pass: coordinadorregistro1

Usuario: coordinadorregistro2
Pass: coordinadorregistro2

PRESIDENTES
Usuario: presidente1
Pass: presidente1

Usuario: presidente2
Pass: presidente2

PROFESORES
Usuario: profesor1
Pass: profesor1

Usuario: profesor2
Pass: profesor2

ADMINISTRADOR DE INSTANCIAS
Usuario: admin@admin.com
Pass: admin
```

# 5. Consideraciones varias

## 5.1 La máquina virtual Laravel Homestead
La instalación demora bastante debido a que crea una máquina virtual Vagrant llamada **Homestead**. Es de suma utilidad poder acceder mediante terminal a esta máquina virtual para hacer uso de los comandos propios de Laravel. No en vano, no deja de ser un Ubuntu virtualizado, con la ventaja de poder trabajar desde tu máquina local y no depender de la virtualización de un escritorio consumiendo recursos gráficos.  

**Antes que nada, debemos situarnos en la carpeta `homestead`.**

### 5.1.1 Arrancar la máquina

`vagrant up`

### 5.1.2 Acceder a la máquina

`vagrant ssh`

### 5.1.3 Parar la máquina

`vagrant halt`

## 5.2 Crear y poblar la base de datos
_Nota: el proceso de instalación ya realiza este paso._

**Una vez dentro de la MV de Homestead**, hacemos  

`cd laravel`  

El siguiente comando inicializará la configuración de la base de datos. Si ya se hubiera trabajado anteriormente con la app, **borrará todas las instancias y comenzará de 0.**  

`php artisan evidentia:start`  

## 5.3 Crear la instancia por defecto
_Nota: el proceso de instalación ya realiza este paso._  

Ya que Evidentia soporta varias instancias de la misma app, es más rápido crear una instancia de desarrollo y saltar el paso del formulario en la administración. Además, esto creará usuarios de ejemplo:  

`php artisan evidentia:createinstance`     

Esto creará las tablas y las populará para una instancia en concreto, _Curso 2020/21_  

## 5.4 Reiniciar la instancia por defecto

Si, por lo que sea, en el desarrollo o testeo se decidiera crear nuevas tablas (migraciones), basta con ejecutar el siguiente comando:  

`php artisan evidentia:reloadinstance`  

## 5.5 Opciones de Vagrant

### 5.5.1 Cambiar configuración en la máquina de Vagrant
Cualquier configuración que se haga en el archivo `Homestead.yaml` debe ir seguido de `vagrant reload --provision`

### 5.5.2 Salir de Vagrant
`exit`

### 5.5.3 Parar Vagrant
`vagrant halt`  

### 5.5.4 Destruir máquina de Vagrant
`vagrant destroy`  

### 5.5.5 Eliminar Laravel Homestead
`vagrant box remove laravel/homestead`

## 5.6 Acceder a la base de datos mediante MySQL Workbench u otro gestor de BBDD
**Asegúrate de que la MV está arrancada.** Los datos de acceso son los siguientes:
```
host: localhost
puerto: 33060
base de datos: homestead
usuario: homestead
contraseña: secret
```
