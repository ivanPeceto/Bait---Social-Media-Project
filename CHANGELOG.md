# Changelog

## [feature/backend/search] - 2025-10-16

_(Cambios realizados por @jmrodriguezspinker)_

### Added

* **Métodos en el backend**:

  * Se añadieron los métodos `getUserByUsername()` y `getUserByName()` al backend para la búsqueda de usuarios.
  * Se añadió el recurso `SearchResource` para estructurar la respuesta de las búsquedas de usuario.

* **Rutas de API**:

  * Se agregaron las rutas correspondientes a los métodos de búsqueda mencionados en el archivo `api.php`.

### Docs

* **Documentación OpenAPI**:

  * Se actualizó el archivo `api-docs.json` para documentar los nuevos endpoints de búsqueda.
  * Se añadió el esquema `PaginationSchema` para estandarizar la documentación de respuestas paginadas.

## [mergetest/docs-tests] - 2025-10-16

_(Cambios realizados por @juancruzct)_

### Added

* **Endpoints de Seguidores/Seguidos**:
    * Se añadió el endpoint `GET /api/users/{user}/followers` para listar los seguidores de un usuario.
    * Se añadió el endpoint `GET /api/users/{user}/following` para listar los usuarios a los que sigue un usuario.
* **Feed Personalizado**:
    * Se implementó el endpoint `GET /api/feed` para la carga inicial del feed, mostrando posts de los usuarios seguidos y del propio usuario.
    * Se implementó la actualización del feed en tiempo real mediante websockets, disparando el evento `NewPost` que notifica a los seguidores a través de canales privados.

### Fixed

* **Corrección en el Feed**: Se solucionó un error de `SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'id' in field list is ambiguous` en el `FeedController` al especificar `users.id` en la consulta.

## [feature/frontend/profile] - 2025-10-19

_(Cambios realizados por @facu24fm)_

### Added
- Perfil: Subida interactiva de avatar y banner con vista previa y confirmación.
- Perfil: Mostrar la fecha de registro del usuario ("Unido a Bait en...").
- Perfil: Se añadió el menú de eliminación de publicaciones (menú de 3 puntos).

### changed
- Perfil: Se actualizó el diseño del encabezado para que coincida con la referencia de diseño (avatar a la izquierda, botón de edición a la derecha).
- Perfil: Se ajustó el estilo del feed de publicaciones para que coincida con el feed de inicio.
- Diseño: Se ajustaron las columnas del diseño principal y los divisores verticales.
- Diseño: Se añadió el logotipo centrado a la barra de navegación lateral.
- Diseño: Se actualizó el estilo de la barra de búsqueda de la barra de navegación superior.

### Fixed
- Perfil: Se aseguró que el avatar del perfil principal se muestre como un círculo perfecto.
- Perfil: Se corrigió el error de TypeScript por la falta de la propiedad `created_at` en el modelo de usuario.
- Perfil: Se corrigió el error de TypeScript al pasar un número en lugar de una cadena a `loadUserPosts`.
- i18n: Se registró la configuración regional en español para el formato de fecha (por ejemplo, nombres de meses).

## [features/frontend/posts] - 2025-10-16

_(Cambios realizados por @facu24fm)_

### Added

- **Visualización de Perfiles:** Se implementó la página de perfil de usuario. Ahora puede mostrar tanto el perfil del usuario que ha iniciado sesión (en la ruta `/profile`) como el perfil público de cualquier otro usuario (en `/profile/:id`).
- **Eliminación de Posts:** Los usuarios ahora pueden eliminar sus propias publicaciones directamente desde el feed a través de un nuevo ícono.


### Corregido (Fixed)
- **UI (Interfaz de Usuario):** Se corrigió un problema de CSS que causaba que las palabras muy largas en el contenido de un post se salieran de su contenedor.

## [mergetest/docs-tests] - 2025-10-15

_(Cambios realizados por @juancruzct)_

### Observations

* Se detectó un bug crítico en la lógica de subida de avatares y banners: al reemplazar una imagen, si el usuario tenía asignada la imagen por defecto, esta era eliminada de la base de datos, provocando errores en operaciones posteriores.
* Se observó que el `BannerController` devolvía un error `401 Unauthenticated` de forma inconsistente debido a la forma en que se inicializaba la autenticación en su constructor.
* Se identificó que el sistema de notificaciones utilizaba una implementación personalizada incompatible con el estándar de Laravel, lo que causaba múltiples errores al crear y consultar notificaciones.

### Added

* Nuevo endpoint `GET /api/posts/{post}/user-reaction` para verificar la reacción de un usuario a un post específico.
* Nuevo endpoint `GET /api/users/{user}/posts` para obtener todos los posts de un usuario.
* Nuevo endpoint `GET /api/users/{user}` para visualizar perfiles públicos de otros usuarios.

### Changed

* **Refactorización del `BannerController`**: Se eliminó la inyección del guard de autenticación en el constructor para usar el helper `auth()` estándar, solucionando errores `401`.
* **Limpieza de API**: Se eliminaron los endpoints redundantes `GET /api/auth/me`, `GET /api/avatars/{avatar}` y `GET /api/banners/{banner}` para simplificar la API.
* Se cambió el método de la ruta para actualizar posts de `PATCH` a `PUT` en `routes/api.php` para coincidir con la documentación de Swagger.
* **Mejora de Privacidad en `UserResource`**: Se modificó el `UserResource` para que el campo `email` solo sea visible para el propietario del perfil, ocultándolo en las vistas de perfiles públicos.

### Fixed

* **Corrección en `AvatarController` y `BannerController`**: Se añadió una verificación para prevenir que la imagen por defecto sea eliminada durante el proceso de subida de una nueva.
* **Corrección de Relaciones Faltantes**:
    * Se añadió la relación `posts()` al modelo `User`.
    * Se añadió la relación `reactions()` al modelo `Post`.
* **Corrección de Namespaces**: Se añadieron los `use` statements correctos en `ProfileController` y `User` para importar `PostResource` y `Post` desde el módulo `Multimedia`.
* **Refactorización del Sistema de Notificaciones**: Se migró el sistema de notificaciones a la implementación estándar de Laravel para usar UUIDs. Esto incluyó la corrección de la migración `create_notifications_table`, la actualización del `NotificationController`, `NotificationResource`, `UpdateNotificationRequest` y la eliminación de relaciones personalizadas en el modelo `User`.
* **Corrección en Clases de Notificación**: Se solucionaron errores de `Undefined property` y de sintaxis en `NewRepostNotification`, `NewReactionNotification` y `NewFollowNotification`.
* **Documentación de Swagger**: Se corrigieron los códigos de respuesta esperados en los endpoints de subida de `Avatar` y `Banner` a `201 Created`.


## [1.1.4] - 2025-10-07

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
