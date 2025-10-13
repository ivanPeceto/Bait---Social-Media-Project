# Changelog

## [2.1.2]

**Author:** _@jmrodriguezspinker_
**Date:** 2025-10-12
**Branch:** `feature/frontend/auth-ui`

#### 🚀 Added

* Se agregó la variable global `API_URL` en Angular (`environment.ts`) para facilitar la configuración de endpoints.
* Se añadió un botón de **logout** en el frontend, junto con su lógica de cierre de sesión.
* Se habilitó **CORS** en el backend (`config/cors.php`) para permitir peticiones cruzadas durante el desarrollo.

#### 🛠️ Changed

* Se modificó `.env.example` cambiando `REVERB_SECRET` por `REVERB_APP_SECRET` para que funcione correctamente **Reverb**.
* Se actualizó la documentación (`api-docs.json`) con cambios menores.
* Se actualizó el script `start` en `package.json` agregando `--poll` para habilitar **hot reload** en entornos donde el file watcher no detecta cambios.
* Se adaptó el interceptor de errores de autenticación y el servicio `auth.service.ts` para permitir definir rutas dinámicas desde el `environment`.
* Se ajustó la lógica de **login** y **registro** para que se conecten correctamente con el backend.
* Se modificó el mínimo de caracteres para contraseñas a **8** en login y register.
* Se actualizó el `registerController` para incluir el campo `name` y ajustar validaciones.

#### 🧹 Chore

* Se corrigió la importación del componente `Login` eliminando los `{}` innecesarios.


## [2.1.1]

**Author:** _@jmrodriguezspinker_
**Date:** 2025-10-11
**Branch:** `feature/frontend/auth-ui`

#### 🚀 Added

* Se agregaron los seeders `DatabaseSeeder` y `UserSeeder` en `backend/api-bait/database/seeders`.

#### 🛠️ Changed

* Se modificó el archivo `docker-compose.yml` para incluir el servicio de **phpMyAdmin**.
* Se agregó el script `start_server_mod` para incluir los comandos `flads init-api` y `fresh`.
* Se actualizó `.env.example` cambiando la variable `REVERB_SECRET` a `REVERB_APP_SECRET`.
* Se añadió la línea comentada `# AUTH_GUARD` en `.env.example` como referencia para futuras configuraciones.

## [1.1.3] - 2025-10-06

_(Cambios realizados por @jmrodriguezspinker)_

### Observations

* Se detectaron posibles errores en `PostReactionController`. Se recomienda realizar una revisión y pruebas adicionales para validar su comportamiento.
* Solo `AuthController` ha sido corroborado hasta el momento. El resto de los controladores requiere verificación.
* Se sugiere que los métodos privilegiados de `UserData` sean agrupados en una misma clase para mejorar la cohesión y organización del código.

### Documentation

* Se finalizó la documentación del módulo `Multimedia`.
  
## [1.1.2] - 2025-10-05

### Fixed

* Se corrigió un error de *copy-paste* en `BannerController::destroyForUser()`, donde se hacía referencia a "avatar" cuando en realidad correspondía a "banner".

### Observations

* Se detectó que `ProfileController::show` es muy similar o incluso idéntico a `AuthController::me`. Se recomienda evaluar una posible refactorización para evitar duplicación de lógica.

### Documentation

* Se finalizó la documentación del módulo `UserData`.
  
## [1.1.1] - 2025-10-05

### Added

* Se crearon nuevos métodos para las rutas `GET /` y `GET /ping` en el archivo `routes/api.php`.

* Los controladores correspondientes se encuentran en:


## [1.1.2] - 2025-10-05

### Fixed

* Se corrigió un error de *copy-paste* en `BannerController::destroyForUser()`, donde se hacía referencia a "avatar" cuando en realidad correspondía a "banner".

### Observations

* Se detectó que `ProfileController::show` es muy similar o incluso idéntico a `AuthController::me`. Se recomienda evaluar una posible refactorización para evitar duplicación de lógica.

### Documentation

* Se finalizó la documentación del módulo `UserData`.
  
## [1.1.1] - 2025-10-05

### Added

* Se crearon nuevos métodos para las rutas `GET /` y `GET /ping` en el archivo `routes/api.php`.

* Los controladores correspondientes se encuentran en:

  ```
  app/Modules/Healthcheck/Http/Controllers
  ```

* Ambos métodos retornan la misma respuesta: `{"status": "ok"}`.

* La ruta `GET /` entra en conflicto con la misma ruta definida en `routes/web.php`, ya que ambas comparten el endpoint raíz (`/`).

  Se recomienda revisar cuál de las dos rutas debe tener prioridad o mover una de ellas a un prefijo distinto para evitar el conflicto.

