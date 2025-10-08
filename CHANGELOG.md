# Changelog

## [1.1.7] - 2025-10-08

_(Cambios realizados por @jmrodriguezspinker)_

### Changed

* Se corrigió la relación `user()` en el modelo `Post` para que devuelva una relación `belongsTo` hacia el modelo `User`, reflejando correctamente que un post pertenece a un único usuario.
* Se agregó la relación `multimediaContents()` en el modelo `Post` como una relación `hasMany` hacia el modelo `MultimediaContent`, permitiendo acceder a todos los contenidos multimedia asociados a un post.
* Se actualizaron los imports en el modelo `Post` para incluir `MultimediaContent` y las clases de relaciones `BelongsTo` y `HasMany` de Eloquent.
* Se mejoró la estructura y legibilidad del código en el modelo `Post` para facilitar su mantenimiento y comprensión.

## [1.1.6] - 2025-10-07

_(Cambios realizados por @ivanPeceto)_

### Observations

* Se detectó la falta de validación de estado de usuario para el caso de los usuarios que están suspendidos. 
* Queda pendiente añadir un middleware que invalide cualquier JWT generado antes de la suspensión de la cuenta.
* Se detectó problemas con la dependencia de swagger al crear los contenedores desde 0.
* Se detectó la incorrecta representación de las respuestas de la api en algunos schema de la documentación de swagger
* Se observó la falta de un endpoint crucial en el módulo de comentarios, el de devolver todos aquellos asociados a un posteo.
* El modelo de Notification entra en conflicto con el estándar de Laravel, por lo que hubo que añadir métodos que "adapten" su funcionamiento.

### Added

* Nuevo seeder `DatabaseSeeder.php` para generar un usuario administrador y uno moderador iniciales
* Nuevo archivo `.dockerignore` para resolver problemas de buildeo de contenedores con la dependencia de swagger.
* Nueva colección de postman `Bait_API.postman_collection.json` para testear manualmente el funcionamiento de todos los endpoints.

### Changed

* Método `login` en `AuthController.php` para verificar el estado del usuario.
* Comentado Swagger dentro de los providers de `app.php`
* Volúmenes generados en `docker-compose.yml` 
* Se añadió el método `showFromPost` a `CommentController.php` junto a un nuevo endpoint asociado.

## [1.1.5] - 2025-10-07

_(Cambios realizados por @jmrodriguezspinker)_

### Changed

* Se actualizó el método `uploadAvatar` para preservar los avatares previamente subidos en base de datos y almacenamiento:
  * La lógica anterior eliminaba el registro y archivo del avatar anterior.
  * Ahora, los nuevos avatares se agregan y el campo `avatar_id` en la tabla `users` se actualiza para apuntar al nuevo, manteniendo así un historial y evitando pérdida de datos.
* Se modificó el `role_id` en `AdminUserSeeder` para reflejar correctamente el rol de administrador (cambio de 1 a 3).
* Se cambió el método HTTP en las rutas de `PostController` de `patch` a `put`.
* Se corrigió la obtención del ID del usuario autenticado en la función `store` de `PostReactionController`: de `$user_id = auth()->user()->id()` a `$user_id = auth()->user()->id`.
* Se mejoró el formato y claridad de los comentarios en el módulo `UserInteractions`.

### Added

* Se añadieron los campos `created_at` y `updated_at` en los Seeders, excepto en `ReactionTypesSeeders`.
* Se documentó el error 401 en los módulos `UserData` y `Multimedia`, que no estaba contemplado previamente.
* Se agregaron las reglas `role_id` y `state_id` en la validación de `UpdateProfileRequest`.

### Deprecated

* Se deshabilitó el método `me()` en `AuthController`, dado que su funcionalidad es duplicada por `ProfileController::show`, junto con su documentación.
* Se deshabilitó la prueba `it_returns_the_authenticated_user_data()` en `tests/Feature/UserData/AuthTest.php`.
* Se comentaron las rutas:  
  - `Route::get('me', [AuthController::class, 'me'])->middleware('auth:api')->name('auth.me')`  
  - `Route::get('/ping', [PingController::class, 'ping'])`  
  en el archivo `routes/api.php`.

### Documentation

* Se completó la documentación en los siguientes controladores y módulos:  
  `ProfileController`, `UserRoleController`, `UserStateController`, `UserManagementController`, `RepostController`, `PostReactionController`, `PostController`, `MultimediaContentController`, `CommentController`.


## [1.1.4] - 2025-10-07

_(Cambios realizados por @jmrodriguezspinker)_

### Added

* Se creó `AdminUserSeeder` para generar un usuario administrador por defecto:  
  - Email: `admin@example.com`  
  - Contraseña: `admin`

* Se creó `DatabaseSeeder`, el cual invoca todos los seeders, permitiendo ejecutar `php artisan db:seed` una sola vez para poblar toda la base de datos.

* Se agregó el servicio `phpmyadmin` al archivo `docker-compose.yml`, utilizando las variables `DB_USERNAME` y `DB_PASSWORD` del archivo `.env` para la conexión a la base de datos.

### Changed

* Se modificó el script `start_server.sh` para que inicie PhpMyAdmin junto con el resto de los servicios Docker.
* Se modificó el script `start_server.sh` para que ejecute automáticamente los siguientes comandos de Laravel al utilizar los flags `--build` o `--api`:
  - `php artisan key:generate`
  - `php artisan jwt:secret --force`
  - `php artisan migrate`
  - `php artisan db:seed`

  Esto permite inicializar completamente el backend de Laravel sin pasos manuales adicionales después de levantar los servicios.

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