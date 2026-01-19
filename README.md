# 📚 Proyecto de Control de Absentismo y Horarios

Este proyecto ha sido desarrollado como un trabajo de **subida de nota**, centrado en la gestión eficiente de horarios escolares, control de absentismo y generación automática de temporalizaciones.

## 🚀 Descripción del Proyecto

La aplicación permite gestionar el calendario escolar y los horarios de los profesores, automatizando cálculos complejos que normalmente se harían manualmente.

### 🌟 Funcionalidades Principales

1.  **Gestión de Horarios de Profesores**:
    -   Los profesores pueden registrar su horario semanal para cada asignatura.
    -   Interfaz intuitiva para asignar horas a cada día de la semana.

2.  **Cálculo Automático de Horas por Trimestre**:
    -   El sistema calcula automáticamente las horas lectivas reales para cada trimestre.
    -   **Algoritmo inteligente**: Tiene en cuenta el calendario escolar registrado previamente, excluyendo días festivos y periodos vacacionales definidos en la base de datos.
    
3.  **Generación de Calendario Visual (PDF)**:
    -   Generación automática de un PDF con la temporalización visual del curso.
    -   El PDF muestra el calendario completo organizado por meses y trimestres, destacando festivos y eventos.
    -   Ideal para planificación docente y cumplimiento normativo.

## 🛠️ Stack Tecnológico

-   **Framework PHP**: [Laravel](https://laravel.com/)
-   **Panel de Administración**: [FilamentPHP](https://filamentphp.com/) (Gestión de recursos, tablas y formularios)
-   **Generación de PDF**: [laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
-   **Base de Datos**: MySQL

## 📋 Requisitos Previos

-   PHP ^8.2
-   Composer
-   Node.js & NPM

## 🔧 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en local:

1.  **Clonar el repositorio**:
    ```bash
    git clone <url-del-repositorio>
    cd ProyectoAbsentismo
    ```

2.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```

3.  **Instalar dependencias de Frontend**:
    ```bash
    npm install
    npm run build
    ```

4.  **Configurar entorno**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Base de Datos**:
    Configura tu conexión a base de datos en el archivo `.env` y ejecuta las migraciones:
    ```bash
    php artisan migrate
    ```

6.  **Crear usuarios en el sistema**:
    El proyecto incluye un comando personalizado para crear usuarios con roles específicos (admin o profesor):

    ```bash
    # Crear un administrador
    php artisan make:user --role=admin

    # Crear un profesor
    php artisan make:user --role=profesor
    ```

## 📄 Uso

1.  Accede a `/admin` e inicia sesión.
2.  Configura el **Calendario Escolar** con los días festivos.
3.  Crea los **Cursos** definiendo las fechas de inicio y fin de cada trimestre.
4.  Los profesores pueden acceder a **Horarios** para registrar sus horas semanales.
5.  Desde la sección de **Cursos**, se puede descargar el PDF de temporalización.

---
Desarrollado por Mario como parte del proyecto de subida de nota.