* Se definieron etiquetas OpenAPI (`@OA\Tag`) en `App\Http\Controllers` para evitar la generación automática de descripciones en la documentación de Swagger.

  ```php
  * @OA\Tag(name="Health")
  * @OA\Tag(name="Auth")
  * @OA\Tag(name="Avatar")
  * @OA\Tag(name="Banner")
  * @OA\Tag(name="Profile")
  * @OA\Tag(name="User Management")
  * @OA\Tag(name="User Roles")
  * @OA\Tag(name="User States")
  * @OA\Tag(name="Comment")
  * @OA\Tag(name="Multimedia Content")
  * @OA\Tag(name="Posts")
  * @OA\Tag(name="Post Reaction")
  * @OA\Tag(name="Repost")
  * @OA\Tag(name="Chat")
  * @OA\Tag(name="Follow")
  * @OA\Tag(name="Message")
  * @OA\Tag(name="Privileged")
  ```

* Se añadió la definición de esquema de seguridad para reconocer e identificar rutas protegidas con JWT en Swagger:

  ```php
  * @OA\SecurityScheme(
  *      securityScheme="bearerAuth",
  *      type="http",
  *      scheme="bearer",
  *      bearerFormat="JWT",
  *      description="Use a JWT token to authenticate"
  * )
  ```

* Se creó el archivo `database/seeders/RoleSeeder.php`, que llama a la clase `UserRolesSeeder` ubicada en `database/seeders/UserRolesSeeder.php`, porque no se identificó a tiempo por qué `UserRolesSeeder` no se ejecutaba directamente con `db:seed`.

* Antes de probar las APIs públicas, es necesario ejecutar los seeders para poblar la base de datos con datos esenciales:

  ```bash
  php artisan db:seed --class=BannerSeeder --class=UserRolesSeeder --class=UserStateSeeder --class=AvatarSeeder
  ```

* Se creó la carpeta `app/Docs` y se añadieron los archivos correspondientes para la documentación específica del `AuthController`.

### Changed

* Se modificó la configuración de Swagger UI en `config/l5-swagger.php` para que las rutas se muestren expandidas por defecto en la documentación web.

  ```php
  'defaults' => [
      'documentation' => [
          'ui' => [
              'docExpansion' => 'list',
          ],
      ],
  ],
  ```

* Esta configuración reemplaza el valor anterior (`'none'` o ausente) y permite visualizar todas las rutas desplegadas automáticamente al acceder a `/api/documentation`.


## [1.1.0] - 2025-10-05

### Bug Fixes

* Se corrigió un conflicto de merge en `tests/Traits/WithUser.php` que causaba errores de sintaxis en los tests. Se eliminaron las marcas de conflicto (`<<<<<<<`, `=======`, `>>>>>>>`) para permitir la ejecución correcta de PHPUnit.

### Improvements

* Se actualizaron los tests para usar atributos PHP `#[Test]` en lugar de metadata en comentarios docblock, preparando el código para compatibilidad con PHPUnit 12 y eliminando warnings de depreciación. (/** @test */ a #[Test]). El problema surgía cuando se ejecutaba `docker compose exec backend php artisan test --filter=ExampleTest`.

### Added

* Integración de Swagger (L5-Swagger) para documentación automática de la API REST.
* La documentación está disponible en `/api/documentation`.
* Anotaciones OpenAPI añadidas en el controlador base (`App\Http\Controllers\Controller`).
* Endpoint local disponible en `http://127.0.0.1:8001`.

### Changed

* **Configuración de L5-Swagger (`config/l5-swagger.php`)**:

  * Se añadieron las rutas:

    * `app/Modules/Multimedia/Http/Controllers`
    * `app/Modules/UserInteractions/Http/Controllers`
    * `app/Modules/UserData/Http/Controllers`
  * Estas rutas fueron incorporadas al array `'annotations'` dentro de `'config/l5-swagger'`, permitiendo que L5-Swagger escanee los controladores de estos módulos y genere la documentación API correspondiente.

* **Inicialización de controladores para L5-Swagger**:

  * Se corrigieron los nombres de los controladores `MultimediaManagmentController` y `UserManagmentController`, añadiendo la letra faltante "e" para formar `MultimediaManagementController` y `UserManagementController`.
  * En `routes/api.php`, se ajustaron las rutas de estos controladores, reemplazando `app/Http/Controllers/{Controller}` por `app/Modules/{Module}/Http/Controllers/{Controller}`.
  * Dentro de ambos controladores, se actualizó el namespace incorrecto de `App\Http\Controllers` a `App\Modules\{Module}\Http\Controllers`.
  * También se añadió la línea `use App\Http\Controllers\Controller;` para asegurar la correcta herencia desde el controlador base.