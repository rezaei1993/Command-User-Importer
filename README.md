# Project Name

## Description

This project is aimed at providing a command-line tool to import user data from a text file into a database. It includes functionality to validate the data and handle import failures gracefully.

## Installation

1. Clone the repository:
   ```
   https://github.com/rezaei1993/Command-User-Importer.git
   ```

2. Navigate to the project directory:
   ```
   cd your-project
   ```

3. Install dependencies:
   ```
   composer install
   ```

4. Set up your environment variables by copying the `.env.example` file to `.env`:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Configure your database connection in the `.env` file.

## Usage

To import users from a text file, run the following command:

```
php artisan import:users
```

Ensure that the text file is located at `app/Console/Commands/TemporaryFiles/users.txt`.

## Features

- Imports user data from a text file into a database.
- Validates user data and handles import failures.
- Logs errors for failed imports.
- Creates a separate file for storing failed import lines.

## Contributing

Contributions are welcome! Please feel free to open an issue or submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
