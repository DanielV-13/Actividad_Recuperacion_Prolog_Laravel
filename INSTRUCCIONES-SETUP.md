# Guía paso a paso: montar, probar y subir el proyecto

> Hecho para Windows (cmd / PowerShell). Si usas Mac/Linux, los comandos de Git y
> artisan son iguales; solo cambian `copy`→`cp`, `ren`→`mv`, `rmdir`→`rm -r`.

---

## 0. Requisitos (instalar una sola vez)

Verifica en una terminal que tienes las 3 herramientas:

```bash
php --version        # PHP 8.2 o superior
composer --version   # Composer 2.x
swipl --version      # SWI-Prolog 9.x
```

- Si falta **PHP/Composer**: instala [Laravel Herd](https://herd.laravel.com/windows) (trae PHP + Composer) o XAMPP + Composer.
- Si falta **SWI-Prolog**: descárgalo de https://www.swi-prolog.org/download/stable
  y, durante la instalación, marca la opción de **agregarlo al PATH**.

---

## 1. Generar el esqueleto de Laravel y unir mis archivos

Esta carpeta (`Actividad Recuperacion-Daniel Vaca`) contiene SOLO los archivos
personalizados del proyecto (la base Prolog, el controlador, el servicio, la vista
y el README). Falta el esqueleto de Laravel (vendor, artisan, config, etc.).

Abre una terminal **dentro de la carpeta `CODIGO`** y ejecuta:

```bash
:: 1) Crea un Laravel limpio en una carpeta temporal
composer create-project laravel/laravel laravel-base

:: 2) Copia mis archivos personalizados encima del esqueleto (los fusiona)
robocopy "Actividad Recuperacion-Daniel Vaca" "laravel-base" /E

:: 3) Borra la carpeta parcial y renombra la completa con el nombre final
rmdir /S /Q "Actividad Recuperacion-Daniel Vaca"
ren laravel-base "Actividad Recuperacion-Daniel Vaca"

:: 4) Entra al proyecto ya completo
cd "Actividad Recuperacion-Daniel Vaca"
```

> `robocopy` fusiona árboles de carpetas; si un archivo ya existe (como
> `routes/web.php`), lo reemplaza por el mío. Es lo que queremos.

---

## 2. Configurar el entorno

```bash
copy .env.example .env
php artisan key:generate
```

Solo si `swipl --version` **NO funcionó** en el paso 0 (no está en el PATH),
abre `.env` y agrega al final la ruta a tu ejecutable, por ejemplo:

```env
SWIPL_PATH="C:/Program Files/swipl/bin/swipl.exe"
```

> Importante: durante el desarrollo NO ejecutes `php artisan config:cache`,
> porque el servicio lee `SWIPL_PATH` con `env()`.

---

## 3. Probar la base de conocimiento SOLA (antes de Laravel)

Conviene verificar que el `.pl` funciona por sí mismo:

```bash
swipl prolog/juego.pl
```

Dentro de SWI-Prolog prueba estas consultas (escribe cada línea y Enter):

```prolog
?- puede_aceptar('Elara', m2).
?- ejecutar_ataque(['Kratos','Nathan Drake'], 'Valkyria', M).
?- ejecutar_ataque(['Crash Bandicoot'], 'Jabali', M).
?- generar_reporte_grupo(['Elara','Rin'], m2, M).
?- halt.
```

Resultados esperados:

- `puede_aceptar('Elara', m2)` → **true** (Elara nivel 5 ≥ dificultad 5).
- `ejecutar_ataque(['Kratos','Nathan Drake'], 'Valkyria', M)` → M dice que
  **derrotaron** a Valkyria (250 + 90 = 340 ≥ 300 de vida).
- `ejecutar_ataque(['Crash Bandicoot'], 'Jabali', M)` → **derrotó** (30 ≥ 25).

Si todo responde bien, sal con `halt.`

---

## 4. Levantar Laravel y probar el chatbot

```bash
php artisan serve
```

Abre en el navegador: **http://127.0.0.1:8000**

Prueba escribiendo (o usando los botones de ejemplo):

```
personajes
inventario Kratos
acepta Elara en m2
ataque Kratos, Nathan Drake vs Valkyria
ataque Kratos vs Valkyria
reporte Elara, Rin en m2
ayuda
```

- `ataque Kratos, Nathan Drake vs Valkyria` → derrotaron.
- `ataque Kratos vs Valkyria` → Valkyria **sobrevive** (250 < 300). Así ves los 2 estados.

**Toma una captura** de la pantalla con una conversación y guárdala como
`docs/screenshots/main.png` (la rúbrica del portafolio la exige).

Para detener el servidor: `Ctrl + C`.

---

## 5. Subir a GitHub (repositorio propio del proyecto)

1. En GitHub crea un repositorio **nuevo y vacío** (con tu correo institucional),
   por ejemplo `rpg-prolog-bot`. NO marques "Add a README" (ya tienes uno).

2. En la terminal, dentro de la carpeta del proyecto:

```bash
git init
git add .
git commit -m "feat: chatbot Laravel + base de conocimiento Prolog del juego RPG"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/rpg-prolog-bot.git
git push -u origin main
```

3. (Recomendado por la guía) trabaja con ramas y commits atómicos en futuros cambios:

```bash
git checkout -b feature/nueva-regla
:: ...editas...
git add .
git commit -m "feat: agrega regla puede_huir/2"
git push origin feature/nueva-regla
:: luego abres un Pull Request hacia main en GitHub
```

4. **Enlaza el proyecto en tu portafolio** `tu-usuario.github.io`, dentro de la
   carpeta `2026-1 - Lenguajes de Programación`, agregando el enlace a este repo.

---

## Estructura del proyecto (archivos clave)

```
Actividad Recuperacion-Daniel Vaca/
├── prolog/
│   └── juego.pl                          ← Base de conocimiento (Prolog)
├── app/
│   ├── Services/PrologService.php        ← Ejecuta swipl y captura la salida
│   └── Http/Controllers/JuegoController.php ← Interpreta comandos y arma la consulta
├── resources/views/juego/chatbot.blade.php  ← Interfaz del chatbot
├── routes/web.php                        ← Rutas
├── docs/screenshots/                     ← Aquí va main.png
└── README.md
```
