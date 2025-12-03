Assumptions & Invariants
Core Business Assumptions

A Product has an available_quantity representing stock that can be reserved or sold.

A Hold represents a temporary reservation preventing overselling until it is:

Converted into an order, OR

Expired automatically via scheduler.

System Invariants (Always True)
Invariant	Meaning
available_quantity ≥ 0	No overselling under any concurrency condition.
A product cannot be modified while a hold is processing	Guaranteed using row-level locking lockForUpdate()
A hold can be processed only once	Prevented via idempotent key (idempotency)
On hold expiration → product quantity must be restored	Verified by automated scheduler + tests
Webhooks must not produce duplicate orders	Same idempotency keys enforce concurrency safety

These invariants are enforced using DB transactions, Redis caching, and atomic operations.

▶️ How to Run the Application
1️⃣ Install Dependencies
composer install
cp .env.example .env
php artisan key:generate


Configure your database in .env.

2️⃣ Run Migrations & Seeders
php artisan migrate --seed

3️⃣ Start Application
php artisan serve



⏱ Scheduler for Hold Expiry

The system automatically expires holds and restores product stock.

php artisan schedule:run



🧪 Running Tests (Pest)
Run full test suite
php artisan test




Tests cover:

Preventing overselling under concurrent holds

Product quantity restoration on hold expiry

Webhook idempotency safety

Race condition between webhook and order creation

📊 Logs & Metrics
Application Logs

Location:

storage/logs/laravel.log




Enable in .env:

APP_DEBUG=true
DB_LOGGING=true

Cache Keys

To inspect cached product stock:

product_{id}

You can manually verify the application behavior using the provided Postman Collection.
You will find the collection JSON file located in the root directory of the project.