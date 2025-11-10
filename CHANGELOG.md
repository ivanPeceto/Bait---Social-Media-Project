## [feat/websockets] - 2025-11-10

_(Cambios realizados por @ivanPeceto)_

### Changed
* Crea un nuevo endpoint que devuelve todos los usuarios con los que podemos iniciar un chat.
* Añade los métodos y documentación necesarios en `ChatController`.

### Affects
* `backend/bait-api/app/Modules/UserInteractions/Http/Controllers/ChatController.php`
* `backend/bait-api/routes/api.php`

## [feat/websockets] - 2025-11-10

_(Cambios realizados por @ivanPeceto)_

### Adds
* `frontend/src/app/core/services/chats.service.ts`
* `frontend/src/app/core/models/chats.model.ts`
* `frontend/src/app/features/chat/chat-sidebar/chat-sidebar.component.html`
* `frontend/src/app/features/chat/chat-sidebar/chat-sidebar.component.ts`
* `frontend/src/app/features/chat/chat-window/chat-window.component.html`
* `frontend/src/app/features/chat/chat-window/chat-window.component.ts`
* `frontend/src/app/features/chat/chat.component.ts`

### Changed
* Implementa los nuevos módulos de chat en `main.component.ts`

### Affects
* `frontend/src/app/core/models/api-payloads.model.ts`
* `frontend/src/app/layout/main/main.component.ts`

## [feat/websockets] - 2025-11-09

_(Cambios realizados por @ivanPeceto)_

### Changed 
* Corrige llamado a relación incorrecta en `SendNewRepostNotification`
* Añade implementacion de Queue's a `SendNewReactionNotification` 
* Añade atributo "action" a `CreateReactionPayload` en `api-payloads.model.ts`.
* Refactoriza el método `toggleReaction` ahora llamado `manageReaction` en `interaction.service.ts`
* Añade verificación en el backend para evitar que un usuario repostee su propio posteo. Afecta `RepostController`.
* Añade el helper `updatePostInArray` en `profile.component.ts`.

## Bufix
* Corrige inconsistencias en el código de `PostReactionController` que impedían el funcionamiento de las notificaciones.

### Affects
* `backend/bait-api/app/Listeners/SendNewRepostNotification.php`
* `backend/bait-api/app/Listeners/SendNewReactionNotification.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/PostReactionController.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/RepostController.php`
* `frontend/src/app/core/models/api-payloads.model.ts`
* `frontend/src/app/core/services/interaction.service.ts`
* `frontend/src/app/features/home/home.ts`
* `frontend/src/app/features/profile/profile.component.ts`

## [fix/front/ws] - 2025-11-08

_(Cambios realizados por @ivanPeceto)_

### Changed
* Añade el nombre "NewPost" con `broadcastAs` para que el frontend lo pueda interpretar el frontend.
* Corrige nombre de canal "App.Models.User{id}" al nombre correcto "users.{id}" en `home.ts`
* Corrige error de tipeo en el FeedController

### Affects
* `backend/bait-api/app/Events/NewPost.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/FeedController.php`
* `frontend/src/app/features/home/home.ts`

## [feature/frontend/websocket] - 2025-11-07

_(Cambios realizados por @jmrodriguezspinker)_

### Fixed

* Adaptación final del componente principal `MainComponent` para reflejar la nueva estructura y funcionamiento.
* Adaptación del `NotificationService` para manejar notificaciones en tiempo real vía WebSocket.

### Added

* Adaptación del `MainComponent` para manejar eventos WebSocket en vivo y carga desde backend.
* Nuevo servicio `NotificationListenerService` para manejar notificaciones de usuario en tiempo real.
* Nuevo servicio `EchoService` para gestionar canales privados y eventos en WebSocket.
* Obtención del token JWT y user_id desde `localStorage` para autenticación en tiempo real.
* Middleware agregado para rutas de broadcasting en backend.
* Correcciones y mejoras en el driver Reverb para conexión con Pusher.
* Refactorización e implementación de transmisión en canales privados para notificaciones en backend.
* Nuevas notificaciones para acciones específicas (seguidores, reposts, reacciones) que reemplazan el evento genérico.
* Servicio backend para manejar trabajos en cola vía Redis.
* Agregado soporte para notificaciones WebSocket, configuración de Nginx y phpMyAdmin, assets de Swagger y generación de claves/JWT en Laravel.
* Notificaciones WebSocket integradas en el layout principal de la aplicación.

---

### Archivos afectados

