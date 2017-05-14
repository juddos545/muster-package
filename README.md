## Muster

This package inspects the columns of a MySQL database table and generates Laravel validations rules based on the column data types and foreign keys.

## Installation

```
composer require juddos545/muster
```

Be sure to include our service provider in your `app.php`:

```php
\Juddos545\Muster\MusterServiceProvider::class
```

## Example

```
muster|master⚡ ⇒ php artisan muster:validation domains

Generated validation for domains:

['domain_name' => 'required', 'blogger_name' => 'required', 'email' => 'required|email', 'niche_id' => 'required|exists:niches', 'notes' => 'required|string', 'accepts_gifts' => 'required|boolean', 'writes_content' => 'required|boolean', 'da_rating' => 'required|integer', 'tf_rating' => 'required|integer', 'cf_rating' => 'required|integer', 'currency_id' => 'required|exists:currencies', 'archived_at' => 'required|date', 'disabled_at' => 'required|date']
```