# Instalacion de Programas necesarios
1. laragon version 6.0.0
2. dbeaber


 git config --global user.name "Yendrid Quijada" <!--      -->
git config --global user.email "your_email@example.com"



# Sistema completo de gestión de farmacia

## Características

1. Productos
2. Categorías de productos
3. Compras
4. Ventas
5. Proveedores
6. Reportes
7. Control de acceso (roles y permisos)
8. Usuarios
9. Perfil de usuario
10. Configuración (ajustes de la aplicación)
11. Respaldo de la aplicación
12. Panel de control
13. Notificaciones de inventario

## Instalación, en la terminal de laragon

Sigue estos pasos para instalar la aplicación:

1. Clonar el repositorio
```
git clone https://github.com/soYYen22/servicio-comunitario-hospital.git
```

2. Ir al directorio del proyecto
```
cd servicio-comunitario-hospital
```

3. Instalar paquetes con composer
```
composer install
```

4. Instalar paquetes npm
```
npm install

npm run dev
```
5. Crear la base de datos

6. Copiar el archivo .env.example y renombrarlo a .env
```
cp .env.example ./.env
```

7. Generar la clave de la aplicación
```
php artisan key:generate
```

8. Configurar la conexión a la base de datos en el archivo .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doccure
DB_USERNAME=root
DB_PASSWORD=
```

9. Ejecutar las migraciones
```
php artisan migrate --seed
```

10. Iniciar el servidor local
```
php artisan serve
```

11. Abrir la dirección en el navegador
```
http://127.0.0.1:8000
```

## Credenciales de administrador

```
email: admin@admin.com
contraseña: admin
```

## Uso

**Perfil** - Cada usuario tiene su propio perfil. Puedes actualizar tus credenciales haciendo clic en el botón editar. También puedes cambiar tu contraseña desde la pestaña de contraseña.

**Usuarios** - Lista de todos los usuarios del sistema. Puedes agregar, editar o eliminar usuarios. También puedes exportar o imprimir los datos.

**Control de acceso** - Roles y permisos de usuarios. Puedes crear nuevos roles y asignarles permisos específicos.

**Proveedores** - Lista de proveedores de productos. Puedes agregar, editar o eliminar proveedores.

**Ventas** - Registro de todas las ventas realizadas. Puedes agregar nuevas ventas, eliminarlas y exportar los datos.

**Compras** - Registro de todas las compras de productos. Puedes agregar, editar o eliminar compras y exportar los datos.

**Productos** - Lista de todos los productos en venta. Puedes agregar, editar o eliminar productos.
  - *Sin stock*: Productos con cantidad cero (el sistema los detecta automáticamente)
  - *Vencidos*: Productos cuya fecha de vencimiento ha llegado (detectados automáticamente)

**Categorías** - Categorías de productos. Puedes agregar, editar o eliminar categorías.

## Cómo agregar un producto y venderlo

1. Agregar la categoría del producto
2. Agregar el proveedor del producto
3. Realizar una compra del producto
4. Agregar el producto a tu inventario
5. Comenzar a vender el producto
6. Cuando recibas notificaciones de stock, actualizar la cantidad o hacer una nueva compra Sistema completo de gestión de farmacia

## Características

1. Productos
2. Categorías de productos
3. Compras
4. Ventas
5. Proveedores
6. Reportes
7. Control de acceso (roles y permisos)
8. Usuarios
9. Perfil de usuario
10. Configuración (ajustes de la aplicación)
11. Respaldo de la aplicación
12. Panel de control
13. Notificaciones de inventario

## Instalación

Sigue estos pasos para instalar la aplicación:

1. Clonar el repositorio
```
git clone https://github.com/MusheAbdulHakim/Pharmacy-management-system.git
```

2. Ir al directorio del proyecto
```
cd Pharmacy-management-system
```

3. Instalar paquetes con composer
```
composer install
```

4. Instalar paquetes npm
```
npm install; npm run dev
```

5. Crear la base de datos

6. Copiar el archivo .env.example y renombrarlo a .env
```
cp .env.example ./.env
```

7. Generar la clave de la aplicación
```
php artisan key:generate
```

8. Configurar la conexión a la base de datos en el archivo .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doccure
DB_USERNAME=root
DB_PASSWORD=
```

9. Ejecutar las migraciones
```
php artisan migrate --seed
```

10. Iniciar el servidor local
```
php artisan serve
```

11. Abrir la dirección en el navegador
```
http://127.0.0.1:8000
```

## Credenciales de administrador

```
email: admin@admin.com
contraseña: admin
```

## Uso

**Perfil** - Cada usuario tiene su propio perfil. Puedes actualizar tus credenciales haciendo clic en el botón editar. También puedes cambiar tu contraseña desde la pestaña de contraseña.

**Usuarios** - Lista de todos los usuarios del sistema. Puedes agregar, editar o eliminar usuarios. También puedes exportar o imprimir los datos.

**Control de acceso** - Roles y permisos de usuarios. Puedes crear nuevos roles y asignarles permisos específicos.

**Proveedores** - Lista de proveedores de productos. Puedes agregar, editar o eliminar proveedores.

**Ventas** - Registro de todas las ventas realizadas. Puedes agregar nuevas ventas, eliminarlas y exportar los datos.

**Compras** - Registro de todas las compras de productos. Puedes agregar, editar o eliminar compras y exportar los datos.

**Productos** - Lista de todos los productos en venta. Puedes agregar, editar o eliminar productos.
  - *Sin stock*: Productos con cantidad cero (el sistema los detecta automáticamente)
  - *Vencidos*: Productos cuya fecha de vencimiento ha llegado (detectados automáticamente)

**Categorías** - Categorías de productos. Puedes agregar, editar o eliminar categorías.

## Cómo agregar un producto y venderlo

1. Agregar la categoría del producto
2. Agregar el proveedor del producto
3. Realizar una compra del producto
4. Agregar el producto a tu inventario
5. Comenzar a vender el producto
6. Cuando recibas notificaciones de stock, actualizar la cantidad o hacer una nueva compra