* `frontend/src/app/layout/main/main.component.html`
* `frontend/src/app/core/services/notification.service.ts`
* `frontend/src/app/layout/main/main.component.ts`
* `frontend/src/app/core/services/notification.listener.service.ts`
* `frontend/src/app/core/services/echo.service.ts`
* `frontend/src/app/core/services/auth.service.ts`
* `backend/bait-api/routes/channels.php`
* `backend/bait-api/config/broadcasting.php`
* `backend/bait-api/app/Notifications/NewFollowNotification.php`
* `backend/bait-api/app/Notifications/NewReactionNotification.php`
* `backend/bait-api/app/Notifications/NewRepostNotification.php`
* `backend/bait-api/app/Modules/UserInteractions/Http/Controllers/FollowController.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/RepostController.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/PostReactionController.php`
* `docker-compose.yml`

## [fix/front/ws] - 2025-11-06

_(Cambios realizados por @ivanPeceto)_

### Bugfix
* Corrige redirección incorrecta a storage en `nginx.conf` arreglando el bug de imagenes default inexistentes.
* Sube al repositorio de manera forsoza las imagenes default de avatars y banners.

### Changed
* Refactoriza el endpoint `/feed` en el backend para devolver:
  1. Todos los posteos del mismo usuario.
  2. Todos los reposteos del mismo usuario.
  3. Todos los posteos de los usuarios a los que sigue.
  4. Todos los reposteos de los usuarios a los que sigue
* Añade campo "type" a `RepostResource.php` y `PostResource.php`
* Integra el nuevo campo "type" en `post.model.ts`
* Añade a `api-payloads.model.ts` nuevos tipos para manejar payloads paginados.
* Añade método `getFeed` a `post.service.ts`

