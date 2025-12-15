SISTEMA DE MESA DE PARTES (MESAPARTES_JVC) – LARAVEL
Sistema web desarrollado en Laravel versión 11.
para la gestión de documentos de Mesa de Partes:
- Registro de documentos
- Seguimiento de trámites
- Gestión de usuarios
- Control de estados y movimientos
 REQUISITOS DEL SISTEMA
Antes de instalar el proyecto, asegúrese de contar con lo siguiente:
- PHP >= 8.1
- Composer
- MySQL 
- XAMPP 
- Node.js y NPM
- Git
PASOS PARA LA INSTALACIÓN
1.	Instalar XAMPP
Descargar e instalar XAMPP y asegurarse de iniciar:
- Apache
- MySQL
2.	Clonar el repositorio
git clone https://github.com/Josevc7/mesapartes_jvc.git
Ingresar a la carpeta del proyecto:
cd mesapartes_jvc
3.	Instalar dependencias con Composer
composer install
4.	Crear archivo de configuración
Copiar el archivo de entorno:
cp .env.example .env 0 copy .env.example .env
5.	Configurar la base de datos
Editar el archivo .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mesapartes
DB_USERNAME=root
DB_PASSWORD=
Crear la base de datos en MySQL:
CREATE DATABASE mesapartes;
6.	Generar la clave de la aplicación
php artisan key:generate
Ejecutar migraciones
php artisan migrate
Si el proyecto incluye datos iniciales:
php artisan migrate –seed
7.	Instalar dependencias frontend (opcional)
npm install
npm run dev
8.	Ejecutar el servidor
php artisan serve
Abrir en el navegador:
http://127.0.0.1:8000
Usuario de prueba:
Usuario: mesapartes@mesapartes.gob.pe
Contraseña: mesa123


