# Guía paso a paso: probar el proyecto y subir cambios

> Pensada para Windows (cmd / PowerShell). En Mac/Linux los comandos de Git y
> artisan son iguales; solo cambia `copy`→`cp`.

El proyecto **ya tiene el esqueleto completo de Laravel 12 incluido** y un `.env`
listo para usar. No hay que generar nada desde cero: solo instalar dependencias y
levantar el servidor.

---

## 0. Requisitos (instalar una sola vez)

Verifica en una terminal que tienes las 3 herramientas:

```bash
php --version        # PHP 8.2 o superior
composer --version   # Composer 2.x
swipl --version      # SWI-Prolog 9.x
```

- Si falta **PHP/Composer**: instala [Laravel Herd](https://herd.laravel.com/windows)
  (trae PHP + Composer) o XAMPP + Composer.
- Si falta **SWI-Prolog**: descárgalo de https://www.swi-prolog.org/download/stable
  y, durante la instalación, **marca la opción de agregarlo al PATH**.

---

## 1. Instalar dependencias y levantar el proyecto

Abre una terminal **dentro de la carpeta del proyecto** (`Actividad Recuperacion-Daniel Vaca`)
y ejecuta:

```bash
# 1. Descargar las dependencias de PHP (crea la carpeta vendor/, no se sube a git)
composer install

# 2. Levantar el servidor de desarrollo
php artisan serve
```

Abre el navegador en **http://127.0.0.1:8000**. Verás el chatbot del juego.

> El `.env` ya trae `APP_KEY` generada y usa sesión y caché en **archivos**, así que
> **no necesitas base de datos** ni `php artisan migrate`.

### Si "swipl" no está en el PATH

Edita el archivo `.env` y pon la ruta completa al ejecutable, por ejemplo:

```
SWIPL_PATH="C:/Program Files/swipl/bin/swipl.exe"
```

---

## 2. Probar el chatbot

Escribe estas instrucciones en la caja de texto (o usa los botones de ejemplo):

| Instrucción | Qué hace |
|-------------|----------|
| `personajes` | Lista los 6 personajes con nivel y vida |
| `enemigos` | Lista los 3 enemigos con su vida |
| `misiones` | Lista las misiones con dificultad y XP |
| `armas` | Lista las armas y sus puntos de ataque |
| `inventario Kratos` | Muestra el inventario de un personaje |
| `acepta Elara en m2` | ¿Tiene nivel para esa misión? |
| `ataque Kratos, Nathan Drake vs Valkyria` | Combate grupal narrado (340 de daño → la derrotan) |
| `ataque Crash Bandicoot vs Valkyria` | Combate individual (30 de daño → sobrevive) |
| `reporte Elara, Rin en m2` | Evalúa XP + equipamiento del grupo |
| `ayuda` | Muestra todos los comandos |

---

## 3. Subir cambios a GitHub

Cada vez que cambies algo y lo quieras guardar en GitHub:

```bash
git add .
git commit -m "describe aquí qué cambiaste"
git push
```

`add` prepara los cambios, `commit` los guarda en tu historial local y `push` los
envía a GitHub.

> La carpeta `vendor/` y el archivo `.env` **no se suben** (están en `.gitignore`),
> porque `vendor/` se regenera con `composer install` y `.env` puede tener datos
> propios de cada máquina. Por eso quien clone el repo solo debe correr
> `composer install`.

---

## 4. Estructura del proyecto (lo importante)

```
prolog/juego.pl                          Base de conocimiento (hechos + reglas)
app/Services/PrologService.php           Puente PHP <-> SWI-Prolog
app/Http/Controllers/JuegoController.php Traduce comandos a consultas Prolog
resources/views/juego/chatbot.blade.php  Interfaz web del chatbot
routes/web.php                           Las 3 rutas (/, /consultar, /limpiar)
.env                                     Configuración (ya lista)
```

El resto de carpetas (`bootstrap`, `config`, `public`, `storage`, etc.) son el
esqueleto estándar de Laravel.
