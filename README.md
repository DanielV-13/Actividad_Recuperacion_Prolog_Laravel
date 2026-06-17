# RPG Prolog Bot

**Materia:** Lenguajes de Programación | **Periodo:** 2026-1 | **Estado:** Completado

Interfaz web en **Laravel** que consulta una **base de conocimiento en SWI-Prolog**
para simular un juego de rol (RPG). El usuario interactúa con un **chatbot**: escribe
una instrucción, el sistema arma la consulta Prolog correspondiente, la ejecuta sobre
la base de conocimiento y narra el resultado.

## Equipo de trabajo

- [Daniel Vaca](https://github.com/) <!-- reemplaza con tu URL de perfil -->

## Capturas / Demo

![Vista principal](docs/screenshots/main.png)

> Demo local en `http://127.0.0.1:8000`

## Funcionalidad

- [x] Base de conocimiento de jugadores, enemigos, misiones, armas e inventarios en Prolog
- [x] 2 reglas nuevas (`tiene_arma/1`, `es_alto_nivel/1`) sobre el juego de clase
- [x] 3 jugadores nuevos, 3 enemigos con vida y fuerza de ataque en las armas
- [x] Ejecución de ataque individual y grupal con narración (constructor de oraciones)
- [x] Chatbot en Laravel que traduce instrucciones a consultas Prolog reales

## Tecnologías

`PHP 8.2` | `Laravel 12` | `SWI-Prolog` | `Tailwind (CDN)`

## Ejecución

Requisitos previos: **PHP 8.2+**, **Composer** y **SWI-Prolog** instalados.

```bash
# 1. Instalar dependencias
composer install

# 2. Preparar entorno
copy .env.example .env        # en Linux/Mac: cp .env.example .env
php artisan key:generate

# 3. (Solo si "swipl" no esta en el PATH) indicar la ruta en .env
# SWIPL_PATH="C:/Program Files/swipl/bin/swipl.exe"

# 4. Levantar el servidor
php artisan serve
# Abrir http://127.0.0.1:8000
```

### Comandos del chatbot

| Instrucción | Ejemplo |
|-------------|---------|
| Listar personajes | `personajes` |
| Listar enemigos | `enemigos` |
| Listar misiones | `misiones` |
| Listar armas | `armas` |
| Ver inventario | `inventario Kratos` |
| ¿Puede aceptar misión? | `acepta Elara en m2` |
| Ataque (individual o grupal) | `ataque Kratos, Nathan Drake vs Valkyria` |
| Reporte de grupo / misión | `reporte Elara, Rin en m2` |
| Ayuda | `ayuda` |

## Métricas de Progreso

| Indicador | Valor |
|-----------|-------|
| Commits totales | [Número] |
| Issues/PRs fusionados | [X/Y] |
| Cobertura de pruebas | N/A |
| Última actualización | 2026-06-14 |

## Reflexión y Aprendizajes

- **Habilidades desarrolladas:** integración de un lenguaje lógico (Prolog) con un
  framework MVC (Laravel); diseño de un parser de comandos; manejo de procesos
  externos desde PHP.
- **Qué funcionó bien:** generar el objetivo Prolog en un archivo temporal evitó los
  problemas de comillas entre Windows y Linux; reutilizar el constructor de oraciones
  para narrar los ataques.
- **Qué se podría mejorar:** añadir autocompletado de nombres y validación previa
  contra los hechos antes de consultar Prolog.
- **Conceptos clave aplicados:** hechos y reglas, unificación, recursión, backtracking
  y el modelo declarativo frente al imperativo de PHP.
