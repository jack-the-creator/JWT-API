# JWT-API

## Set-up

Build and start docker containers:
```
docker compose up -d —build
```

Enter bash terminal in PHP container:
```
docker exec -it php-container bash
```

Install all dependencies:
```
composer install
```

Create databases:
```
php bin/console doctrine:database:create
php bin/console doctrine:database:create --env=test 
```

Migrate migrations:
```
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:migrate --env=test
```


## API - [User Controller](https://github.com/jack-the-creator/JWT-API/blob/main/app/src/Controller/UserController.php)

### **POST** /register

Register a new user

Request payload:
- email
- password

Requirements:
- Email must follow standard email format
- Password must be 6 characters or more

Example:
```
{
    "email": "test@test.com"
    "password": "password123
}
```
Expected return:
```
{
    "token": sfwefwdfsdfwefe...
}
```

### **POST** /login

Login with valid credentials

Request payload:
- email
- password

Example:
```
{
    "email": "test@test.com"
    "password": "password123
}
```
Expected return:
```
{
    "token": sfwefwdfsdfwefe...
}
```

## Testing

Run all unit tests:
```
php bin/phpunit
```

The tests for the [User Registration](https://github.com/jack-the-creator/JWT-API/blob/main/app/tests/UserRegistrationTest.php) are to simulate entering valid and invalid data when registering. To run the [tests]([https://github.com/jack-the-creator/Lexsynergy-backend/blob/master/tests/RegistrationFormTypeTest.php](https://github.com/jack-the-creator/JWT-API/blob/main/app/tests/UserRegistrationTest.php)) specifically, please enter into your bash terminal:
```
php bin/phpunit tests/UserRegistrationTest.php
```

The tests for the [User Login](https://github.com/jack-the-creator/JWT-API/blob/main/app/tests/UserLoginTest.php) are to simulate entering valid and invalid data when logging in. To run the [tests]([https://github.com/jack-the-creator/Lexsynergy-backend/blob/master/tests/RegistrationFormTypeTest.php](https://github.com/jack-the-creator/JWT-API/blob/main/app/tests/UserLoginTest.php)) specifically, please enter into your terminal:
```
php bin/phpunit tests/UserLoginTest.php
```