### Affects 
* `nginx/nginx.conf.template`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/FeedController.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Resources/RepostResource.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Resources/PostResource.php`
* `backend/bait-api/app/Modules/Multimedia/Http/Controllers/FeedController.php`
* `frontend/src/app/core/models/api-payloads.model.ts`
* `frontend/src/app/core/models/post.model.ts`
* `frontend/src/app/core/services/post.service.ts`

## [fix/front/ws] - 2025-11-05

_(Cambios realizados por @ivanPeceto)_

### Added

* Añade nueva variable `VITE_APP_URL_BASE` en `.env.example`.
* Añade nueva variable `IP_ADDRESS` en `.env.example`.
* Añade actualización automática de la nueva variable `L5_SWAGGER_CONST_HOST` en el `.env`.
* Añade actualización automática de la nueva variable `VITE_APP_URL_BASE` en el `.env`.
* Añade actualización automática de la nueva variable `IP_ADDRESS` en el `.env`.

### Changed

* Pasa el `.env` en `docker-compose.yml` al frontend.
* En `frontend/vite.config.ts` añade la extracción de variables de entorno para objetener la ip del host y reverb_app_key en manera dinámica. 
* Corrige direcciones IP hardcodeadas en `frontend/src/environments/environment.ts` y `frontend/src/app/core/services/echo.service.ts`.
* Corrige reverb_app_key hardcodeada en `frontend/src/app/core/services/echo.service.ts`.

### Affects 

* `start_server.sh`
* `backend/bait-api/app/Events/NewReactionEvent.php`
* `backend/bait-api/app/Events/NewRepost.php`
* `frontend/src/app/features/home/home.ts`
* `.env.example`
* `docker-compose.yml`
* `frontend/vite.config.ts`
* `frontend/src/environments/environment.ts`
* `frontend/src/app/core/services/echo.service.ts`

## [feature/frontend/websocket] - 2025-11-04

_(Cambios realizados por @jmrodriguezspinker)_

### Added

* Añadida la línea 92 a `.env.example` para nuevas configuraciones de entorno.
* Agregados los servicios `nginx` y `phpMyAdmin` al setup de Docker (`docker-compose.yml`).
* Añadidas dependencias `laravel-echo` y `pusher-js` para eventos en tiempo real (`frontend/package.json`, `frontend/package-lock.json`).
* Añadidos los assets de Swagger UI a `public/vendor` (`backend/bait-api/public/vendor/`).
* Nuevos servicios de notificaciones en tiempo real usando Echo y Pusher:

  * `frontend/src/app/core/services/echo.service.ts`
  * `frontend/src/app/core/services/notification.listener.service.ts`
  * `frontend/src/app/core/services/notification.service.ts`
* Agregado `Nginx Dockerfile` y configuración por defecto (`nginx/Dockerfile`).
* Configuración de Nginx para:

  * Servir archivos estáticos con caching (`/vendor/` y `/storage/`)
  * Proxy para conexiones WebSocket (`/ws/`)
  * Proxy para API broadcasting con cabeceras CORS (`/broadcasting/`)

### Changed

* Actualizado `.gitignore` con nuevas reglas (`.gitignore`).
* Configurados permisos y assets de L5Swagger (`backend/bait-api/Dockerfile`).
* Actualizados tags para documentación (`backend/bait-api/app/Http/Controllers/Controller.php`).
* Configurada la conexión a Reverb usando variables de entorno y TLS (`backend/bait-api/config/broadcasting.php`).
* Ajustadas rutas de L5 Swagger para compatibilidad con NGINX (`backend/bait-api/config/l5-swagger.php`).
* Modificada plantilla de Swagger UI para mostrar correctamente la documentación y expandir los docs con NGINX (`backend/bait-api/resources/views/vendor/l5-swagger/index.blade.php`).
* Configurado Vite dev server con host personalizado y HMR (`frontend/vite.config.ts`).
* Integradas notificaciones WebSocket en el layout principal (`frontend/src/app/layout/main/main.component.html`, `main.component.ts`).
 
### Chore

* Generación de Laravel key y JWT secret con limpieza de cache (`start_server.sh`).

### Docs

* Actualizada la documentación generada de la API Swagger (`backend/bait-api/storage/api-docs/api-docs.json`).

### Affects

* `.env.example`
* `.gitignore`
* `docker-compose.yml`
* `backend/bait-api/Dockerfile`
* `backend/bait-api/app/Http/Controllers/Controller.php`
* `backend/bait-api/config/broadcasting.php`
* `backend/bait-api/config/l5-swagger.php`
* `backend/bait-api/resources/views/vendor/l5-swagger/index.blade.php`
* `backend/bait-api/storage/api-docs/api-docs.json`
* `frontend/package.json`
* `frontend/package-lock.json`
* `frontend/vite.config.ts`
* `frontend/src/app/core/services/echo.service.ts`
* `frontend/src/app/core/services/notification.listener.service.ts`
* `frontend/src/app/core/services/notification.service.ts`
* `frontend/src/app/layout/main/main.component.html`
* `frontend/src/app/layout/main/main.component.ts`
* `start_server.sh`
* `nginx/Dockerfile`
* `nginx/nginx.conf.template`

# Changelog
## [fix/back/ws] - 2025 - 11 - 01

## Fixes
* Corrige variables del env file para ser consistentes con el nuevo contenedor de reverb y hacer uso del contenedor de redis.
* Añade tiempo de espera al inicio del contenedor reverb para asegurar que todas las dependencias esten activas.

## Affects
* `.env.example`
* `docker-compose-yml`

## [fix/frontend/integrations-to-main] - 2025-10-31

_(Cambios realizados por @juancruzct)_

### Changed
* Refactorizado el manejo de estado del usuario (`auth.service.ts`, `main.component.ts`, `profile.component.ts`) para eliminar "race conditions" y asegurar que el avatar actualizado se muestre en toda la aplicación.
* Corregida la lógica de `onPostSubmit` (`home.component.ts`) para mostrar el avatar y las imágenes del post instantáneamente, solucionando el bug de `src=""` que requería refrescar (F5).
* Optimizada la función `loadUserReposts` (`profile.component.ts`) para usar la data del `Resource` y eliminar llamadas N+1 a la API.
* Actualizado `app/Modules/Multimedia/Http/Resources/MultimediaContentResource.php` para que la clave `url_content` devuelva una URL absoluta y completa (usando `Storage::url()`).
* Arreglada la carga de imágenes en el feed y perfiles (el bug del F5):**
    * Añadida la relación `multimedia_contents()` al modelo `app/Modules/Multimedia/Domain/Models/Post.php`.
    * Actualizado `app/Modules/Multimedia/Http/Controllers/PostController.php` (método `index`) para cargar la relación `multimedia_contents` (Eager Loading).
    * Actualizado `app/Modules/UserData/Http/Controllers/ProfileController.php` (métodos `getUserPosts` y `getUserReposts`) para cargar las relaciones anidadas (`multimedia_contents` y `post.multimedia_contents`).


### Affects
* `auth.service.ts`
* `home.component.ts`
* `profile.component.ts`
* `main.component.ts`
* `ProfileController.php`
* `Post.php`
* `PostController.php`
* `MultimediaContentResource.php`


## [feat/frontend/edit-profile] - 2025-10-30

_(Cambios realizados por @facu24fm)_

### Changed
*Se añadio la vista para editar perfil de usuario.
*Se implemento la integracion de notificaciones de la rama feature/frontend/notifications-ui
*Se implemento sistema de reposts.


## [feat/nginx] - 2025-10-30

_(Cambios realizados por @ivanPeceto)_

### Added
* Nuevo archivo `nginx.conf.template`
* Nuevas entradas a `.gitignore` para no subir `nginx.conf`
* Nuevos contenedores para `reverb` y `nginx`
* Nuevo archivo `nginx/nginx.conf.template`

### Changed
* Añade configuración automática a `nginx.conf` en el script de instalación con impresión de la dirección ip del servidor en la red local.
* Añade el comando 

### Affects
* `docker-compose.yml`
* `.gitignore`
* `start_server.sh`

## [feature/frontend/admin-view] - 2025-10-16
## [feature/frontend/search] - 2025-10-20
## [mergetest2/merge-frontend] - 2025-10-20

_(Cambios realizados por @juancruzct)_

### Added

* **Frontend - Búsqueda de Usuarios**:
    * Se implementó una barra de búsqueda dinámica en el `HomeComponent` (ahora `MainComponent` layout).
    * La búsqueda se activa mientras el usuario escribe (`debounceTime`) y busca por nombre (`name`) o username (`@` prefix), mostrando resultados en un panel desplegable.
    * Los resultados son clickeables y navegan al perfil público del usuario (`goToProfile`).
    * Se creó `SearchService` para las llamadas a la API de búsqueda (`/users/search/name/{name}` y `/users/search/username/{username}`).
* **Frontend - Panel de Admin (Gestión de Usuarios)**:
    * Se reemplazaron los botones de acción por un menú desplegable (3 puntos) por cada usuario en la tabla.
    * Se añadió un modal para editar `name`, `username` y `email`.
    * Se añadió un modal para que el admin cambie la contraseña de un usuario (requiere confirmación).
    * Se implementaron dropdowns (`<select>`) en la tabla para cambiar el `rol` y `estado` del usuario, cargando las opciones disponibles desde la API.
    * Se añadieron opciones en el menú desplegable para eliminar `avatar` y `banner` del usuario.
    * Se añadieron métodos a `AdminUserService` para interactuar con los endpoints de actualización (`PUT /privileged/users/{user}/update`), cambio de contraseña (`PUT /privileged/users/{user}/password`), y eliminación de avatar/banner (`DELETE .../{user}/avatar`, `DELETE .../{user}/banner`).
* **Frontend - Perfil Dinámico**:
    * Se añadió la ruta dinámica `/profile/:username` en `app.routes.ts`.
    * `ProfileComponent` ahora lee el parámetro `:username` de la URL para mostrar el perfil público correspondiente (usando `GET /api/users/{user:username}`) o el perfil propio si no hay parámetro (usando `GET /api/profile/show`).
    * Se añadió el método `getPublicProfile` a `ProfileService`.
    * Se reintrodujo y corrigió la lógica `isOwnProfile` en `ProfileComponent`.
* **Contadores en Perfil**: Se añadieron los campos `followers_count` y `following_count` al `UserResource` para mostrar el número de seguidores y seguidos en los perfiles.
* **Listado de Usuarios para Admin**: Se añadió el endpoint `GET /api/privileged/users` para obtener la lista completa de usuarios (protegido para roles `admin` y `moderator`).    

### Changed

* **Backend - Permisos de Actualización**: Se modificó `UpdateProfileRequest.php` para incluir `role_id` y `state_id` en las reglas de validación, permitiendo que el endpoint `PUT /privileged/users/{user}/update` guarde los cambios de rol y estado realizados por un admin.
* **Backend - Route Model Binding**: Se especificó `{user:username}` en la definición de la ruta `GET /api/users/{user}` en `api.php` para asegurar la búsqueda por nombre de usuario.
* **Backend - Visibilidad de Email**: Se ajustó `UserResource.php` para que el endpoint `GET /api/privileged/users` siempre devuelva el campo `email` cuando la petición es hecha por un 'admin' o 'moderator'.
* **Frontend - Panel de Admin (Gestión de Usuarios)**:
    * Se optimizó la actualización de `name`, `username`, `role` y `state` para reflejarse instantáneamente en la interfaz (actualización local del array `users`) sin recargar toda la lista desde la API.
    * Se ajustó la llamada a `changeUserPassword` en `AdminUserService` y `UserManagementComponent` para enviar los campos `new_password` y `new_password_confirmation` requeridos por `ChangePasswordRequest.php`.
    * Se añadió la columna `Email` a la tabla de usuarios.
* **Frontend - Layout Principal**: Se corrigió la función `isPrivilegedUser()` en `MainComponent` para leer el rol como un string simple desde `localStorage` (`currentUser.role`), permitiendo mostrar correctamente el enlace al panel de admin.


### Fixed
* **Frontend - Panel de Admin**:
    * Se corrigió `AdminModule` eliminando la importación de componentes standalone del array `imports`.
    * Se corrigió `AdminUserService` para extraer el array de usuarios de la envoltura `{ "data": [...] }` de la API usando `map`.
    * Se corrigieron errores de `case-sensitivity` en el HTML del `UserManagementComponent` al comparar estados.
    * Se solucionó el bug visual donde la celda de usuario desaparecía tras la edición, modificando `onUpdateUser` para actualizar solo las propiedades `name` y `username` localmente.
* **Frontend - Navegación**: Se corrigió la configuración de rutas (`app.routes.ts`) y la lógica de `ProfileComponent` para permitir la navegación correcta a perfiles (`/profile/:username`) desde los resultados de búsqueda.


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
