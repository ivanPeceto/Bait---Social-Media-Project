
# Bait
## An open-source social-media project

Bait is a social media application inspired by popular platforms like X, designed to provide a seamless and engaging user experience. This project was developed as part of a university course, showcasing a full-stack application with real-time features and a modern architecture.


## Features

- **User Authentication**: Secure user registration, login, and profile management.
- **Post Creation**: Create and share posts with text and images.
- **Following System**: Follow other users to see their content on your feed.
- **Reactions and engagement**:
    - Like, Dislike, Haha, and Sad reactions.
    - Comment on posts.
    - Share posts with your followers.
- **Notifications**: Receive instant notifications for new interactions (likes, comments, new posts, etc).
- **Direct Messaging**: Chat with other users in real-time.
- **User Profiles**: Dedicated profile pages showcasing user information, profile pictures, and all their published and shared posts.

## Tech Stack

**Frontend**: Angular (with typescript) and TailwindCSS

**Backend**: Laravel, Swagger and Redis.

**DataBase**: MySQL.

**Infrastructure**: Docker and Nginx.



## Authors

- [Iván Gabriel Peceto](https://github.com/ivanPeceto)
- [Juan Cruz Comas Tavella](https://github.com/juancruzct12)
- [Facundo Nahuel Martinez Larroza](https://github.com/facu24fm)
- [Juan Manuel Rodriguez Spinker](https://github.com/jmrodriguezspinker)



## Installation and Setup

To get the project up and running, follow these steps:

1. Clone the repository
    ```bash
    git clone https://github.com/ivanPeceto/Bait---Social-Media-Project.git
    cd Bait---Social-Media-Project
    ```

2. Configure `.env` files:

    - In the root of the project, copy the example environment file:
    ```bash
    cp .env.example .env
    ```
    - Configure your database connection, Redis, and broadcasting settings.

3. Make the installation script executable:
    - (You'll only need to do this once)
    ```bash
    chmod +x start_server.sh
    ```

4. Run the application:
    - Our `start_server.sh` script simplifies the entire Docker setup.
    - You can check all the functionallities by running:
    ```bash
    ./start_server.sh --help
    ```
    - For a fresh installation you should run:
    ```bash
    ./start_server.sh --clean --build
    ```

5. Access the application:
    - The frontend will be available at `http://localhost:4200`.
    - The backend API will be available at `http://localhost:8001`. 
    - The API documentation can be accessed at `http://localhost:8001/api/documentation`.

    

## License

This project is open-source and is licensed under the [MIT License](https://choosealicense.com/licenses/mit/)

