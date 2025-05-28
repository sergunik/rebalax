# Rebalax

Rebalax is an open-source automated portfolio rebalancer designed for managing dual-asset crypto portfolios (e.g. BTC and XAUT). The bot monitors price fluctuations and executes rebalancing operations when the asset allocation deviates beyond a configurable threshold (e.g. 7%).

It supports trading via centralized exchange APIs (e.g. Binance), converting assets through an intermediate stablecoin (USDT) to maintain an even 50/50 balance. The project is built with simplicity, transparency, and automation in mind – ideal for long-term crypto holders who want to optimize their portfolio performance without daily micromanagement.

## Requirements

- PHP 8.x or higher
- Composer
- MySQL or another SQL database

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/your-repo.git
    cd your-repo
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    ```

3.  **Set up environment:**

    -   Copy `.env.example` to `.env` and configure your database and other environment variables.
    -   Generate application key:
        ```bash
        php artisan key:generate
        ```

4.  **Database setup:**

    -   Run database migrations:
        ```bash
        php artisan migrate
        ```

    -   Seed the database (optional):
        ```bash
        php artisan db:seed
        ```

## Code Quality

This project uses [PHP\_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for code style checks.

-   To run the linter:
    ```bash
    vendor/bin/phpcs --standard=PSR12 app/
    ```
    
## License

This is an open-source project licensed under the [MIT License](LICENSE).
